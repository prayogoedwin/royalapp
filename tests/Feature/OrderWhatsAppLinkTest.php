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

class OrderWhatsAppLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_detail_phone_opens_whatsapp_web(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('0813-2356-7354');

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('https://web.whatsapp.com/send?phone=6281323567354', false)
            ->assertSee('0813-2356-7354');
    }

    public function test_edit_order_shows_whatsapp_button(): void
    {
        $user = $this->userWithPermissions(['edit-orders', 'show-orders']);
        $order = $this->makeOrder('0813-2356-7354');

        $this->actingAs($user)
            ->get(route('orders.edit', $order))
            ->assertOk()
            ->assertSee('WhatsApp')
            ->assertSee('Buka WhatsApp Web')
            ->assertSee('https://web.whatsapp.com/send?phone=6281323567354', false);
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

    private function makeOrder(string $phone): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $status = OrderStatus::query()->firstOrCreate(['name' => 'Open'], ['color' => '#2563eb']);

        return Order::create([
            'order_number' => 'RA.WA.001',
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Iqr - Mylo',
            'customer_phone' => $phone,
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
    }
}
