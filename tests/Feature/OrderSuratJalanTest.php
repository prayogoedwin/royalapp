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
use Illuminate\Support\Facades\Storage;
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

    public function test_surat_jalan_returns_interactive_html_for_authorized_user(): void
    {
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RT.2026.10.0001', 'RT01', 'Towing');

        $this->actingAs($user)
            ->get(route('orders.surat-jalan', $order))
            ->assertOk()
            ->assertSee('SURAT JALAN', false)
            ->assertSee('Simpan', false)
            ->assertSee('Cetak', false)
            ->assertSee('Kunci Kontak', false)
            ->assertSee('sj-pad', false);
    }

    public function test_can_save_checklist_and_reopen_saved_state(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions(['show-orders']);
        $order = $this->makeOrder('RT.SAVE.1', 'RT01', 'Towing');
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs($user)
            ->postJson(route('orders.surat-jalan.save', $order), [
                'items' => [
                    ['name' => 'Kunci Kontak', 'status' => 'ada'],
                    ['name' => 'P3K', 'status' => 'tidak'],
                ],
                'signatures' => [
                    'pengirim' => $png,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('status', true);

        $order->refresh();
        $this->assertNotEmpty($order->surat_jalan_public_token);
        $this->assertSame('ada', collect($order->surat_jalan_checklist['items'])->firstWhere('name', 'Kunci Kontak')['status']);
        $this->assertSame('tidak', collect($order->surat_jalan_checklist['items'])->firstWhere('name', 'P3K')['status']);
        $this->assertNotEmpty($order->surat_jalan_checklist['ttd']['pengirim']);
        $this->assertDatabaseHas('order_photos', [
            'order_id' => $order->id,
            'title' => 'TTD Surat Jalan Pengirim',
        ]);

        $this->actingAs($user)
            ->get(route('orders.surat-jalan', $order))
            ->assertOk()
            ->assertSee('value="ada"', false)
            ->assertSee('checked', false)
            ->assertSee('Kirim WA', false)
            ->assertSee('data-wa="https://api.whatsapp.com/send?phone=', false)
            ->assertSee('whatsapp://send?phone=', false)
            ->assertSee('Salin link', false)
            ->assertDontSee('href="https://api.whatsapp.com', false);

        $this->get(route('surat-jalan.public', $order->surat_jalan_public_token))
            ->assertOk()
            ->assertSee('SURAT JALAN', false)
            ->assertSee('Kunci Kontak', false)
            ->assertDontSee('Simpan', false);

        $this->get(route('surat-jalan.public', $order->surat_jalan_public_token).'?download=1')
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="surat-jalan-RT.SAVE.1.html"');
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
        $this->assertNotEmpty($data['logo_src']);
        $this->assertStringStartsWith('data:image/png;base64,', $data['logo_src']);
        $this->assertSame('RT.0003', $data['nomor']);
        $this->assertSame('RT88', $data['kode_unit']);
        $this->assertSame('Avanza 2018', $data['jenis_kendaraan']);
        $this->assertSame('Avanza 2018', $data['merk_tipe']);
        $this->assertSame('B 1234 XYZ', $data['nopol']);
        $this->assertSame('Andi Driver', $data['driver']);
        $this->assertSame('Sari Asisten', $data['asisten']);
        $this->assertSame('Budi Santoso', $data['pengirim_nama']);
        $this->assertSame('0812000111', $data['pengirim_telepon']);
        $this->assertSame('Jl. Gatot Subroto No. 1', $data['pengirim_alamat']);
        $this->assertSame('0812999888', $data['penerima_telepon']);
        $this->assertSame('Bengkel Cikarang', $data['penerima_alamat']);
        $this->assertStringContainsString('Mogok', $data['keterangan']);
        $this->assertStringContainsString('Mogok', $data['catatan']);
        $this->assertStringContainsString('Lembar 3 rangkap', $data['pembayaran']);
        $this->assertContains('Kunci Kontak', $data['perlengkapan']);
        $this->assertContains('STNK / STCK Profit', $data['perlengkapan']);
        $this->assertContains('Dongkrak / Stang', $data['perlengkapan']);
        $this->assertContains('Segitiga Pengaman', $data['perlengkapan']);
        $this->assertContains('Reserve Ban / Ban Serep', $data['perlengkapan']);
        $this->assertCount(12, $data['perlengkapan_kiri']);
        $this->assertCount(11, $data['perlengkapan_kanan']);
        $this->assertSame('#4CA7DC', $data['brand_blue']);
        $this->assertNotEmpty($data['cars']['rear_front']);
        $this->assertNotEmpty($data['cars']['side_right']);
        $this->assertNotEmpty($data['cars']['front']);
    }

    public function test_ambulance_surat_jalan_uses_towing_logo_and_unit_nopol(): void
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
        $this->assertNotEmpty($data['logo_src']);
        $this->assertStringStartsWith('data:image/png;base64,', $data['logo_src']);
        $this->assertSame('Ambulance High Roof', $data['jenis_kendaraan']);
        $this->assertSame('B 9 RA', $data['nopol']);
        $this->assertSame('-', $data['penerima_telepon']);

        $html = view('orders.pdf.surat_jalan', $data)->render();
        $this->assertStringContainsString('SURAT JALAN', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('ROYAL TOWING', $html);
        $this->assertStringContainsString('No HP', $html);
        $this->assertStringContainsString('Kunci Kontak', $html);
        $this->assertStringContainsString('STNK / STCK Profit', $html);
        $this->assertStringContainsString('Kaca Spion Tengah (Dalam)', $html);
        $this->assertStringContainsString('Karpet Dalam Depan', $html);
        $this->assertStringContainsString('Dongkrak / Stang', $html);
        $this->assertStringContainsString('Penutup Tangki BBM', $html);
        $this->assertStringContainsString('Segitiga Pengaman', $html);
        $this->assertStringContainsString('ADA', $html);
        $this->assertStringContainsString('TIDAK', $html);
        $this->assertStringContainsString('#4CA7DC', $html);
        $this->assertStringContainsString('Kode Unit', $html);
        $this->assertStringContainsString('Merk / Tipe', $html);
        $this->assertStringContainsString('No Rangka', $html);
        $this->assertStringContainsString('PERHATIAN!', $html);
        $this->assertStringContainsString('PEMBAYARAN', $html);
        $this->assertStringContainsString('PENGIRIM', $html);
        $this->assertStringContainsString('PENERIMA', $html);
        $this->assertStringContainsString('DRIVER', $html);
        $this->assertStringNotContainsString('KONDISI KENDARAAN', $html);
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
