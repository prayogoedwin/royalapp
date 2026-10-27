<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class DeviceTokenController extends Controller
{
    public function __construct(private DeviceTokenService $tokens) {}

    /**
     * @OA\Post(
     *     path="/api/device-token",
     *     tags={"Auth"},
     *     summary="Register FCM token for the logged-in user",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *
     *             @OA\Property(property="fcm_token", type="string"),
     *             @OA\Property(property="platform", type="string", example="android")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:32'],
        ]);

        $this->tokens->save($request->user(), $validated['fcm_token'], $validated['platform'] ?? null);

        return response()->json([
            'status' => true,
            'message' => 'Device token disimpan.',
            'data' => [],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/device-token/refresh",
     *     tags={"Auth"},
     *     summary="Replace an old FCM token with a new one",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *
     *             @OA\Property(property="fcm_token", type="string", description="New FCM token"),
     *             @OA\Property(property="old_fcm_token", type="string", nullable=true, description="Previous FCM token to replace"),
     *             @OA\Property(property="platform", type="string", example="android")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'old_fcm_token' => ['nullable', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:32'],
        ]);

        $this->tokens->refresh(
            $request->user(),
            $validated['fcm_token'],
            $validated['old_fcm_token'] ?? null,
            $validated['platform'] ?? null
        );

        return response()->json([
            'status' => true,
            'message' => 'Device token diperbarui.',
            'data' => [],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/device-token",
     *     tags={"Auth"},
     *     summary="Remove FCM token for the logged-in user",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *
     *             @OA\Property(property="fcm_token", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        $this->tokens->forget($request->user(), $validated['fcm_token']);

        return response()->json([
            'status' => true,
            'message' => 'Device token dihapus.',
            'data' => [],
        ]);
    }
}
