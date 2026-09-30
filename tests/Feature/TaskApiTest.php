<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\OrderStatus;
use App\Models\Position;
use App\Models\Task;
use App\Models\TaskCrew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_tasks_assigned_to_logged_in_employee(): void
    {
        [$sintia, $employee] = $this->makeEmployeeUser('Sintia Aulia');
        $pending = $this->makeStatus('Pending');
        $waiting = $this->makeStatus('Waiting');
        $ongoing = $this->makeStatus('Ongoing');
        $this->makeStatus('Done');

        $minePending = $this->makeTask('SERVICE KENDARAAN', $pending, $employee);
        $this->makeTask('LAPORAN PENGELUARAN JULY 2026', $waiting, $employee);
        $this->makeTask('UPDATE PEKERJAAN KAROSERI RT01', $ongoing, $employee);
        $this->makeTask('Cek website 31 Juli 2026', $pending, $employee);

        $other = $this->makeEmployeeUser('Joko Santoso')[1];
        $this->makeTask('Pengecekan Kendaraan RA01', $ongoing, $other);

        Sanctum::actingAs($sintia);

        $this->getJson('/api/stats/tasks/total')
            ->assertOk()
            ->assertJsonPath('data.total_tasks', 4)
            ->assertJsonPath('data.pending_tasks', 3);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(4, 'data.data')
            ->assertJsonFragment(['title' => 'SERVICE KENDARAAN'])
            ->assertJsonMissing(['title' => 'Pengecekan Kendaraan RA01']);

        $this->getJson('/api/tasks?status=menunggu')
            ->assertOk()
            ->assertJsonCount(3, 'data.data')
            ->assertJsonFragment(['title' => 'SERVICE KENDARAAN'])
            ->assertJsonFragment(['title' => 'LAPORAN PENGELUARAN JULY 2026'])
            ->assertJsonMissing(['title' => 'UPDATE PEKERJAAN KAROSERI RT01']);

        $this->getJson('/api/tasks/'.$minePending->id)
            ->assertOk()
            ->assertJsonPath('data.code', 'TSK-'.str_pad((string) $minePending->id, 3, '0', STR_PAD_LEFT))
            ->assertJsonPath('data.title', 'SERVICE KENDARAAN');
    }

    public function test_hides_unassigned_task_detail(): void
    {
        [$sintia, $employee] = $this->makeEmployeeUser('Sintia Aulia');
        $other = $this->makeEmployeeUser('Joko Santoso')[1];
        $task = $this->makeTask('Pengecekan Kendaraan RA01', $this->makeStatus('Ongoing'), $other);

        Sanctum::actingAs($sintia);

        $this->getJson('/api/tasks/'.$task->id)->assertNotFound();
        $this->assertNotNull($employee);
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

    private function makeTask(string $title, OrderStatus $status, Employee $employee): Task
    {
        $task = Task::create([
            'title' => $title,
            'description' => $title,
            'order_status_id' => $status->id,
        ]);

        TaskCrew::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
            'role' => 'Finance',
        ]);

        return $task;
    }
}
