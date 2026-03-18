<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderVehicleIssue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'unit_code',
        'issue_category',
        'description',
        'priority',
        'issue_photo',
        'repair_photo',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'created_by',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getCategoryLabel($category): string
    {
        return match($category) {
            'mechanical' => 'Mechanical',
            'body' => 'Body/Exterior',
            'interior' => 'Interior',
            'safety' => 'Safety Equipment',
            'medical_equipment' => 'Medical Equipment',
            'other' => 'Other',
            default => $category,
        };
    }

    public static function getPriorityLabel($priority): string
    {
        return match($priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => $priority,
        };
    }

    public static function getPriorityColor($priority): string
    {
        return match($priority) {
            'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
