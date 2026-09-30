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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_waiting_and_done_orders_for_logged_in_crew(): void
    {
        [$user, $employee] = $this->makeEmployeeUser();
        $pending = $this->makeStatus('Pending');
        $waiting = $this->makeStatus('Waiting');
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');
        $cancelled = $this->makeStatus('Cancelled');

        $this->makeOrder($pending, $employee);
        $this->makeOrder($waiting, $employee);
        $this->makeOrder($waiting, $employee);
        $this->makeOrder($ongoing, $employee);
        $this->makeOrder($done, $employee);
        $this->makeOrder($cancelled, $employee);

        $other = $this->makeEmployeeUser()[1];
        $this->makeOrder($done, $other);

        Sanctum::actingAs($user);

        $this->getJson('/api/stats/orders/total')
            ->assertOk()
            ->assertJsonPath('data.total_orders', 6)
            ->assertJsonPath('data.pending_orders', 1)
            ->assertJsonPath('data.waiting_orders', 2)
            ->assertJsonPath('data.ongoing_orders', 1)
            ->assertJsonPath('data.done_orders', 1)
            ->assertJsonPath('data.cancelled_orders', 1);
    }

    public function test_returns_zero_counts_when_user_has_no_employee(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/stats/orders/total')
            ->assertOk()
            ->assertJsonPath('data.total_orders', 0)
            ->assertJsonPath('data.pending_orders', 0)
            ->assertJsonPath('data.waiting_orders', 0)
            ->assertJsonPath('data.ongoing_orders', 0)
            ->assertJsonPath('data.done_orders', 0)
            ->assertJsonPath('data.cancelled_orders', 0);
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

    private function makeOrder(OrderStatus $status, Employee $employee): Order
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

        OrderCrew::create([
            'order_id' => $order->id,
            'employee_id' => $employee->id,
            'role' => 'driver',
        ]);

        return $order;
    }
}
