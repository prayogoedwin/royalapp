<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles')->select('users.*');
            
            return DataTables::of($users)
                ->addColumn('roles', function ($user) {
                    $badges = '';
                    foreach ($user->roles as $role) {
                        $badges .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1">' . $role->name . '</span>';
                    }
                    return $badges ?: '<span class="text-sm text-gray-500 dark:text-gray-400">No roles</span>';
                })
                ->addColumn('actions', function ($user) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-users')) {
                        $actions .= '<a href="' . route('users.show', $user) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-users')) {
                        $actions .= '<a href="' . route('users.edit', $user) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-users')) {
                        $actions .= '<form action="' . route('users.destroy', $user) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions;
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('M d, Y');
                })
                ->rawColumns(['roles', 'actions'])
                ->make(true);
        }

        return view('users.index');
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users-' . date('Y-m-d') . '.xlsx');
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return to_route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load('roles.permissions');
        
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $user->roles()->sync($validated['roles'] ?? []);

        return to_route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('users.index')->with('status', 'User deleted successfully.');
    }
}
