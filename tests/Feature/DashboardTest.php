<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Task;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertStatus(200);
    }

    public function test_dashboard_shows_cached_user_order_and_task_counts(): void
    {
        $this->actingAs(User::factory()->create());
        $division = Division::query()->create(['nama' => 'Royal Ambulance']);
        $status = OrderStatus::query()->create(['name' => 'Waiting', 'color' => 'gray']);

        Order::create([
            'order_number' => 'RA.0001',
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Budi',
            'customer_phone' => '0812',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
        Task::create(['title' => 'Cek unit']);
        Task::create(['title' => 'Laporan']);

        Cache::flush();

        $this->get('/dashboard')
            ->assertOk()
            ->assertViewHas('total_users', 1)
            ->assertViewHas('total_orders', 1)
            ->assertViewHas('total_tasks', 2)
            ->assertSee('Total Tasks');

        $this->assertTrue(Cache::has('dashboard.stats.visible'));

        $this->get('/dashboard')->assertOk();
        $this->assertSame(2, Cache::get('dashboard.stats.visible')['total_tasks']);
    }

    public function test_creating_a_task_refreshes_dashboard_cache(): void
    {
        $this->actingAs(User::factory()->create());
        Cache::put('dashboard.stats.visible', [
            'total_users' => 99,
            'total_orders' => 99,
            'total_tasks' => 99,
        ], 300);

        Task::create(['title' => 'Baru']);

        $this->assertFalse(Cache::has('dashboard.stats.visible'));
        $this->assertSame(1, app(DashboardStatsService::class)->counts()['total_tasks']);
    }
}
