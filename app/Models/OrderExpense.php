<?php

namespace App\Models;

use App\Support\OrderCategoryOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'expense_category',
        'description',
        'amount',
        'receipt_photo',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getCategoryLabel($category): string
    {
        return OrderCategoryOptions::expenseCategoryLabel($category);
    }
}
