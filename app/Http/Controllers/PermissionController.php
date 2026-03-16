<?php

namespace App\Http\Controllers;

use App\Exports\PermissionsExport;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::withCount('roles')->select('permissions.*');
            
            return DataTables::of($permissions)
                ->addColumn('roles_count', function ($permission) {
                    return $permission->roles_count ?? 0;
                })
                ->addColumn('actions', function ($permission) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-permissions')) {
                        $actions .= '<a href="' . route('permissions.show', $permission) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-permissions')) {
                        $actions .= '<a href="' . route('permissions.edit', $permission) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-permissions')) {
                        $actions .= '<form action="' . route('permissions.destroy', $permission) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('created_at', function ($permission) {
                    return $permission->created_at->format('M d, Y');
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('permissions.index');
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
