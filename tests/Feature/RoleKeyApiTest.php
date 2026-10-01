<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleKeyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_api_includes_immutable_role_key_after_name(): void
    {
        $role = Role::query()->create([
            'name' => 'Coordinator',
            'key' => 'coordinator',
        ]);

        $user = User::factory()->create([
            'name' => 'Ridho Driver [Test]',
            'email' => 'ridhodriver@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonPath('data.roles.0.name', 'Coordinator')
            ->assertJsonPath('data.roles.0.key', 'coordinator');

        $rolePayload = $response->json('data.roles.0');
        $keys = array_keys($rolePayload);

        $this->assertContains('name', $keys);
        $this->assertContains('key', $keys);
        $this->assertSame(array_search('name', $keys, true) + 1, array_search('key', $keys, true));
    }

    public function test_role_key_stays_the_same_when_name_changes(): void
    {
        $role = Role::query()->create([
            'name' => 'Coordinator',
            'key' => 'coordinator',
        ]);

        $role->update(['name' => 'Koordinator']);

        $this->assertSame('coordinator', $role->fresh()->key);
    }
}
