<?php

namespace App\Support;

class OrderCategoryOptions
{
    public static function expenseCategories(): array
    {
        return [
            'solar' => 'Solar/BBM',
            'e-toll' => 'E-Toll',
            'parkir' => 'Parkir',
            'tol_manual' => 'Tol Manual',
            'makan' => 'Makan',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function issueCategories(): array
    {
        return [
            'mechanical' => 'Mechanical',
            'body' => 'Body/Exterior',
            'interior' => 'Interior',
            'safety' => 'Safety Equipment',
            'medical_equipment' => 'Medical Equipment',
            'other' => 'Other',
        ];
    }

    public static function expenseCategoryLabel(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return self::expenseCategories()[$value] ?? $value;
    }

    public static function issueCategoryLabel(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return self::issueCategories()[$value] ?? $value;
    }
}
