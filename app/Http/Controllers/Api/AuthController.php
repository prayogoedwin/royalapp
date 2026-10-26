<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="fcm_token", type="string", nullable=true),
     *             @OA\Property(property="platform", type="string", nullable=true, example="android")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function login(Request $request, DeviceTokenService $deviceTokens): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'fcm_token' => ['nullable', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:32'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah.',
                'data' => [],
            ], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        if (! empty($validated['fcm_token'])) {
            $deviceTokens->save($user, $validated['fcm_token'], $validated['platform'] ?? null);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="fcm_token", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function logout(Request $request, DeviceTokenService $deviceTokens): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:512'],
        ]);

        if (! empty($validated['fcm_token'])) {
            $deviceTokens->forget($request->user(), $validated['fcm_token']);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
            'data' => [],
        ]);
    }
}
