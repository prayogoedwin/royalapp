<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderVehicleIssue;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderVehicleIssueIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_when_related_order_is_soft_deleted(): void
    {
        $user = $this->userWithPermissions([
            'view-order-vehicle-issues',
            'show-order-vehicle-issues',
        ]);

        $order = $this->makeOrder('RA.0001', 'RA02');
        $issue = OrderVehicleIssue::create([
            'order_id' => $order->id,
            'unit_code' => 'RA02',
            'issue_category' => 'mechanical',
            'description' => 'Mesin overheat',
            'priority' => 'high',
            'created_by' => $user->id,
        ]);

        $order->delete();

        $this->actingAs($user)
            ->get(route('order-vehicle-issues.index'))
            ->assertOk()
            ->assertSee('RA.0001 (terhapus)', false)
            ->assertDontSee(route('orders.show', $order), false);

        $this->actingAs($user)
            ->get(route('order-vehicle-issues.show', $issue))
            ->assertOk()
            ->assertSee('RA.0001 (terhapus)', false);
    }

    public function test_index_still_links_to_active_orders(): void
    {
        $user = $this->userWithPermissions(['view-order-vehicle-issues']);
        $order = $this->makeOrder('RA.0008', 'RA01');

        OrderVehicleIssue::create([
            'order_id' => $order->id,
            'unit_code' => 'RA01',
            'issue_category' => 'body',
            'description' => 'Baret pintu',
            'priority' => 'low',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('order-vehicle-issues.index'))
            ->assertOk()
            ->assertSee('RA.0008', false)
            ->assertSee(route('orders.show', $order), false)
            ->assertDontSee('terhapus', false);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'tester']);
        $permissionIds = [];

        foreach ($permissions as $name) {
            $permissionIds[] = Permission::create(['name' => $name])->id;
        }

        $role->permissions()->attach($permissionIds);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeOrder(string $orderNumber, string $unitCode): Order
    {
        $division = Division::create(['nama' => 'Ambulance']);
        $status = OrderStatus::create(['name' => 'Open', 'color' => '#2563eb']);

        return Order::create([
            'order_number' => $orderNumber,
            'unit_code' => $unitCode,
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
    }
}
