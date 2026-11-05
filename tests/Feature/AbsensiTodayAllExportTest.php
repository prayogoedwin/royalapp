<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiTodayAllExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_date_range_download_controls(): void
    {
        $viewer = $this->userWithPermissions(['view-presensi-all']);
        $this->makeEmployee('AGASIRA WISANG KANDHA');

        $this->actingAs($viewer)
            ->get(route('absensi.today.all', ['tanggal' => '2026-11-06']))
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('Download Excel')
            ->assertSee('Dari')
            ->assertSee('Sampai')
            ->assertSee('1 halaman per tanggal', false);
    }

    public function test_pdf_export_covers_each_day_in_range(): void
    {
        $viewer = $this->userWithPermissions(['view-presensi-all']);
        $employee = $this->makeEmployee('AGASIRA WISANG KANDHA');

        Absensi::create([
            'employee_id' => $employee->id,
            'tanggal' => '2026-11-01',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'status' => Absensi::STATUS_HADIR,
        ]);

        Absensi::create([
            'employee_id' => $employee->id,
            'tanggal' => '2026-11-03',
            'jam_masuk' => '09:15:00',
            'status' => Absensi::STATUS_TERLAMBAT,
        ]);

        $pdf = $this->actingAs($viewer)->get(route('absensi.today.pdf', [
            'start' => '2026-11-01',
            'end' => '2026-11-03',
        ]));

        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString(
            'absensi-semua-pegawai-2026-11-01_2026-11-03.pdf',
            (string) $pdf->headers->get('content-disposition')
        );
    }

    public function test_excel_export_accepts_date_range(): void
    {
        $viewer = $this->userWithPermissions(['view-presensi-all']);
        $this->makeEmployee('ALIS SANDRI');

        $excel = $this->actingAs($viewer)->get(route('absensi.today.excel', [
            'start' => '2026-11-01',
            'end' => '2026-11-02',
        ]));

        $excel->assertOk();
        $this->assertStringContainsString(
            'absensi-semua-pegawai-2026-11-01_2026-11-02.xlsx',
            (string) $excel->headers->get('content-disposition')
        );
    }

    public function test_date_range_longer_than_62_days_is_rejected(): void
    {
        $viewer = $this->userWithPermissions(['view-presensi-all']);

        $this->actingAs($viewer)
            ->get(route('absensi.today.pdf', [
                'start' => '2026-01-01',
                'end' => '2026-03-15',
            ]))
            ->assertSessionHasErrors('end');
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
            'nik' => 'NIK-'.uniqid(),
            'full_name' => $name,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);
    }
}
