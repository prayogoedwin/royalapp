<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Position;
use App\Models\Pool;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employees = Employee::with(['user', 'position', 'division', 'employeeType'])
                ->select('employees.*');
            
            return DataTables::of($employees)
                ->addColumn('user_name', function ($employee) {
                    return $employee->user ? $employee->user->name : '-';
                })
                ->addColumn('position_name', function ($employee) {
                    return $employee->position->nama;
                })
                ->addColumn('division_name', function ($employee) {
                    return $employee->division->nama;
                })
                ->addColumn('employee_type_name', function ($employee) {
                    return $employee->employeeType->nama;
                })
                ->addColumn('status_badge', function ($employee) {
                    $colors = [
                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'inactive' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'resigned' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                    ];
                    $color = $colors[$employee->status] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 rounded-full text-xs font-medium ' . $color . '">' . ucfirst($employee->status) . '</span>';
                })
                ->addColumn('actions', function ($employee) {
                    $actions = '';

                    $myEmployeeId = auth()->user()?->employee?->id;
                    $canViewPresensi = auth()->user()->hasPermission('view-presensi');
                    $canViewPresensiAll = auth()->user()->hasPermission('view-presensi-all');
                    
                    if (auth()->user()->hasPermission('show-employees')) {
                        $actions .= '<a href="' . route('employees.show', $employee) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-employees')) {
                        $actions .= '<a href="' . route('employees.edit', $employee) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }

                    if (auth()->user()->hasPermission('edit-employees') && ! $employee->user) {
                        $actions .= '<form action="' . route('employees.create-account', $employee) . '" method="POST" class="inline" onsubmit="return confirm(\'Buat akun login untuk employee ini? Password default sama dengan NIK.\')">
                            ' . csrf_field() . '
                            <button type="submit" class="text-amber-600 dark:text-amber-400 hover:underline mr-3">Buat Akun</button>
                        </form>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-employees') && (int) $employee->user_id !== (int) auth()->id()) {
                        $actions .= '<form action="' . route('employees.destroy', $employee) . '" method="POST" class="inline" onsubmit="return confirm(\'Hapus employee ini? Akun user terkait juga akan dihapus.\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    // Presensi button: allow self, or admin with edit-absensi-status permission
                    if ($myEmployeeId && (int) $employee->id === (int) $myEmployeeId) {
                        if ($canViewPresensi) {
                            $actions .= '<a href="' . route('presensi.my') . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Presensi</a>';
                        }
                    } elseif ($canViewPresensiAll) {
                        $actions .= '<a href="' . route('employees.presensi', $employee) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Presensi</a>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('created_at', function ($employee) {
                    return $employee->created_at->format('M d, Y');
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('employees.index');
    }

    public function create(): View
    {
        $positions = Position::orderBy('nama')->get();
        $divisions = Division::orderBy('nama')->get();
        $employeeTypes = EmployeeType::orderBy('nama')->get();
        $roles = Role::orderBy('name')->get();
        $pools = Pool::orderBy('pool_name')->get();

        return view('employees.create', compact('positions', 'divisions', 'employeeTypes', 'roles', 'pools'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // User data
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            
            // Employee data
            'position_id' => ['required', 'exists:positions,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'employee_type_id' => ['required', 'exists:employee_types,id'],
            'nik' => ['required', 'string', 'unique:employees,nik', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'pool_id' => ['nullable', 'exists:pools,id'],
            'status' => ['required', 'in:active,inactive,resigned'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date', 'after:join_date'],
        ]);

        DB::beginTransaction();
        try {
            // Create user first
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign roles if provided
            if (!empty($validated['roles'])) {
                $user->roles()->sync($validated['roles']);
            }

            // Create employee with user_id
            $employee = Employee::create([
                'user_id' => $user->id,
                'position_id' => $validated['position_id'],
                'division_id' => $validated['division_id'],
                'pool_id' => $validated['pool_id'] ?? null,
                'employee_type_id' => $validated['employee_type_id'],
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'birth_date' => $validated['birth_date'],
                'status' => $validated['status'],
                'join_date' => $validated['join_date'],
                'resign_date' => $validated['resign_date'],
            ]);

            DB::commit();

            return to_route('employees.index')->with('status', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    public function show(Employee $employee): View
    {
        $employee->load(['user.roles', 'position', 'division', 'employeeType']);

        return view('employees.show', compact('employee'));
    }

    public function createAccount(Employee $employee): RedirectResponse
    {
        if ($employee->user) {
            return back()->with('status', 'Employee ini sudah memiliki akun.');
        }

        if (blank($employee->nik)) {
            return back()->withErrors(['error' => 'NIK kosong, akun tidak bisa dibuat.']);
        }

        $employee->loadMissing('position');

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $this->accountEmailFor($employee),
                'password' => $employee->nik,
            ]);

            $roleName = strcasecmp((string) $employee->position?->nama, 'driver') === 0
                ? 'Operational'
                : 'User';
            $role = Role::query()->where('name', $roleName)->first()
                ?? Role::query()->where('name', 'User')->first()
                ?? Role::query()->where('name', 'Operational')->first();

            if ($role) {
                $user->assignRole($role);
            }

            $employee->update(['user_id' => $user->id]);

            DB::commit();

            return back()->with('status', 'Akun berhasil dibuat. Email: '.$user->email.'. Password sama dengan NIK.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal membuat akun: '.$e->getMessage()]);
        }
    }

    private function accountEmailFor(Employee $employee): string
    {
        $local = Str::slug($employee->nik, '.') ?: 'emp'.$employee->id;
        $email = strtolower($local).'@royalapp.com';

        if (! User::query()->where('email', $email)->exists()) {
            return $email;
        }

        return strtolower($local).'.'.$employee->id.'@royalapp.com';
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['user.roles']);
        $positions = Position::orderBy('nama')->get();
        $divisions = Division::orderBy('nama')->get();
        $employeeTypes = EmployeeType::orderBy('nama')->get();
        $roles = Role::orderBy('name')->get();
        $pools = Pool::orderBy('pool_name')->get();

        return view('employees.edit', compact('employee', 'positions', 'divisions', 'employeeTypes', 'roles', 'pools'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            // User data
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($employee->user_id ?? 'NULL')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            
            // Employee data
            'position_id' => ['required', 'exists:positions,id'],
            'division_id' => ['required', 'exists:divisions,id'],
            'employee_type_id' => ['required', 'exists:employee_types,id'],
            'nik' => ['required', 'string', 'unique:employees,nik,' . $employee->id, 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'pool_id' => ['nullable', 'exists:pools,id'],
            'status' => ['required', 'in:active,inactive,resigned'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date', 'after:join_date'],
        ]);

        DB::beginTransaction();
        try {
            // Update or create user
            if ($employee->user) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ];
                
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                
                $employee->user->update($userData);
                
                // Sync roles
                $employee->user->roles()->sync($validated['roles'] ?? []);
            } else {
                // Create user if not exists
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password'] ?? 'password'),
                ]);
                
                if (!empty($validated['roles'])) {
                    $user->roles()->sync($validated['roles']);
                }
                
                $employee->user_id = $user->id;
            }

            // Update employee
            $employee->update([
                'position_id' => $validated['position_id'],
                'division_id' => $validated['division_id'],
                'pool_id' => $validated['pool_id'] ?? null,
                'employee_type_id' => $validated['employee_type_id'],
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'birth_date' => $validated['birth_date'],
                'status' => $validated['status'],
                'join_date' => $validated['join_date'],
                'resign_date' => $validated['resign_date'],
            ]);

            DB::commit();

            return to_route('employees.index')->with('status', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->user_id && (int) $employee->user_id === (int) auth()->id()) {
            return back()->with('status', 'Tidak dapat menghapus employee dari akun yang sedang digunakan.');
        }

        DB::beginTransaction();
        try {
            $user = $employee->user;

            $employee->delete();

            if ($user) {
                $user->delete();
            }

            DB::commit();

            return to_route('employees.index')->with('status', 'Employee dan user terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }
}
