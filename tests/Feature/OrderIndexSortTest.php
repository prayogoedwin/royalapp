<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIndexSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_order_list_returns_newest_first(): void
    {
        $user = $this->userWithPermissions(['view-orders']);
        $older = $this->makeOrder('RA.0001', $user);
        $newer = $this->makeOrder('RA.0002', $user);
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer->forceFill(['created_at' => now()])->save();

        $response = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('orders.index'), $this->dataTablesQuery());

        $response->assertOk();
        $this->assertSame(
            [$newer->id, $older->id],
            collect($response->json('data'))->pluck('id')->all()
        );
        $this->assertSame(
            [$user->name, $user->name],
            collect($response->json('data'))->pluck('created_by_name')->all()
        );
    }

    public function test_orders_index_has_status_select(): void
    {
        $user = $this->userWithPermissions(['view-orders']);
        OrderStatus::query()->firstOrCreate(['name' => 'Pending'], ['color' => 'yellow']);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('id="order-status-filter"', false)
            ->assertSee('Semua status')
            ->assertSee('Pending');
    }

    public function test_orders_index_can_filter_by_status_and_period(): void
    {
        $user = $this->userWithPermissions(['view-orders']);
        $waiting = OrderStatus::query()->firstOrCreate(['name' => 'Waiting'], ['color' => 'gray']);
        $done = OrderStatus::query()->firstOrCreate(['name' => 'Done'], ['color' => 'green']);

        $todayWaiting = $this->makeOrder('RA.WAIT.TODAY', $user, $waiting);
        $todayDone = $this->makeOrder('RA.DONE.TODAY', $user, $done);
        $oldWaiting = $this->makeOrder('RA.WAIT.OLD', $user, $waiting);
        $oldWaiting->forceFill([
            'created_at' => now()->subDay(),
            'pickup_datetime' => now()->subDay(),
        ])->save();

        $todayWaiting->forceFill(['created_at' => now(), 'pickup_datetime' => now()])->save();
        $todayDone->forceFill(['created_at' => now(), 'pickup_datetime' => now()])->save();

        $statusResponse = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('orders.index', ['status' => 'Waiting']), $this->dataTablesQuery());

        $statusResponse->assertOk();
        $statusIds = collect($statusResponse->json('data'))->pluck('id')->all();
        $this->assertContains($todayWaiting->id, $statusIds);
        $this->assertContains($oldWaiting->id, $statusIds);
        $this->assertNotContains($todayDone->id, $statusIds);

        $periodResponse = $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('orders.index', [
                'status' => 'Waiting',
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ]), $this->dataTablesQuery());

        $periodResponse->assertOk();
        $periodIds = collect($periodResponse->json('data'))->pluck('id')->all();
        $this->assertContains($todayWaiting->id, $periodIds);
        $this->assertNotContains($oldWaiting->id, $periodIds);
        $this->assertNotContains($todayDone->id, $periodIds);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'tester-'.implode('-', $permissions)]);
        $permissionIds = [];

        foreach ($permissions as $name) {
            $permissionIds[] = Permission::query()->firstOrCreate(['name' => $name])->id;
        }

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeOrder(string $orderNumber, ?User $creator = null, ?OrderStatus $status = null): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $status ??= OrderStatus::query()->firstOrCreate(['name' => 'Open'], ['color' => '#2563eb']);

        return Order::create([
            'order_number' => $orderNumber,
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
            'created_by' => $creator?->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataTablesQuery(): array
    {
        $columns = [
            ['data' => 'order_number', 'name' => 'order_number'],
            ['data' => 'customer_name', 'name' => 'customer_name'],
            ['data' => 'division_name', 'name' => 'division.nama'],
            ['data' => 'pickup_datetime', 'name' => 'pickup_datetime'],
            ['data' => 'price', 'name' => 'price'],
            ['data' => 'status_badge', 'name' => 'order_status_id'],
            ['data' => 'created_by_name', 'name' => 'created_by_name'],
            ['data' => 'created_at', 'name' => 'created_at'],
            ['data' => 'actions', 'name' => 'actions', 'orderable' => 'false', 'searchable' => 'false'],
        ];

        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => $columns,
            'order' => [
                ['column' => 7, 'dir' => 'desc'],
            ],
        ];
    }
}
