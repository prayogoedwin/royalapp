<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="List users",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles.permissions')->visibleTo()->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $users,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get profile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $request->user()->load('roles.permissions'),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Update profile",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
        ]);

        $request->user()->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'data' => $request->user()->fresh()->load('roles.permissions'),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     tags={"Profile"},
     *     summary="Update password",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is invalid.',
                'data' => [],
            ], 422);
        }

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully.',
            'data' => [],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/stats/orders/total",
     *     tags={"Stats"},
     *     summary="Count orders by user for every status (cached)",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function totalOrders(Request $request): JsonResponse
    {
        $user = $request->user();
        $employeeId = $user->employee?->id;

        $empty = $this->emptyOrderCounts();

        if (! $employeeId) {
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $empty,
            ]);
        }

        $cacheKey = "api:user:{$user->id}:order-counts:v3";
        $counts = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($employeeId) {
            $orders = Order::query()
                ->whereHas('orderCrews', fn ($q) => $q->where('employee_id', $employeeId));

            $counts = ['total_orders' => (clone $orders)->count()];

            foreach ($this->orderCountFields() as $statusName => $field) {
                $counts[$field] = (clone $orders)->whereHas('orderStatus', function ($status) use ($statusName) {
                    $status->whereRaw('LOWER(name) = ?', [$statusName]);
                })->count();
            }

            return $counts;
        });

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $counts,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function orderCountFields(): array
    {
        return [
            'pending' => 'pending_orders',
            'waiting' => 'waiting_orders',
            'ongoing' => 'ongoing_orders',
            'done' => 'done_orders',
            'cancelled' => 'cancelled_orders',
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyOrderCounts(): array
    {
        return array_merge(
            ['total_orders' => 0],
            array_fill_keys(array_values($this->orderCountFields()), 0)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/stats/tasks/total",
     *     tags={"Stats"},
     *     summary="Count total tasks by user",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function totalTasks(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;
        if (! $employeeId) {
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => [
                    'total_tasks' => 0,
                    'pending_tasks' => 0,
                ],
            ]);
        }

        $query = Task::query()
            ->whereHas('taskCrews', fn ($q) => $q->where('employee_id', $employeeId));

        $total = (clone $query)->count();
        $pending = (clone $query)
            ->where(function ($q) {
                $q->whereHas('orderStatus', function ($status) {
                    $status->whereIn('name', ['Pending', 'Waiting']);
                })->orWhereNull('order_status_id');
            })
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => [
                'total_tasks' => $total,
                'pending_tasks' => $pending,
            ],
        ]);
    }
}
