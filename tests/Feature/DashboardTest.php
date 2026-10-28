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
            ->assertSee('Total Tasks')
            ->assertSee('Waiting');

        $this->assertTrue(Cache::has('dashboard.stats.visible.v2'));

        $this->get('/dashboard')->assertOk();
        $this->assertSame(2, Cache::get('dashboard.stats.visible.v2')['total_tasks']);
    }

    public function test_creating_a_task_refreshes_dashboard_cache(): void
    {
        $this->actingAs(User::factory()->create());
        Cache::put('dashboard.stats.visible.v2', [
            'total_users' => 99,
            'total_orders' => 99,
            'total_tasks' => 99,
            'order_statuses' => [],
        ], 300);

        Task::create(['title' => 'Baru']);

        $this->assertFalse(Cache::has('dashboard.stats.visible.v2'));
        $this->assertSame(1, app(DashboardStatsService::class)->counts()['total_tasks']);
    }

    public function test_dashboard_shows_order_counts_per_status_including_cancelled(): void
    {
        $this->actingAs(User::factory()->create());
        $division = Division::query()->create(['nama' => 'Royal Ambulance']);
        $waiting = OrderStatus::query()->create(['name' => 'Waiting', 'color' => 'gray']);
        $done = OrderStatus::query()->create(['name' => 'Done', 'color' => 'green']);
        $cancelled = OrderStatus::query()->create(['name' => 'Cancelled', 'color' => 'red']);

        $this->makeDashboardOrder($division->id, $waiting->id, 'RA.0001');
        $this->makeDashboardOrder($division->id, $done->id, 'RA.0002');
        $this->makeDashboardOrder($division->id, $cancelled->id, 'RA.0003');
        $this->makeDashboardOrder($division->id, $cancelled->id, 'RA.0004');

        Cache::flush();

        $this->get('/dashboard')
            ->assertOk()
            ->assertViewHas('total_orders', 4)
            ->assertSee('Status Order')
            ->assertSee('Cancelled')
            ->assertSee('Waiting')
            ->assertSee('Done');

        $statuses = collect($this->viewDataFromDashboard()['order_statuses'])->keyBy('name');
        $this->assertSame(1, $statuses['Waiting']['total']);
        $this->assertSame(1, $statuses['Done']['total']);
        $this->assertSame(2, $statuses['Cancelled']['total']);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewDataFromDashboard(): array
    {
        return Cache::get('dashboard.stats.visible.v2');
    }

    private function makeDashboardOrder(int $divisionId, int $statusId, string $orderNumber): void
    {
        Order::create([
            'order_number' => $orderNumber,
            'division_id' => $divisionId,
            'order_status_id' => $statusId,
            'customer_name' => 'Budi',
            'customer_phone' => '0812',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
    }
}
