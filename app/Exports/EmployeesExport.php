<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  Collection<int, Employee>  $employees
     */
    public function __construct(
        private Collection $employees,
    ) {}

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Lengkap',
            'Jabatan',
            'Divisi',
            'Tipe',
            'Status',
            'Dibuat',
        ];
    }

    /**
     * @param  Employee  $employee
     * @return list<string>
     */
    public function map($employee): array
    {
        return [
            (string) $employee->nik,
            (string) $employee->full_name,
            $employee->position?->nama ?? '-',
            $employee->division?->nama ?? '-',
            $employee->employeeType?->nama ?? '-',
            $employee->status ? ucfirst((string) $employee->status) : '-',
            $employee->created_at?->format('d M Y') ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Karyawan';
    }
}
