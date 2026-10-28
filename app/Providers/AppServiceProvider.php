<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $flushDashboard = fn () => DashboardStatsService::flush();

        foreach ([User::class, Order::class, Task::class] as $model) {
            $model::created($flushDashboard);
            $model::updated($flushDashboard);
            $model::deleted($flushDashboard);
            $model::restored($flushDashboard);
        }
    }
}
