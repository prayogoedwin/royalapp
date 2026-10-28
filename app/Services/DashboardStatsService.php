<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public const CACHE_TTL_SECONDS = 300;

    /**
     * @return array{
     *     total_users: int,
     *     total_orders: int,
     *     total_tasks: int,
     *     order_statuses: list<array{name: string, color: string, total: int}>
     * }
     */
    public function counts(?User $viewer = null): array
    {
        $viewer ??= auth()->user();
        $key = $this->cacheKey($viewer);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($viewer) {
            return [
                'total_users' => User::query()->visibleTo($viewer)->count(),
                'total_orders' => Order::query()->count(),
                'total_tasks' => Task::query()->count(),
                'order_statuses' => $this->orderStatusCounts(),
            ];
        });
    }

    public static function flush(): void
    {
        Cache::forget('dashboard.stats.visible');
        Cache::forget('dashboard.stats.all');
        Cache::forget('dashboard.stats.visible.v2');
        Cache::forget('dashboard.stats.all.v2');
    }

    /**
     * @return list<array{name: string, color: string, total: int}>
     */
    private function orderStatusCounts(): array
    {
        return OrderStatus::query()
            ->orderBy('id')
            ->withCount('orders')
            ->get()
            ->map(fn (OrderStatus $status): array => [
                'name' => $status->name,
                'color' => $status->color,
                'total' => (int) $status->orders_count,
            ])
            ->all();
    }

    private function cacheKey(?User $viewer): string
    {
        return $viewer instanceof User && $viewer->isHiddenAccount()
            ? 'dashboard.stats.all.v2'
            : 'dashboard.stats.visible.v2';
    }
}
