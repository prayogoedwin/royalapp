<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MasterRolesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_roles_lists_id_name_and_key(): void
    {
        Role::query()->create(['name' => 'Coordinator', 'key' => 'coordinator']);
        Role::query()->create(['name' => 'Operational', 'key' => 'operational']);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/master/roles')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Coordinator')
            ->assertJsonPath('data.0.key', 'coordinator')
            ->assertJsonPath('data.1.name', 'Operational')
            ->assertJsonPath('data.1.key', 'operational')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    ['id', 'name', 'key'],
                ],
            ]);
    }

    public function test_master_roles_requires_authentication(): void
    {
        $this->getJson('/api/master/roles')->assertUnauthorized();
    }
}
