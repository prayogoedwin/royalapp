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

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_customers(): void
    {
        $this->get(route('customers.index'))->assertRedirect('/login');
        $this->get(route('customers.show', ['phone' => '08123456789']))->assertRedirect('/login');
    }

    public function test_user_without_permission_cannot_open_customers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('customers.index'))->assertForbidden();
    }

    public function test_customers_are_grouped_by_phone(): void
    {
        $viewer = $this->userWithPermissions(['view-orders', 'show-orders', 'create-orders']);
        $this->makeOrder('RA.0001', 'Budi', '08111111111');
        $this->makeOrder('RA.0002', 'Budi Repeat', '08111111111');
        $this->makeOrder('RA.0003', 'Sari', '08222222222');

        $response = $this->actingAs($viewer)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('customers.index'), $this->customerDataTablesQuery());

        $response->assertOk();
        $rows = collect($response->json('data'));
        $this->assertCount(2, $rows);

        $budi = $rows->first(fn ($row) => str_contains((string) ($row['customer_phone'] ?? ''), '08111111111'));
        $this->assertNotNull($budi);
        $this->assertSame(2, (int) $budi['total_orders']);
        $this->assertSame('Budi Repeat', $budi['customer_name']);
        $this->assertStringContainsString('Repeat Order', $budi['actions']);
        $this->assertStringContainsString('Detail', $budi['actions']);
    }

    public function test_customer_detail_shows_history_and_repeat_order(): void
    {
        $viewer = $this->userWithPermissions(['view-orders', 'show-orders', 'create-orders']);
        $this->makeOrder('RA.1001', 'Budi', '08111111111');
        $this->makeOrder('RA.1002', 'Budi Repeat', '08111111111');
        $this->makeOrder('RA.2001', 'Sari', '08222222222');

        $this->actingAs($viewer)
            ->get(route('customers.show', ['phone' => '08111111111']))
            ->assertOk()
            ->assertSee('Budi Repeat')
            ->assertSee('08111111111')
            ->assertSee('Repeat Order')
            ->assertSee('History order')
            ->assertSee(route('orders.create', [
                'customer_name' => 'Budi Repeat',
                'customer_phone' => '08111111111',
            ]))
            ->assertDontSee('Sari');
    }

    public function test_orders_index_can_filter_by_customer_phone(): void
    {
        $viewer = $this->userWithPermissions(['view-orders']);
        $mine = $this->makeOrder('RA.3001', 'Budi', '08111111111');
        $other = $this->makeOrder('RA.3002', 'Sari', '08222222222');

        $response = $this->actingAs($viewer)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('orders.index', ['customer_phone' => '08111111111']), $this->orderDataTablesQuery());

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($mine->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_repeat_order_form_prefills_customer_fields(): void
    {
        $viewer = $this->userWithPermissions(['create-orders']);

        $this->actingAs($viewer)
            ->get(route('orders.create', [
                'customer_name' => 'Budi Repeat',
                'customer_phone' => '08111111111',
            ]))
            ->assertOk()
            ->assertSee('Repeat Order')
            ->assertSee('value="Budi Repeat"', false)
            ->assertSee('value="08111111111"', false);
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

    private function makeOrder(string $orderNumber, string $name, string $phone): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $status = OrderStatus::query()->firstOrCreate(['name' => 'Open'], ['color' => '#2563eb']);

        return Order::create([
            'order_number' => $orderNumber,
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function customerDataTablesQuery(): array
    {
        $columns = [
            ['data' => 'customer_name', 'name' => 'customer_name'],
            ['data' => 'customer_phone', 'name' => 'customer_phone'],
            ['data' => 'total_orders', 'name' => 'total_orders'],
            ['data' => 'last_order_at', 'name' => 'last_order_at'],
            ['data' => 'actions', 'name' => 'actions', 'orderable' => 'false', 'searchable' => 'false'],
        ];

        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => $columns,
            'order' => [
                ['column' => 3, 'dir' => 'desc'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderDataTablesQuery(): array
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
