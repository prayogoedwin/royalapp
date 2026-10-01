<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Position extends Model
{
    protected $fillable = [
        'nama',
        'key',
    ];

    protected static function booted(): void
    {
        static::creating(function (Position $position): void {
            if (filled($position->key)) {
                return;
            }

            $position->key = static::uniqueKeyFromNama((string) $position->nama);
        });
    }

    public static function uniqueKeyFromNama(string $nama): string
    {
        $base = Str::slug($nama, '_') ?: 'position';
        $key = $base;
        $suffix = 2;

        while (static::query()->where('key', $key)->exists()) {
            $key = $base.'_'.$suffix;
            $suffix++;
        }

        return $key;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (! array_key_exists('nama', $array) || ! array_key_exists('key', $array)) {
            return $array;
        }

        $result = [];
        foreach ($array as $attribute => $value) {
            if ($attribute === 'key') {
                continue;
            }

            $result[$attribute] = $value;

            if ($attribute === 'nama') {
                $result['key'] = $array['key'];
            }
        }

        return $result;
    }
}
