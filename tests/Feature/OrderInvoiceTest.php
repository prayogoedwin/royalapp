<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderTowing;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_invoice(): void
    {
        $order = $this->makeOrder('RA.INV.1', 'RA01', 'Ambulance');

        $this->get(route('orders.invoice', $order))->assertRedirect();
    }

    public function test_user_without_permission_cannot_open_invoice(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder('RA.INV.2', 'RA01', 'Ambulance');

        $this->actingAs($user)
            ->get(route('orders.invoice', $order))
            ->assertForbidden();
    }

    public function test_invoice_without_brand_shows_ambulance_and_towing_choice(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RA.INV.3', 'RA01', 'Ambulance');

        $this->actingAs($user)
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('Pilih kop invoice', false)
            ->assertSee('Royal Ambulance', false)
            ->assertSee('Royal Towing', false)
            ->assertSee('brand=ambulance', false)
            ->assertSee('brand=towing', false)
            ->assertDontSee('Payment instruction', false);
    }

    public function test_ambulance_invoice_uses_ambulance_logo_name_and_bank_accounts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:00:00'));

        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RA.INV.4', 'RA02', 'Ambulance', [
            'customer_name' => 'Ibu Astuti',
            'customer_phone' => '087881712553',
            'pickup_address' => 'Cengkareng',
            'destination_address' => 'RSPAD',
            'price' => 1300000,
        ]);
        Unit::create([
            'division_id' => $order->division_id,
            'code' => 'RA02',
            'description' => 'Unit Luxio/APV',
            'nopol' => 'B 1 RA',
        ]);

        $this->actingAs($user)
            ->get(route('orders.invoice', [$order, 'brand' => 'ambulance']))
            ->assertOk()
            ->assertSee('INVOICE', false)
            ->assertSee('Royal Ambulance', false)
            ->assertSee('royal.emsindonesia@gmail.com', false)
            ->assertSee('+62811131000', false)
            ->assertSee('BCA 534-525-826-1 an Royal Medical Services PT', false)
            ->assertSee('Mandiri 1210019942899 an PT Royal Medical Services', false)
            ->assertDontSee('5345271144', false)
            ->assertSee('Ibu Astuti', false)
            ->assertSee('Sewa Ambulance Unit Luxio/APV', false)
            ->assertSee('Cengkareng - RSPAD', false)
            ->assertSee('Rp1,300,000', false)
            ->assertSee('INVOICE #', false);

        $order->refresh();
        $this->assertNotNull($order->invoice_number);
        $this->assertSame(1, (int) $order->invoice_number);
    }

    public function test_towing_invoice_uses_towing_logo_name_and_bank_account(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RT.INV.5', 'RT01', 'Towing', [
            'customer_name' => 'Friend Xpeng',
            'pickup_address' => 'Kelapa Gading',
            'destination_address' => 'Xpeng Sunter',
            'price' => 750000,
        ]);
        OrderTowing::create([
            'order_id' => $order->id,
            'car_type' => 'Hidrolik',
        ]);
        Unit::create([
            'division_id' => $order->division_id,
            'code' => 'RT01',
            'description' => 'Towing Hidrolik',
            'nopol' => 'B 2101 UNS',
        ]);

        $this->actingAs($user)
            ->get(route('orders.invoice', [$order, 'brand' => 'towing']))
            ->assertOk()
            ->assertSee('Royal Towing', false)
            ->assertSee('royaltowing.id@gmail.com', false)
            ->assertSee('+6281170009000', false)
            ->assertSee('BCA 5345271144 Royal Medical Services PT', false)
            ->assertDontSee('534-525-826-1', false)
            ->assertDontSee('1210019942899', false)
            ->assertSee('Friend Xpeng', false)
            ->assertSee('Sewa Towing Hidrolik', false)
            ->assertSee('Kelapa Gading - Xpeng Sunter', false)
            ->assertSee('Rp750,000', false)
            ->assertSee('B 2101 UNS', false);
    }

    public function test_one_order_keeps_the_same_invoice_number_on_reprint(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $first = $this->makeOrder('RA.INV.6', 'RA01', 'Ambulance', ['price' => 100000]);
        $second = $this->makeOrder('RA.INV.7', 'RA01', 'Ambulance', ['price' => 200000]);

        $this->actingAs($user)
            ->get(route('orders.invoice', [$first, 'brand' => 'ambulance']))
            ->assertOk()
            ->assertSee('data-invoice-number="1"', false);

        $this->actingAs($user)
            ->get(route('orders.invoice', [$second, 'brand' => 'towing']))
            ->assertOk()
            ->assertSee('data-invoice-number="2"', false);

        $this->actingAs($user)
            ->get(route('orders.invoice', [$first, 'brand' => 'towing']))
            ->assertOk()
            ->assertSee('data-invoice-number="1"', false)
            ->assertSee('Royal Towing', false)
            ->assertDontSee('data-invoice-number="3"', false);

        $this->assertSame(1, (int) $first->fresh()->invoice_number);
        $this->assertSame(2, (int) $second->fresh()->invoice_number);
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeOrder(string $orderNumber, string $unitCode, string $divisionName, array $overrides = []): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => $divisionName]);
        $status = OrderStatus::query()->firstOrCreate(
            ['name' => 'Open'],
            ['color' => '#2563eb']
        );

        return Order::create(array_merge([
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
        ], $overrides));
    }
}
