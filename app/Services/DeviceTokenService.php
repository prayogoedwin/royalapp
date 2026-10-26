<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;

class DeviceTokenService
{
    public function save(User $user, string $token, ?string $platform = null): UserDevice
    {
        $token = trim($token);

        UserDevice::query()
            ->where('fcm_token', $token)
            ->where('user_id', '!=', $user->id)
            ->delete();

        return UserDevice::query()->updateOrCreate(
            ['fcm_token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $platform,
                'last_used_at' => now(),
            ]
        );
    }

    public function forget(?User $user, string $token): void
    {
        $query = UserDevice::query()->where('fcm_token', trim($token));

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $query->delete();
    }
}
