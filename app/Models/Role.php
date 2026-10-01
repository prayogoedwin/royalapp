<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = [
        'name',
        'key',
    ];

    protected static function booted(): void
    {
        static::creating(function (Role $role): void {
            if (filled($role->key)) {
                return;
            }

            $role->key = static::uniqueKeyFromName((string) $role->name);
        });
    }

    public static function uniqueKeyFromName(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'role';
        $key = $base;
        $suffix = 2;

        while (static::query()->where('key', $key)->exists()) {
            $key = $base.'_'.$suffix;
            $suffix++;
        }

        return $key;
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (! array_key_exists('name', $array) || ! array_key_exists('key', $array)) {
            return $array;
        }

        $result = [];
        foreach ($array as $attribute => $value) {
            if ($attribute === 'key') {
                continue;
            }

            $result[$attribute] = $value;

            if ($attribute === 'name') {
                $result['key'] = $array['key'];
            }
        }

        return $result;
    }
}
