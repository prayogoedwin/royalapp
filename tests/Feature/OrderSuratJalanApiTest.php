<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderStatus;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderSuratJalanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_surat_jalan(): void
    {
        [, $order] = $this->crewOrder();

        $this->get('/api/orders/'.$order->id.'/surat-jalan')
            ->assertUnauthorized()
            ->assertJsonPath('status', false);
    }

    public function test_crew_can_open_surat_jalan_html_with_bearer_token(): void
    {
        [$user, $order] = $this->crewOrder();
        Sanctum::actingAs($user);

        $this->get('/api/orders/'.$order->id.'/surat-jalan')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('SURAT JALAN', false)
            ->assertSee($order->order_number, false)
            ->assertSee('PENGIRIM', false)
            ->assertSee('PERLENGKAPAN KENDARAAN', false)
            ->assertSee('Simpan', false)
            ->assertSee('sj-pad', false);
    }

    public function test_crew_can_save_surat_jalan_checklist(): void
    {
        Storage::fake('public');
        [$user, $order] = $this->crewOrder();
        Sanctum::actingAs($user);
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->postJson('/api/orders/'.$order->id.'/surat-jalan', [
            'items' => [
                ['name' => 'Kunci Kontak', 'status' => 'ada'],
            ],
            'signatures' => [
                'driver' => $png,
            ],
        ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Surat jalan tersimpan.');

        $order->refresh();
        $this->assertSame('ada', collect($order->surat_jalan_checklist['items'])->firstWhere('name', 'Kunci Kontak')['status']);
        $this->assertNotEmpty($order->surat_jalan_public_token);
        $this->get(route('surat-jalan.public', $order->surat_jalan_public_token))
            ->assertOk()
            ->assertSee($order->order_number, false);
    }

    public function test_webview_can_open_surat_jalan_with_query_token(): void
    {
        [$user, $order] = $this->crewOrder();
        $token = $user->createToken('api-token')->plainTextToken;

        $this->get('/api/orders/'.$order->id.'/surat-jalan?token='.$token)
            ->assertOk()
            ->assertSee('SURAT JALAN', false)
            ->assertSee($order->customer_name, false);
    }

    public function test_non_crew_cannot_open_other_order_surat_jalan(): void
    {
        [, $order] = $this->crewOrder();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $this->get('/api/orders/'.$order->id.'/surat-jalan')
            ->assertNotFound()
            ->assertJsonPath('message', 'Order not found.');
    }

    /**
     * @return array{0: User, 1: Order}
     */
    private function crewOrder(): array
    {
        $user = User::factory()->create();
        $position = Position::query()->firstOrCreate(['nama' => 'Driver'], ['key' => 'driver']);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);
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
        $status = OrderStatus::query()->firstOrCreate(['name' => 'Ongoing'], ['color' => 'blue']);
        $order = Order::create([
            'order_number' => 'RA.'.uniqid(),
            'unit_code' => 'RA02',
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);
        OrderCrew::create([
            'order_id' => $order->id,
            'employee_id' => $employee->id,
            'role' => 'driver',
        ]);

        return [$user, $order];
    }
}
