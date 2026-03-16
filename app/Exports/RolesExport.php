<?php

namespace App\Exports;

use App\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RolesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Role::withCount('users', 'permissions')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Total Users',
            'Total Permissions',
            'Created At',
        ];
    }

    public function map($role): array
    {
        return [
            $role->id,
            $role->name,
            $role->users_count,
            $role->permissions_count,
            $role->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
