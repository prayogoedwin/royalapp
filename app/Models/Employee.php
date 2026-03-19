<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'position_id',
        'division_id',
        'pool_id',
        'employee_type_id',
        'nik',
        'full_name',
        'phone',
        'address',
        'birth_date',
        'status',
        'join_date',
        'resign_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
        'resign_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }
}
