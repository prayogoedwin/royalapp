<?php

namespace App\Http\Controllers;

use App\Exports\PermissionsExport;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::withCount('roles')->latest()->paginate(10);

        return view('permissions.index', compact('permissions'));
    }

    public function export()
    {
        return Excel::download(new PermissionsExport, 'permissions-' . date('Y-m-d') . '.xlsx');
    }

    public function create(): View
    {
        return view('permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create($validated);

        return to_route('permissions.index')->with('status', 'Permission created successfully.');
    }

    public function show(Permission $permission): View
    {
        $permission->load('roles');
        
        return view('permissions.show', compact('permission'));
    }

    public function edit(Permission $permission): View
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,'.$permission->id],
        ]);

        $permission->update($validated);

        return to_route('permissions.index')->with('status', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return to_route('permissions.index')->with('status', 'Permission deleted successfully.');
    }
}
