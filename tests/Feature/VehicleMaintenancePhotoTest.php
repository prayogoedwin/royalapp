<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\VehicleMaintenance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleMaintenancePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_photos_can_be_zoomed_on_detail(): void
    {
        $user = $this->userWithPermissions(['show-vehicle-maintenances']);
        $maintenance = $this->makeMaintenance([
            'identification_photo' => 'uploads/2026/10/vehicle-maintenances/damage.jpg',
            'repair_photo' => 'uploads/2026/10/vehicle-maintenances/repair.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('vehicle-maintenances.show', $maintenance))
            ->assertOk()
            ->assertSee('cursor-zoom-in', false)
            ->assertSee('Perkecil', false)
            ->assertSee('Perbesar', false)
            ->assertSee('Foto Kerusakan', false)
            ->assertSee('Foto Perbaikan', false);
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
     * @param  array<string, mixed>  $overrides
     */
    private function makeMaintenance(array $overrides = []): VehicleMaintenance
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $status = OrderStatus::query()->firstOrCreate(['name' => 'Open'], ['color' => '#2563eb']);
        $unit = Unit::query()->firstOrCreate(
            ['code' => 'RT02'],
            [
                'division_id' => $division->id,
                'description' => 'Unit Towing',
                'nopol' => 'B 2 RT',
            ]
        );

        return VehicleMaintenance::create(array_merge([
            'maintenance_type' => 'PERBAIKAN',
            'damage_description' => 'Jack stean hidrolik lemah',
            'unit_id' => $unit->id,
            'order_status_id' => $status->id,
            'total_cost' => 0,
        ], $overrides));
    }
}
