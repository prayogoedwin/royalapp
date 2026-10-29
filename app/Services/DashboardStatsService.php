<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Task;
use App\Models\User;
use App\Support\ReportPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    public const CACHE_TTL_SECONDS = 300;

    public const CACHE_VERSION_KEY = 'dashboard.stats.version';

    /**
     * @return array{
     *     total_users: int,
     *     total_orders: int,
     *     total_tasks: int,
     *     order_statuses: list<array{name: string, color: string, total: int}>
     * }
     */
    public function counts(?User $viewer = null, ?ReportPeriod $period = null): array
    {
        $viewer ??= auth()->user();
        $period ??= ReportPeriod::fromRequest(request() instanceof Request ? request() : Request::create('/'));
        $key = $this->cacheKey($viewer, $period);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($viewer, $period) {
            $bounds = $period->bounds;

            return [
                'total_users' => User::query()->visibleTo($viewer)->count(),
                'total_orders' => $this->ordersInPeriod(Order::query(), $bounds)->count(),
                'total_tasks' => Task::query()
                    ->when($bounds, fn (Builder $query) => $query->whereBetween('created_at', $bounds))
                    ->count(),
                'order_statuses' => $this->orderStatusCounts($bounds),
            ];
        });
    }

    public static function flush(): void
    {
        if (! Cache::has(self::CACHE_VERSION_KEY)) {
            Cache::forever(self::CACHE_VERSION_KEY, 1);
        }

        Cache::increment(self::CACHE_VERSION_KEY);
        Cache::forget('dashboard.stats.visible');
        Cache::forget('dashboard.stats.all');
        Cache::forget('dashboard.stats.visible.v2');
        Cache::forget('dashboard.stats.all.v2');
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     * @return list<array{name: string, color: string, total: int}>
     */
    private function orderStatusCounts(?array $bounds): array
    {
        return OrderStatus::query()
            ->orderBy('id')
            ->withCount(['orders as orders_count' => function (Builder $query) use ($bounds): void {
                $this->ordersInPeriod($query, $bounds);
            }])
            ->get()
            ->map(fn (OrderStatus $status): array => [
                'name' => $status->name,
                'color' => $status->color,
                'total' => (int) $status->orders_count,
            ])
            ->all();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    private function ordersInPeriod(Builder $query, ?array $bounds): Builder
    {
        if ($bounds === null) {
            return $query;
        }

        return $query->whereRaw('COALESCE(pickup_datetime, created_at) BETWEEN ? AND ?', [
            $bounds[0]->format('Y-m-d H:i:s'),
            $bounds[1]->format('Y-m-d H:i:s'),
        ]);
    }

    private function cacheKey(?User $viewer, ReportPeriod $period): string
    {
        $version = Cache::get(self::CACHE_VERSION_KEY);
        if ($version === null) {
            Cache::forever(self::CACHE_VERSION_KEY, 1);
            $version = 1;
        }

        $scope = $viewer instanceof User && $viewer->isHiddenAccount()
            ? 'all'
            : 'visible';

        return 'dashboard.stats.v'.(int) $version.'.'.$scope.'.'.$period->cacheSuffix();
    }
}
