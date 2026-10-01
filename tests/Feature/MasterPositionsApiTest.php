<?php

namespace Tests\Feature;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MasterPositionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_positions_lists_id_nama_and_key(): void
    {
        Position::query()->create(['nama' => 'Driver', 'key' => 'driver']);
        Position::query()->create(['nama' => 'Nurse', 'key' => 'nurse']);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/master/positions')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.nama', 'Driver')
            ->assertJsonPath('data.0.key', 'driver')
            ->assertJsonPath('data.1.nama', 'Nurse')
            ->assertJsonPath('data.1.key', 'nurse')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    ['id', 'nama', 'key'],
                ],
            ]);
    }

    public function test_master_positions_requires_authentication(): void
    {
        $this->getJson('/api/master/positions')->assertUnauthorized();
    }
}
