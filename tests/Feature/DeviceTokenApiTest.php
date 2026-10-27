<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_without_fcm_token_still_works(): void
    {
        $user = User::factory()->create([
            'email' => 'driver@royalapp.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'driver@royalapp.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseCount('user_devices', 0);
    }

    public function test_login_with_optional_fcm_token_saves_device(): void
    {
        $user = User::factory()->create([
            'email' => 'driver@royalapp.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'driver@royalapp.com',
            'password' => 'password',
            'fcm_token' => 'token-abc',
            'platform' => 'android',
        ])->assertOk();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'token-abc',
            'platform' => 'android',
        ]);
    }

    public function test_login_moves_existing_token_to_current_user(): void
    {
        $old = User::factory()->create();
        $current = User::factory()->create([
            'email' => 'driver@royalapp.com',
            'password' => 'password',
        ]);

        UserDevice::create([
            'user_id' => $old->id,
            'fcm_token' => 'shared-token',
            'platform' => 'android',
        ]);

        $this->postJson('/api/login', [
            'email' => 'driver@royalapp.com',
            'password' => 'password',
            'fcm_token' => 'shared-token',
        ])->assertOk();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $current->id,
            'fcm_token' => 'shared-token',
        ]);
        $this->assertDatabaseMissing('user_devices', [
            'user_id' => $old->id,
            'fcm_token' => 'shared-token',
        ]);
    }

    public function test_authenticated_user_can_register_and_remove_device_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/device-token', [
            'fcm_token' => 'token-xyz',
            'platform' => 'android',
        ])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'token-xyz',
        ]);

        $this->deleteJson('/api/device-token', [
            'fcm_token' => 'token-xyz',
        ])->assertOk();

        $this->assertDatabaseMissing('user_devices', [
            'fcm_token' => 'token-xyz',
        ]);
    }

    public function test_logout_with_fcm_token_removes_device(): void
    {
        $user = User::factory()->create();
        UserDevice::create([
            'user_id' => $user->id,
            'fcm_token' => 'token-logout',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/logout', [
            'fcm_token' => 'token-logout',
        ])->assertOk();

        $this->assertDatabaseMissing('user_devices', [
            'fcm_token' => 'token-logout',
        ]);
    }

    public function test_refresh_replaces_old_fcm_token(): void
    {
        $user = User::factory()->create();
        UserDevice::create([
            'user_id' => $user->id,
            'fcm_token' => 'token-lama',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/device-token/refresh', [
            'fcm_token' => 'token-baru',
            'old_fcm_token' => 'token-lama',
            'platform' => 'android',
        ])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'token-baru',
        ]);
        $this->assertDatabaseMissing('user_devices', [
            'fcm_token' => 'token-lama',
        ]);
        $this->assertDatabaseCount('user_devices', 1);
    }

    public function test_refresh_without_old_token_just_saves_new_one(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/device-token/refresh', [
            'fcm_token' => 'token-baru',
        ])->assertOk();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'token-baru',
        ]);
    }
}
