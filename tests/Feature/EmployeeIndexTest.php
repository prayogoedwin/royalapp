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

class EmployeeIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_employees(): void
    {
        $this->get(route('employees.index'))->assertRedirect('/login');
        $this->get(route('employees.pdf'))->assertRedirect('/login');
        $this->get(route('employees.excel'))->assertRedirect('/login');
    }

    public function test_user_without_permission_cannot_open_employees(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('employees.index'))->assertForbidden();
        $this->actingAs($user)->get(route('employees.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('employees.excel'))->assertForbidden();
    }

    public function test_employees_index_has_pdf_and_excel_downloads(): void
    {
        $viewer = $this->userWithPermissions(['view-employees']);
        $this->makeEmployee('SINTIA AULIA');

        $this->actingAs($viewer)
            ->get(route('employees.index'))
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('Download Excel');

        $pdf = $this->actingAs($viewer)->get(route('employees.pdf'));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $pdf->headers->get('content-disposition'));

        $excel = $this->actingAs($viewer)->get(route('employees.excel'));
        $excel->assertOk();
        $this->assertStringContainsString('.xlsx', (string) $excel->headers->get('content-disposition'));
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

    private function makeEmployee(string $name): Employee
    {
        $position = Position::query()->firstOrCreate(['nama' => 'Driver']);
        $division = Division::query()->firstOrCreate(['nama' => 'Office']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        return Employee::create([
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => '730803660900004',
            'full_name' => $name,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);
    }
}
