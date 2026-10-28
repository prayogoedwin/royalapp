<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderMapsCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_shows_optional_maps_search(): void
    {
        $user = $this->userWithPermissions(['create-orders']);
        Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        OrderStatus::query()->firstOrCreate(['name' => 'Pending'], ['color' => 'yellow']);

        $this->actingAs($user)
            ->get(route('orders.create'))
            ->assertOk()
            ->assertSee('Cari alamat (seperti Google)', false)
            ->assertSee('Link Google Maps (opsional)', false);
    }

    public function test_can_create_order_without_coordinates(): void
    {
        $user = $this->userWithPermissions(['create-orders', 'view-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $this->actingAs($user)
            ->post(route('orders.store'), $this->orderPayload($division->id, $status->id))
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Budi',
            'pickup_latitude' => null,
            'destination_latitude' => null,
        ]);
    }

    public function test_can_create_order_with_pickup_and_destination_coordinates(): void
    {
        $user = $this->userWithPermissions(['create-orders', 'view-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $this->actingAs($user)
            ->post(route('orders.store'), $this->orderPayload($division->id, $status->id, [
                'pickup_latitude' => '-6.2088',
                'pickup_longitude' => '106.8456',
                'destination_latitude' => '-6.9175',
                'destination_longitude' => '107.6191',
            ]))
            ->assertRedirect(route('orders.index'));

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertEqualsWithDelta(-6.2088, (float) $order->pickup_latitude, 0.0001);
        $this->assertEqualsWithDelta(107.6191, (float) $order->destination_longitude, 0.0001);
        $this->assertStringContainsString('google.com/maps', (string) $order->pickup_maps_url);
        $this->assertStringContainsString('google.com/maps', (string) $order->destination_maps_url);
    }

    public function test_pasted_maps_url_fills_coordinates(): void
    {
        $user = $this->userWithPermissions(['create-orders', 'view-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $this->actingAs($user)
            ->post(route('orders.store'), $this->orderPayload($division->id, $status->id, [
                'pickup_maps_url' => 'https://www.google.com/maps/place/Monas/@-6.175392,106.827153,17z',
            ]))
            ->assertRedirect(route('orders.index'));

        $order = Order::query()->first();
        $this->assertEqualsWithDelta(-6.175392, (float) $order->pickup_latitude, 0.0001);
        $this->assertEqualsWithDelta(106.827153, (float) $order->pickup_longitude, 0.0001);
        $this->assertSame(
            'https://www.google.com/maps/place/Monas/@-6.175392,106.827153,17z',
            $order->getAttributes()['pickup_maps_url'] ?? $order->pickup_maps_url
        );
    }

    public function test_api_order_detail_includes_google_maps_links(): void
    {
        $user = User::factory()->create();
        [$order] = $this->crewOrder($user, [
            'pickup_latitude' => -6.2088,
            'pickup_longitude' => 106.8456,
            'destination_latitude' => -6.9175,
            'destination_longitude' => 107.6191,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.pickup_latitude', -6.2088)
            ->assertJsonPath('data.destination_longitude', 107.6191)
            ->assertJsonPath('data.pickup_maps_url', $order->pickup_maps_url)
            ->assertJsonPath('data.destination_maps_url', $order->destination_maps_url);
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
     * @return array{0: Division, 1: OrderStatus}
     */
    private function divisionAndStatus(): array
    {
        return [
            Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']),
            OrderStatus::query()->firstOrCreate(['name' => 'Pending'], ['color' => 'yellow']),
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function orderPayload(int $divisionId, int $statusId, array $overrides = []): array
    {
        return array_merge([
            'division_id' => $divisionId,
            'order_status_id' => $statusId,
            'customer_name' => 'Budi',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now()->format('Y-m-d H:i:s'),
            'price' => 0,
            'payment_status' => 'UNPAID',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: Order}
     */
    private function crewOrder(User $user, array $overrides = []): array
    {
        $position = Position::query()->firstOrCreate(['nama' => 'Driver'], ['key' => 'driver']);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);
        $status = OrderStatus::query()->firstOrCreate(['name' => 'Ongoing'], ['color' => 'blue']);

        $employee = Employee::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-'.uniqid(),
            'full_name' => 'Driver Test',
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);

        $order = Order::create(array_merge([
            'order_number' => 'RA.'.uniqid(),
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ], $overrides));

        OrderCrew::create([
            'order_id' => $order->id,
            'employee_id' => $employee->id,
            'role' => 'driver',
        ]);

        return [$order->fresh()];
    }
}
