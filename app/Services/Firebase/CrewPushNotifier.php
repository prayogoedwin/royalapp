<?php

namespace App\Services\Firebase;

use App\Jobs\SendCrewPushNotification;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Task;
use App\Models\UserDevice;
use Illuminate\Support\Collection;

class CrewPushNotifier
{
    public function __construct(private FcmClient $client) {}

    public function notifyOrderCreated(Order $order): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        if ($order->orderCrews->isEmpty()) {
            return;
        }

        $status = strtolower((string) $order->orderStatus?->name);
        if ($status !== '' && $status !== 'waiting') {
            return;
        }

        $this->dispatch(
            $this->orderUserIds($order),
            'Order baru',
            'Kamu ditugaskan ke order '.$order->order_number.($order->orderStatus?->name ? ' ('.$order->orderStatus->name.')' : ''),
            [
                'type' => 'order_assigned',
                'order_id' => (string) $order->id,
                'order_number' => (string) $order->order_number,
            ]
        );
    }

    public function notifyOrderUpdated(Order $order, string $reason = 'Order diperbarui'): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        $this->dispatch(
            $this->orderUserIds($order),
            'Update order',
            $reason.': '.$order->order_number.($order->orderStatus?->name ? ' ('.$order->orderStatus->name.')' : ''),
            [
                'type' => 'order_updated',
                'order_id' => (string) $order->id,
                'order_number' => (string) $order->order_number,
            ]
        );
    }

    public function notifyOrderCrewChanged(Order $order): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        $this->dispatch(
            $this->orderUserIds($order),
            'Tim order',
            'Kamu ditugaskan / diperbarui pada order '.$order->order_number,
            [
                'type' => 'order_crew_changed',
                'order_id' => (string) $order->id,
                'order_number' => (string) $order->order_number,
            ]
        );
    }

    public function notifyTaskCreated(Task $task): void
    {
        $task->loadMissing(['orderStatus', 'taskCrews.employee']);

        if ($task->taskCrews->isEmpty()) {
            return;
        }

        $this->dispatch(
            $this->taskUserIds($task),
            'Task baru',
            'Kamu ditugaskan ke task: '.$task->title,
            [
                'type' => 'task_assigned',
                'task_id' => (string) $task->id,
            ]
        );
    }

    public function notifyTaskUpdated(Task $task): void
    {
        $task->loadMissing(['orderStatus', 'taskCrews.employee']);

        $this->dispatch(
            $this->taskUserIds($task),
            'Update task',
            'Ada perubahan pada task: '.$task->title,
            [
                'type' => 'task_updated',
                'task_id' => (string) $task->id,
            ]
        );
    }

    /**
     * @param  Collection<int, int|string>  $userIds
     * @param  array<string, string>  $data
     */
    private function dispatch(Collection $userIds, string $title, string $body, array $data): void
    {
        if (! $this->client->isEnabled()) {
            return;
        }

        $actorId = auth()->id();
        $userIds = $userIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn (int $id) => $actorId && $id === (int) $actorId)
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $tokens = UserDevice::query()
            ->whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->all();

        if ($tokens === []) {
            return;
        }

        SendCrewPushNotification::dispatch($tokens, $title, $body, $data)->afterCommit();
    }

    /**
     * @return Collection<int, int>
     */
    private function orderUserIds(Order $order): Collection
    {
        return $order->orderCrews
            ->map(fn ($crew) => $crew->employee instanceof Employee ? $crew->employee->user_id : null)
            ->filter();
    }

    /**
     * @return Collection<int, int>
     */
    private function taskUserIds(Task $task): Collection
    {
        return $task->taskCrews
            ->map(fn ($crew) => $crew->employee instanceof Employee ? $crew->employee->user_id : null)
            ->filter();
    }
}
