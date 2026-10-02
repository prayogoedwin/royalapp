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

    public function test_update_vehicle_issue_reads_multipart_put_body(): void
    {
        [$user, $order] = $this->crewOrder();
        $issue = OrderVehicleIssue::create([
            'order_id' => $order->id,
            'issue_category' => 'mechanical',
            'description' => 'Rem tidak pakem',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($user);

        $boundary = '----RoyalBoundary';
        $body = implode("\r\n", [
            '--'.$boundary,
            'Content-Disposition: form-data; name="issue_category"',
            '',
            'other',
            '--'.$boundary,
            'Content-Disposition: form-data; name="description"',
            '',
            'Tambah angin',
            '--'.$boundary,
            'Content-Disposition: form-data; name="priority"',
            '',
            'low',
            '--'.$boundary.'--',
            '',
        ]);

        $this->call(
            'PUT',
            '/api/orders/'.$order->id.'/vehicle-issues/'.$issue->id,
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'multipart/form-data; boundary='.$boundary,
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body,
        )
            ->assertOk()
            ->assertJsonPath('data.issue_category', 'other')
            ->assertJsonPath('data.description', 'Tambah angin')
            ->assertJsonPath('data.priority', 'low');
    }

    public function test_update_vehicle_issue_ignores_empty_priority_from_multipart(): void
    {
        [$user, $order] = $this->crewOrder();
        $issue = OrderVehicleIssue::create([
            'order_id' => $order->id,
            'issue_category' => 'mechanical',
            'description' => 'Rem tidak pakem',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($user);

        $boundary = '----RoyalBoundaryEmpty';
        $body = implode("\r\n", [
            '--'.$boundary,
            'Content-Disposition: form-data; name="description"',
            '',
            'Update tanpa priority',
            '--'.$boundary,
            'Content-Disposition: form-data; name="priority"',
            '',
            '',
            '--'.$boundary,
            'Content-Disposition: form-data; name="issue_category"',
            '',
            'string',
            '--'.$boundary.'--',
            '',
        ]);

        $this->call(
            'PUT',
            '/api/orders/'.$order->id.'/vehicle-issues/'.$issue->id,
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'multipart/form-data; boundary='.$boundary,
                'HTTP_ACCEPT' => 'application/json',
            ],
            $body,
        )
            ->assertOk()
            ->assertJsonPath('data.description', 'Update tanpa priority')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.issue_category', 'mechanical');
    }

    public function test_update_vehicle_issue_via_post_multipart(): void
    {
        [$user, $order] = $this->crewOrder();
        $issue = OrderVehicleIssue::create([
            'order_id' => $order->id,
            'issue_category' => 'mechanical',
            'description' => 'Rem tidak pakem',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($user);

        $this->post('/api/orders/'.$order->id.'/vehicle-issues/'.$issue->id, [
            'issue_category' => 'other',
            'description' => 'Tambah angin',
            'priority' => 'low',
        ], [
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('data.issue_category', 'other')
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
