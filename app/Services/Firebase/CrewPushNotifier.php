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
            'Kamu ditugaskan ke order '.$this->orderLabel($order).($order->orderStatus?->name ? ' ('.$order->orderStatus->name.')' : ''),
            $this->orderPayload($order, 'order_assigned')
        );
    }

    public function notifyOrderUpdated(Order $order, string $reason = 'Order diperbarui'): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        $this->dispatch(
            $this->orderUserIds($order),
            'Update order',
            $reason.': '.$this->orderLabel($order).($order->orderStatus?->name ? ' ('.$order->orderStatus->name.')' : ''),
            $this->orderPayload($order, 'order_updated')
        );
    }

    public function notifyOrderCrewChanged(Order $order): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        $this->dispatch(
            $this->orderUserIds($order),
            'Tim order',
            'Kamu ditugaskan / diperbarui pada order '.$this->orderLabel($order),
            $this->orderPayload($order, 'order_crew_changed')
        );
    }

    public function notifyCreatorOrderDone(Order $order, int $previousStatusId): void
    {
        $order->loadMissing(['orderStatus', 'orderCrews.employee']);

        if ((int) $order->order_status_id === $previousStatusId) {
            return;
        }

        if (strtolower((string) $order->orderStatus?->name) !== 'done') {
            return;
        }

        if (! $order->created_by) {
            return;
        }

        $this->dispatch(
            collect([(int) $order->created_by]),
            'Order done',
            $this->driverName($order).' dengan no order '.$this->orderNumber($order).' sudah done, cek website',
            $this->orderPayload($order, 'order_done')
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

    private function orderLabel(Order $order): string
    {
        $number = trim((string) $order->order_number);

        return $number !== ''
            ? '#'.$order->id.' ('.$number.')'
            : '#'.$order->id;
    }

    private function orderNumber(Order $order): string
    {
        $number = trim((string) $order->order_number);

        return $number !== '' ? $number : '#'.$order->id;
    }

    private function driverName(Order $order): string
    {
        foreach ($order->orderCrews as $crew) {
            $role = strtolower(trim((string) $crew->role));
            if (in_array($role, ['driver', 'sopir'], true)) {
                $name = trim((string) ($crew->employee?->full_name ?: $crew->employee?->user?->name));
                if ($name !== '') {
                    return $name;
                }
            }
        }

        foreach ($order->orderCrews as $crew) {
            $name = trim((string) ($crew->employee?->full_name ?: $crew->employee?->user?->name));
            if ($name !== '') {
                return $name;
            }
        }

        return 'Driver';
    }

    /**
     * @return array<string, string>
     */
    private function orderPayload(Order $order, string $type): array
    {
        return [
            'type' => $type,
            'id' => (string) $order->id,
            'order_id' => (string) $order->id,
            'order_number' => (string) $order->order_number,
        ];
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
