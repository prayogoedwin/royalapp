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
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCompletionTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_driver_done_stamps_jakarta_time_and_ignores_client_datetime(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        Carbon::setTestNow(Carbon::parse('2026-09-30 14:05:00', 'Asia/Jakarta'));

        [$user, $employee] = $this->makeEmployeeUser();
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');
        $order = $this->makeOrder($ongoing, $employee);

        Sanctum::actingAs($user);

        $this->putJson('/api/orders/'.$order->id.'/report', [
            'km_awal' => 100,
            'km_akhir' => 120,
            'order_status_id' => $done->id,
            'deliver_datetime' => '2020-01-01 08:00:00',
        ])->assertOk();

        $report = $order->fresh()->orderReport;
        $this->assertNotNull($report);
        $this->assertSame('Asia/Jakarta', $report->deliver_datetime?->timezoneName);
        $this->assertSame('2026-09-30 14:05:00', $report->deliver_datetime?->format('Y-m-d H:i:s'));
        $this->assertSame($done->id, $order->fresh()->order_status_id);

        Carbon::setTestNow(Carbon::parse('2026-09-30 18:40:00', 'Asia/Jakarta'));

        $this->putJson('/api/orders/'.$order->id.'/report', [
            'km_awal' => 100,
            'km_akhir' => 130,
            'order_status_id' => $done->id,
            'deliver_datetime' => '2030-01-01 08:00:00',
        ])->assertOk();

        $this->assertSame(
            '2026-09-30 14:05:00',
            $order->fresh()->orderReport?->deliver_datetime?->format('Y-m-d H:i:s')
        );
    }

    public function test_report_update_without_done_leaves_completion_time_empty(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-30 14:05:00', 'Asia/Jakarta'));

        [$user, $employee] = $this->makeEmployeeUser();
        $ongoing = $this->makeStatus('Ongoing');
        $order = $this->makeOrder($ongoing, $employee);

        Sanctum::actingAs($user);

        $this->putJson('/api/orders/'.$order->id.'/report', [
            'km_awal' => 10,
            'km_akhir' => 12,
            'order_status_id' => $ongoing->id,
            'deliver_datetime' => '2026-09-30 14:05:00',
        ])->assertOk();

        $this->assertNull($order->fresh()->orderReport?->deliver_datetime);
    }

    public function test_admin_report_form_stamps_completion_time_when_status_is_done(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-30 09:15:00', 'Asia/Jakarta'));

        $user = $this->userWithPermissions(['edit-order-report']);
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');
        $order = $this->makeOrder($ongoing);

        $this->actingAs($user)
            ->put(route('orders.update', $order), [
                'update_section' => 'order_report',
                'km_awal' => 50,
                'km_akhir' => 80,
                'order_status_id' => $done->id,
                'deliver_datetime' => '2020-01-01 08:00:00',
            ])
            ->assertRedirect(route('orders.edit', $order));

        $report = $order->fresh()->orderReport;
        $this->assertSame('2026-09-30 09:15:00', $report?->deliver_datetime?->format('Y-m-d H:i:s'));
        $this->assertSame('Asia/Jakarta', $report?->deliver_datetime?->timezoneName);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function makeEmployeeUser(): array
    {
        $user = User::factory()->create();
        $position = Position::query()->firstOrCreate(['nama' => 'Driver']);
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

        return [$user->fresh(), $employee];
    }

    private function makeStatus(string $name): OrderStatus
    {
        return OrderStatus::query()->firstOrCreate(['name' => $name], ['color' => 'gray']);
    }

    private function makeOrder(OrderStatus $status, ?Employee $employee = null): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);

        $order = Order::create([
            'order_number' => 'RA.'.uniqid(),
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);

        if ($employee !== null) {
            OrderCrew::create([
                'order_id' => $order->id,
                'employee_id' => $employee->id,
                'role' => 'driver',
            ]);
        }

        return $order;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'tester-'.uniqid()]);
        $permissionIds = [];

        foreach ($permissions as $name) {
            $permissionIds[] = Permission::query()->firstOrCreate(['name' => $name])->id;
        }

        $role->permissions()->attach($permissionIds);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
