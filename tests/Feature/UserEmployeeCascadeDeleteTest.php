<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEmployeeCascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_user_also_soft_deletes_linked_employee(): void
    {
        $admin = $this->userWithPermissions(['delete-users']);
        [$user, $employee] = $this->makeLinkedUserAndEmployee();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }

    public function test_deleting_employee_also_soft_deletes_linked_user(): void
    {
        $admin = $this->userWithPermissions(['delete-employees']);
        [$user, $employee] = $this->makeLinkedUserAndEmployee();

        $this->actingAs($admin)
            ->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_own_account(): void
    {
        $admin = $this->userWithPermissions(['delete-users']);
        $employee = $this->makeEmployeeFor($admin);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect();

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
        $this->assertNotSoftDeleted('employees', ['id' => $employee->id]);
    }

    public function test_employee_edit_renders_when_user_account_is_missing(): void
    {
        $admin = $this->userWithPermissions(['edit-employees']);
        $employee = $this->makeEmployeeFor(null);

        $this->actingAs($admin)
            ->get(route('employees.edit', $employee))
            ->assertOk();
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
        $employee = $this->makeEmployeeFor($user);

        return [$user, $employee];
    }

    private function makeEmployeeFor(?User $user): Employee
    {
        $position = Position::query()->firstOrCreate(['nama' => 'Driver']);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        return Employee::create([
            'user_id' => $user?->id,
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-'.uniqid(),
            'full_name' => $user?->name ?? 'Asman Prayoga',
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);
    }
}
