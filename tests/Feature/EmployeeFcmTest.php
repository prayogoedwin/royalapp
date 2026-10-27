<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\Firebase\FcmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeFcmTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_show_reports_missing_fcm_token(): void
    {
        $admin = $this->userWithPermissions(['show-employees']);
        [$user, $employee] = $this->makeLinkedUserAndEmployee();

        $this->actingAs($admin)
            ->get(route('employees.show', $employee))
            ->assertOk()
            ->assertSee('Belum punya FCM token')
            ->assertSee('Tes notifikasi');

        $this->assertNotNull($user);
    }

    public function test_employee_show_reports_existing_fcm_token(): void
    {
        $admin = $this->userWithPermissions(['show-employees']);
        [$user, $employee] = $this->makeLinkedUserAndEmployee();
        UserDevice::create([
            'user_id' => $user->id,
            'fcm_token' => 'token-tes',
            'last_used_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('employees.show', $employee))
            ->assertOk()
            ->assertSee('Sudah punya FCM token (1 perangkat)');
    }

    public function test_test_notification_sends_to_employee_devices(): void
    {
        $admin = $this->userWithPermissions(['show-employees']);
        [$user, $employee] = $this->makeLinkedUserAndEmployee();
        UserDevice::create([
            'user_id' => $user->id,
            'fcm_token' => 'token-tes',
        ]);

        $this->mock(FcmClient::class, function ($mock) {
            $mock->shouldReceive('isEnabled')->andReturn(true);
            $mock->shouldReceive('sendToTokens')
                ->once()
                ->with(['token-tes'], 'Tes notifikasi', \Mockery::type('string'), \Mockery::type('array'))
                ->andReturn(1);
        });

        $this->actingAs($admin)
            ->post(route('employees.test-notification', $employee))
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_test_notification_fails_without_token(): void
    {
        $admin = $this->userWithPermissions(['show-employees']);
        [, $employee] = $this->makeLinkedUserAndEmployee();

        $this->actingAs($admin)
            ->post(route('employees.test-notification', $employee))
            ->assertRedirect()
            ->assertSessionHasErrors('error');
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
     * @return array{0: User, 1: Employee}
     */
    private function makeLinkedUserAndEmployee(): array
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
            'full_name' => $user->name,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);

        return [$user, $employee];
    }
}
