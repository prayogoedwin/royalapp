<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderPhoto;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_shows_optional_payment_proof(): void
    {
        $user = $this->userWithPermissions(['create-orders']);
        Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        OrderStatus::query()->firstOrCreate(['name' => 'Pending'], ['color' => 'yellow']);

        $this->actingAs($user)
            ->get(route('orders.create'))
            ->assertOk()
            ->assertSee('Bukti Pembayaran', false)
            ->assertSee('name="payment_proof"', false);
    }

    public function test_can_create_order_without_payment_proof(): void
    {
        $user = $this->userWithPermissions(['create-orders', 'view-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $this->actingAs($user)
            ->post(route('orders.store'), $this->orderPayload($division->id, $status->id))
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Budi',
            'payment_proof' => null,
        ]);
    }

    public function test_can_create_order_with_payment_proof(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions(['create-orders', 'view-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $this->actingAs($user)
            ->post(route('orders.store'), $this->orderPayload($division->id, $status->id, [
                'payment_proof' => UploadedFile::fake()->image('bukti.jpg'),
            ]))
            ->assertRedirect(route('orders.index'));

        $order = Order::query()->first();
        $this->assertNotNull($order?->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);
        $this->assertStringContainsString('order-payment-proof', $order->payment_proof);
        $this->assertNotNull($order->foto_bukti_pembayaran_url);
    }

    public function test_edit_and_show_display_uploaded_payment_proof(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions(['edit-orders', 'show-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $path = UploadedFile::fake()->image('bukti.jpg')->store('uploads/2026/10/order-payment-proof', 'public');
        $order = Order::create($this->orderPayload($division->id, $status->id, [
            'order_number' => 'RA.0001',
            'payment_proof' => $path,
        ]));

        $this->actingAs($user)
            ->get(route('orders.edit', $order))
            ->assertOk()
            ->assertSee('Bukti Pembayaran', false)
            ->assertSee(asset('storage/'.$path), false)
            ->assertSee('name="payment_proof"', false)
            ->assertSee('Perbesar', false)
            ->assertSee('Perkecil', false);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Bukti Pembayaran', false)
            ->assertSee(asset('storage/'.$path), false)
            ->assertSee('Perbesar', false)
            ->assertSee('Perkecil', false);
    }

    public function test_order_photos_lightbox_has_zoom_controls(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions(['edit-orders', 'show-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $path = UploadedFile::fake()->image('foto.jpg')->store('uploads/2026/10/orders', 'public');
        $order = Order::create($this->orderPayload($division->id, $status->id, [
            'order_number' => 'RA.0003',
        ]));
        OrderPhoto::create([
            'order_id' => $order->id,
            'title' => 'Royal APP',
            'path' => $path,
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('imageLightbox()', false)
            ->assertSee('Perbesar', false)
            ->assertSee('Perkecil', false);

        $this->actingAs($user)
            ->get(route('orders.edit', $order))
            ->assertOk()
            ->assertSee('imageLightbox()', false)
            ->assertSee('Perbesar', false)
            ->assertSee('Perkecil', false);
    }

    public function test_edit_can_replace_payment_proof(): void
    {
        Storage::fake('public');
        $user = $this->userWithPermissions(['edit-orders']);
        [$division, $status] = $this->divisionAndStatus();

        $oldPath = UploadedFile::fake()->image('lama.jpg')->store('uploads/2026/10/order-payment-proof', 'public');
        $order = Order::create($this->orderPayload($division->id, $status->id, [
            'order_number' => 'RA.0002',
            'payment_proof' => $oldPath,
        ]));

        $this->actingAs($user)
            ->put(route('orders.update', $order), $this->orderPayload($division->id, $status->id, [
                'update_section' => 'basic',
                'payment_proof' => UploadedFile::fake()->image('baru.jpg'),
            ]))
            ->assertRedirect(route('orders.edit', $order));

        $order->refresh();
        $this->assertNotSame($oldPath, $order->payment_proof);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    public function test_api_uploads_one_payment_proof_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        [$order] = $this->crewOrder($user);

        Sanctum::actingAs($user);

        $this->post('/api/orders/bukti-pembayaran', [
            'id_order' => $order->id,
            'foto_bukti_pembayaran' => UploadedFile::fake()->image('bukti.png'),
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id_order', $order->id);

        $order->refresh();
        $this->assertNotNull($order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);

        $this->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.payment_proof', $order->payment_proof)
            ->assertJsonPath('data.foto_bukti_pembayaran', $order->payment_proof)
            ->assertJsonPath('data.foto_bukti_pembayaran_url', $order->foto_bukti_pembayaran_url);
    }

    public function test_api_rejects_upload_when_user_is_not_crew(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $outsider = User::factory()->create();
        [$order] = $this->crewOrder($user);

        Sanctum::actingAs($outsider);

        $this->post('/api/orders/bukti-pembayaran', [
            'id_order' => $order->id,
            'foto_bukti_pembayaran' => UploadedFile::fake()->image('bukti.png'),
        ])->assertNotFound();
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
