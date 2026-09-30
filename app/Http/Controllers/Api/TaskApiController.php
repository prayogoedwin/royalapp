<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class TaskApiController extends Controller
{
    /**
     * @OA\Get(path="/api/tasks", tags={"Tasks"}, summary="List tasks assigned to the logged-in employee", security={{"sanctum":{}}}, @OA\Response(response=200, description="Success"))
     */
    public function index(Request $request): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;
        if (! $employeeId) {
            return $this->ok([]);
        }

        $query = $this->assignedQuery($employeeId)
            ->with(['orderStatus', 'taskCrews.employee']);

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $statusNames = $this->statusFilterNames((string) $request->query('status', ''));
        if ($statusNames !== []) {
            $query->whereHas('orderStatus', function ($q) use ($statusNames) {
                $q->whereIn('name', $statusNames);
            });
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $tasks = $query->latest('id')->paginate($perPage);

        $tasks->setCollection(
            $tasks->getCollection()->map(fn (Task $task) => $this->payload($task))
        );

        return $this->ok($tasks);
    }

    /**
     * @OA\Get(path="/api/tasks/{task}", tags={"Tasks"}, summary="Task detail for the logged-in employee", security={{"sanctum":{}}}, @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success"))
     */
    public function show(Request $request, int $task): JsonResponse
    {
        $employeeId = $request->user()->employee?->id;
        if (! $employeeId) {
            return $this->error('Task not found.', 404);
        }

        $model = $this->assignedQuery($employeeId)
            ->with(['orderStatus', 'taskCrews.employee', 'createdBy'])
            ->whereKey($task)
            ->first();

        if (! $model) {
            return $this->error('Task not found.', 404);
        }

        return $this->ok($this->payload($model));
    }

    private function assignedQuery(int $employeeId)
    {
        return Task::query()->whereHas('taskCrews', fn ($q) => $q->where('employee_id', $employeeId));
    }

    /**
     * Map HP filter chips to order_statuses.name.
     * menunggu = Pending + Waiting so newly created pending tasks appear there.
     *
     * @return list<string>
     */
    private function statusFilterNames(string $status): array
    {
        $key = mb_strtolower(trim($status));

        return match ($key) {
            '', 'semua', 'all' => [],
            'menunggu', 'waiting', 'pending' => ['Pending', 'Waiting'],
            'berlangsung', 'ongoing' => ['Ongoing'],
            'selesai', 'done' => ['Done'],
            'cancelled' => ['Cancelled'],
            default => [],
        };
    }

    private function payload(Task $task): array
    {
        $crews = $task->taskCrews->map(function ($crew) {
            $name = $crew->employee?->full_name ?? '-';

            return [
                'employee_id' => $crew->employee_id,
                'name' => $name,
                'initials' => $this->initials($name),
                'role' => $crew->role,
            ];
        })->values();

        return [
            'id' => $task->id,
            'code' => sprintf('TSK-%03d', $task->id),
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->orderStatus ? [
                'id' => $task->orderStatus->id,
                'name' => $task->orderStatus->name,
                'color' => $task->orderStatus->color,
            ] : null,
            'crews' => $crews,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
        ];
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    }

    private function ok(mixed $data = [], string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [],
        ], $status);
    }
}
