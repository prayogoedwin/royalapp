<?php

namespace App\Models;

use Database\Factories\LokasiTerkiniFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LokasiTerkini extends Model
{
    /** @use HasFactory<LokasiTerkiniFactory> */
    use HasFactory;

    protected $table = 'lokasi_terkini';

    protected $fillable = [
        'user_id',
        'employee_id',
        'latitude',
        'longitude',
        'waktu_hp',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'waktu_hp' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
