<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderStatus;
use App\Models\OrderTowing;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Support\SuratJalanData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSuratJalanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_surat_jalan(): void
    {
        $order = $this->makeOrder('RT.0001', 'RT01', 'Towing');

        $this->get(route('orders.surat-jalan', $order))
            ->assertRedirect();
    }

    public function test_user_without_permission_cannot_open_surat_jalan(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder('RT.0002', 'RT01', 'Towing');

        $this->actingAs($user)
            ->get(route('orders.surat-jalan', $order))
            ->assertForbidden();
    }

    public function test_surat_jalan_returns_pdf_for_authorized_user(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RT.2026.10.0001', 'RT01', 'Towing');

        $this->actingAs($user)
            ->get(route('orders.surat-jalan', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="surat-jalan-RT.2026.10.0001.pdf"');
    }

    public function test_surat_jalan_maps_vehicle_sender_and_receiver_automatically(): void
    {
        $order = $this->makeOrder('RT.0003', 'RT88', 'Towing', [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '0812000111',
            'pickup_address' => 'Jl. Gatot Subroto No. 1',
            'destination_address' => 'Bengkel Cikarang',
        ]);

        Unit::create([
            'division_id' => $order->division_id,
            'code' => 'RT88',
            'description' => 'Derek Medium',
            'nopol' => 'B 1234 XYZ',
        ]);

        OrderTowing::create([
            'order_id' => $order->id,
            'car_type' => 'Avanza 2018',
            'car_condition' => 'Mogok',
            'receiver_phone' => '0812999888',
        ]);

        $driver = $this->makeEmployee($order->division_id, 'Driver', 'Andi Driver');
        $asisten = $this->makeEmployee($order->division_id, 'Nurse', 'Sari Asisten');
        OrderCrew::create(['order_id' => $order->id, 'employee_id' => $driver->id, 'role' => 'driver']);
        OrderCrew::create(['order_id' => $order->id, 'employee_id' => $asisten->id, 'role' => 'asisten']);

        $order->refresh();
        $data = SuratJalanData::fromOrder($order);

        $this->assertTrue($data['is_towing']);
        $this->assertSame('TOWING', $data['brand_bottom']);
        $this->assertSame('RT.0003', $data['nomor']);
        $this->assertSame('Avanza 2018', $data['jenis_kendaraan']);
        $this->assertSame('B 1234 XYZ', $data['nopol']);
        $this->assertSame('Andi Driver', $data['driver']);
        $this->assertSame('Sari Asisten', $data['asisten']);
        $this->assertSame('Budi Santoso', $data['pengirim_nama']);
        $this->assertSame('0812000111', $data['pengirim_telepon']);
        $this->assertSame('Jl. Gatot Subroto No. 1', $data['pengirim_alamat']);
        $this->assertSame('0812999888', $data['penerima_telepon']);
        $this->assertSame('Bengkel Cikarang', $data['penerima_alamat']);
        $this->assertStringContainsString('Mogok', $data['keterangan']);
        $this->assertContains('Dongkrak', $data['perlengkapan']);
        $this->assertContains('Segitiga Pengaman', $data['perlengkapan']);
        $this->assertContains('Tool Kit', $data['perlengkapan']);
    }

    public function test_ambulance_surat_jalan_uses_text_logo_and_unit_nopol(): void
    {
        $order = $this->makeOrder('RA.0009', 'RA02', 'Ambulance');
        Unit::create([
            'division_id' => $order->division_id,
            'code' => 'RA02',
            'description' => 'Ambulance High Roof',
            'nopol' => 'B 9 RA',
        ]);

        $data = SuratJalanData::fromOrder($order->fresh());

        $this->assertFalse($data['is_towing']);
        $this->assertSame('AMBULANCE', $data['brand_bottom']);
        $this->assertSame('ROYAL', $data['brand_top']);
        $this->assertSame('Ambulance High Roof', $data['jenis_kendaraan']);
        $this->assertSame('B 9 RA', $data['nopol']);
        $this->assertSame('-', $data['penerima_telepon']);

        $html = view('orders.pdf.surat_jalan', $data)->render();
        $this->assertStringContainsString('SURAT JALAN', $html);
        $this->assertStringContainsString('ROYAL', $html);
        $this->assertStringContainsString('AMBULANCE', $html);
        $this->assertStringContainsString('Dongkrak', $html);
        $this->assertStringContainsString('Segitiga Pengaman', $html);
        $this->assertStringContainsString('Kunci Roda', $html);
        $this->assertStringContainsString('Ban Serep', $html);
        $this->assertStringContainsString('APAR', $html);
        $this->assertStringContainsString('P3K', $html);
        $this->assertStringContainsString('Tool Kit', $html);
        $this->assertStringContainsString('Lainnya', $html);
        $this->assertStringContainsString('RA.0009', $html);
        $this->assertStringContainsString('B 9 RA', $html);
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

    private function makeEmployee(int $divisionId, string $positionName, string $fullName): Employee
    {
        $position = Position::query()->firstOrCreate(['nama' => $positionName]);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        return Employee::create([
            'position_id' => $position->id,
            'division_id' => $divisionId,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-'.uniqid(),
            'full_name' => $fullName,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);
    }
}
