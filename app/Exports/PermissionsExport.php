<?php

namespace App\Exports;

use App\Models\Permission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PermissionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Permission::withCount('roles')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Total Roles',
            'Created At',
        ];
    }

    public function map($permission): array
    {
        return [
            $permission->id,
            $permission->name,
            $permission->roles_count,
            $permission->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
