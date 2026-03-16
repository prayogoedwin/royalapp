<?php

namespace App\Http\Controllers;

use App\Exports\RolesExport;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users', 'permissions')->latest()->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function export()
    {
        return Excel::download(new RolesExport, 'roles-' . date('Y-m-d') . '.xlsx');
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);

        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return to_route('roles.index')->with('status', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $role->load('permissions', 'users');
        
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $role->load('permissions');

        return view('roles.edit', compact('role', 'groupedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        // Sync permissions - jika tidak ada permissions yang dicentang, akan remove semua
        $permissionsToSync = $validated['permissions'] ?? [];
        $role->permissions()->sync($permissionsToSync);
        
        // Log untuk debugging
        \Log::info('Role updated', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions_synced' => $permissionsToSync,
            'permissions_count' => count($permissionsToSync)
        ]);

        return to_route('roles.index')->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return to_route('roles.index')->with('status', 'Role deleted successfully.');
    }

    private function groupPermissions($permissions): array
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            
            if (count($parts) >= 2) {
                $action = $parts[0];
                $resource = implode('-', array_slice($parts, 1));
            } else {
                $action = 'other';
                $resource = $permission->name;
            }
            
            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [
                    'name' => ucfirst(str_replace('-', ' ', $resource)),
                    'permissions' => []
                ];
            }
            
            $grouped[$resource]['permissions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action,
                'label' => ucfirst($action)
            ];
        }
        
        ksort($grouped);
        
        foreach ($grouped as &$group) {
            usort($group['permissions'], function($a, $b) {
                $order = ['view' => 1, 'show' => 2, 'create' => 3, 'edit' => 4, 'download' => 5, 'delete' => 6];
                $orderA = $order[$a['action']] ?? 99;
                $orderB = $order[$b['action']] ?? 99;
                return $orderA <=> $orderB;
            });
        }
        
        return $grouped;
    }
}
