<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PositionKeyProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_includes_position_from_employee(): void
    {
        $user = User::factory()->create([
            'name' => 'Ridho Driver',
            'email' => 'ridhodriver@gmail.com',
        ]);

        $position = Position::query()->create([
            'nama' => 'Driver',
            'key' => 'driver',
        ]);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        Employee::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-PROFILE-1',
            'full_name' => 'Ridho Driver',
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.position.id', $position->id)
            ->assertJsonPath('data.position.nama', 'Driver')
            ->assertJsonPath('data.position.key', 'driver');
    }

    public function test_profile_position_is_empty_array_when_user_has_no_employee(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonPath('status', true);

        $this->assertSame([], $response->json('data.position'));
    }

    public function test_position_key_stays_the_same_when_nama_changes(): void
    {
        $position = Position::query()->create([
            'nama' => 'Driver',
            'key' => 'driver',
        ]);

        $position->update(['nama' => 'Sopir']);

        $this->assertSame('driver', $position->fresh()->key);
    }
}
