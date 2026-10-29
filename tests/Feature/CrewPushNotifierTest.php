<?php

namespace Tests\Feature;

use App\Jobs\SendCrewPushNotification;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderStatus;
use App\Models\Position;
use App\Models\Task;
use App\Models\TaskCrew;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\Firebase\CrewPushNotifier;
use App\Services\Firebase\FcmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CrewPushNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_all_waiting_order_crew_except_actor(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        $actor = User::factory()->create();
        $this->actingAs($actor);

        [$firstUser, $firstEmployee] = $this->makeEmployeeUser('Driver Satu');
        [$secondUser, $secondEmployee] = $this->makeEmployeeUser('Perawat Dua');

        UserDevice::create(['user_id' => $firstUser->id, 'fcm_token' => 'tok-1']);
        UserDevice::create(['user_id' => $secondUser->id, 'fcm_token' => 'tok-2']);
        UserDevice::create(['user_id' => $actor->id, 'fcm_token' => 'tok-admin']);

        $order = $this->makeOrder('Waiting', [$firstEmployee, $secondEmployee]);

        app(CrewPushNotifier::class)->notifyOrderCreated($order);

        Bus::assertDispatched(SendCrewPushNotification::class, function (SendCrewPushNotification $job) use ($order) {
            sort($job->tokens);

            return $job->tokens === ['tok-1', 'tok-2']
                && $job->data['type'] === 'order_assigned'
                && $job->data['order_id'] === (string) $order->id
                && $job->data['id'] === (string) $order->id
                && str_contains($job->body, '#'.$order->id)
                && str_contains($job->body, (string) $order->order_number);
        });
    }

    public function test_skips_order_create_notification_when_status_is_not_waiting(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        [$user, $employee] = $this->makeEmployeeUser('Driver Satu');
        UserDevice::create(['user_id' => $user->id, 'fcm_token' => 'tok-1']);

        $order = $this->makeOrder('Ongoing', [$employee]);

        app(CrewPushNotifier::class)->notifyOrderCreated($order);

        Bus::assertNotDispatched(SendCrewPushNotification::class);
    }

    public function test_does_not_dispatch_when_firebase_disabled(): void
    {
        Bus::fake();

        $this->mock(FcmClient::class, function ($mock) {
            $mock->shouldReceive('isEnabled')->andReturn(false);
        });

        [$user, $employee] = $this->makeEmployeeUser('Driver Satu');
        UserDevice::create(['user_id' => $user->id, 'fcm_token' => 'tok-1']);
        $order = $this->makeOrder('Waiting', [$employee]);

        app(CrewPushNotifier::class)->notifyOrderCreated($order);

        Bus::assertNotDispatched(SendCrewPushNotification::class);
    }

    public function test_notifies_task_crew_on_create(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        [$user, $employee] = $this->makeEmployeeUser('Sintia');
        UserDevice::create(['user_id' => $user->id, 'fcm_token' => 'tok-task']);

        $task = Task::create([
            'title' => 'Cek unit RA01',
            'order_status_id' => $this->makeStatus('Waiting')->id,
        ]);
        TaskCrew::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
        ]);

        app(CrewPushNotifier::class)->notifyTaskCreated($task->fresh(['taskCrews.employee']));

        Bus::assertDispatched(SendCrewPushNotification::class, function (SendCrewPushNotification $job) {
            return $job->tokens === ['tok-task'] && $job->data['type'] === 'task_assigned';
        });
    }

    public function test_notifies_creator_when_status_becomes_done(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        $creator = User::factory()->create();
        $actor = User::factory()->create();
        $this->actingAs($actor);

        [$driverUser, $driverEmployee] = $this->makeEmployeeUser('Budi Driver');
        UserDevice::create(['user_id' => $creator->id, 'fcm_token' => 'tok-creator']);
        UserDevice::create(['user_id' => $driverUser->id, 'fcm_token' => 'tok-driver']);

        $order = $this->makeOrder('Done', [$driverEmployee], $creator->id);
        $previous = $this->makeStatus('Ongoing');

        app(CrewPushNotifier::class)->notifyCreatorOrderDone($order, (int) $previous->id);

        Bus::assertDispatched(SendCrewPushNotification::class, function (SendCrewPushNotification $job) use ($order) {
            return $job->tokens === ['tok-creator']
                && $job->title === 'Order done'
                && $job->body === 'Budi Driver dengan no order '.$order->order_number.' sudah done, cek website'
                && $job->data['type'] === 'order_done'
                && $job->data['order_id'] === (string) $order->id;
        });
    }

    public function test_does_not_notify_creator_when_status_was_already_done(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        $creator = User::factory()->create();
        UserDevice::create(['user_id' => $creator->id, 'fcm_token' => 'tok-creator']);
        [$driverUser, $driverEmployee] = $this->makeEmployeeUser('Budi Driver');
        $order = $this->makeOrder('Done', [$driverEmployee], $creator->id);

        app(CrewPushNotifier::class)->notifyCreatorOrderDone($order, (int) $order->order_status_id);

        Bus::assertNotDispatched(SendCrewPushNotification::class);
    }

    public function test_does_not_notify_creator_when_status_is_not_done(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        $creator = User::factory()->create();
        UserDevice::create(['user_id' => $creator->id, 'fcm_token' => 'tok-creator']);
        [$driverUser, $driverEmployee] = $this->makeEmployeeUser('Budi Driver');
        $order = $this->makeOrder('Ongoing', [$driverEmployee], $creator->id);
        $previous = $this->makeStatus('Waiting');

        app(CrewPushNotifier::class)->notifyCreatorOrderDone($order, (int) $previous->id);

        Bus::assertNotDispatched(SendCrewPushNotification::class);
    }

    public function test_api_report_done_notifies_created_by_account(): void
    {
        Bus::fake();
        $this->mockEnabledFcm();

        $creator = User::factory()->create();
        UserDevice::create(['user_id' => $creator->id, 'fcm_token' => 'tok-creator']);

        [$driverUser, $driverEmployee] = $this->makeEmployeeUser('Andi Sopir');
        $done = $this->makeStatus('Done');
        $order = $this->makeOrder('Ongoing', [$driverEmployee], $creator->id);

        Sanctum::actingAs($driverUser);

        $this->putJson('/api/orders/'.$order->id.'/report', [
            'km_awal' => 10,
            'km_akhir' => 20,
            'order_status_id' => $done->id,
        ])->assertOk();

        Bus::assertDispatched(SendCrewPushNotification::class, function (SendCrewPushNotification $job) use ($order) {
            return $job->tokens === ['tok-creator']
                && $job->body === 'Andi Sopir dengan no order '.$order->order_number.' sudah done, cek website'
                && $job->data['type'] === 'order_done';
        });
    }

    private function mockEnabledFcm(): void
    {
        $this->mock(FcmClient::class, function ($mock) {
            $mock->shouldReceive('isEnabled')->andReturn(true);
        });
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function makeEmployeeUser(string $name): array
    {
        $user = User::factory()->create(['name' => $name]);
        $position = Position::query()->firstOrCreate(['nama' => 'Driver']);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        $employee = Employee::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-'.uniqid(),
            'full_name' => $name,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);

        return [$user->fresh(), $employee];
    }

    private function makeStatus(string $name): OrderStatus
    {
        return OrderStatus::query()->firstOrCreate(['name' => $name], ['color' => 'gray']);
    }

    /**
     * @param  list<Employee>  $employees
     */
    private function makeOrder(string $statusName, array $employees, ?int $createdBy = null): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $status = $this->makeStatus($statusName);

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
            'created_by' => $createdBy,
        ]);

        foreach ($employees as $employee) {
            OrderCrew::create([
                'order_id' => $order->id,
                'employee_id' => $employee->id,
                'role' => 'driver',
            ]);
        }

        return $order->fresh(['orderStatus', 'orderCrews.employee']);
    }
}
