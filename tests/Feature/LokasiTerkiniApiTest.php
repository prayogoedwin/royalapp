<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\LokasiTerkini;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LokasiTerkiniApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_send_location(): void
    {
        $this->postJson('/api/lokasi-terkini', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ])->assertUnauthorized();
    }

    public function test_user_without_employee_cannot_send_location(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/lokasi-terkini', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ])->assertNotFound()
            ->assertJsonPath('status', false);
    }

    public function test_employee_can_store_current_location(): void
    {
        [$user, $employee] = $this->makeEmployeeUser('Driver Lokasi');
        Sanctum::actingAs($user);

        $this->postJson('/api/lokasi-terkini', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'waktu_hp' => '2026-10-28 15:20:00',
            'user_id' => 999,
            'employee_id' => 999,
        ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.interval_menit', 10)
            ->assertJsonPath('data.waktu_hp', '2026-10-28 15:20:00');

        $lokasi = LokasiTerkini::query()->first();
        $this->assertNotNull($lokasi);
        $this->assertSame($user->id, $lokasi->user_id);
        $this->assertSame($employee->id, $lokasi->employee_id);
        $this->assertEqualsWithDelta(-6.2088, (float) $lokasi->latitude, 0.0001);
        $this->assertEqualsWithDelta(106.8456, (float) $lokasi->longitude, 0.0001);
        $this->assertSame('2026-10-28 15:20:00', $lokasi->waktu_hp->format('Y-m-d H:i:s'));
        $this->assertNotNull($lokasi->created_at);
    }

    public function test_accepts_lat_lng_aliases(): void
    {
        [$user, $employee] = $this->makeEmployeeUser('Driver Alias');
        Sanctum::actingAs($user);

        $this->postJson('/api/lokasi-terkini', [
            'lat' => -7.2575,
            'lng' => 112.7521,
        ])
            ->assertOk()
            ->assertJsonPath('data.employee_id', $employee->id);

        $lokasi = LokasiTerkini::query()->first();
        $this->assertNotNull($lokasi);
        $this->assertEqualsWithDelta(-7.2575, (float) $lokasi->latitude, 0.0001);
        $this->assertEqualsWithDelta(112.7521, (float) $lokasi->longitude, 0.0001);
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

        return [$user, $employee];
    }
}
