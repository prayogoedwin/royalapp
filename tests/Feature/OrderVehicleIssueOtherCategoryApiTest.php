<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderStatus;
use App\Models\OrderVehicleIssue;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderVehicleIssueOtherCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_vehicle_issue_accepts_other_category(): void
    {
        [$user, $order] = $this->crewOrder();
        Sanctum::actingAs($user);

        $this->postJson('/api/orders/'.$order->id.'/vehicle-issues', [
            'issue_category' => 'other',
            'description' => 'Tambah angin',
            'priority' => 'low',
        ])
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.issue_category', 'other')
            ->assertJsonPath('data.description', 'Tambah angin');

        $this->assertDatabaseHas('order_vehicle_issues', [
            'order_id' => $order->id,
            'issue_category' => 'other',
            'description' => 'Tambah angin',
        ]);
    }

    public function test_update_vehicle_issue_persists_other_category_from_json(): void
    {
        [$user, $order] = $this->crewOrder();
        $issue = OrderVehicleIssue::create([
            'order_id' => $order->id,
            'issue_category' => 'mechanical',
            'description' => 'Rem tidak pakem',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/orders/'.$order->id.'/vehicle-issues/'.$issue->id, [
            'issue_category' => 'other',
            'description' => 'Tambah angin',
            'priority' => 'low',
        ])
            ->assertOk()
            ->assertJsonPath('data.issue_category', 'other')
            ->assertJsonPath('data.description', 'Tambah angin')
            ->assertJsonPath('data.priority', 'low');
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

        return [$user, $order];
    }
}
