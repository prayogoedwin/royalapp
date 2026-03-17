<?php

namespace App\Http\Controllers;

use App\Models\EmployeeType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employeeTypes = EmployeeType::select('employee_types.*');
            
            return DataTables::of($employeeTypes)
                ->addColumn('actions', function ($employeeType) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-employee-types')) {
                        $actions .= '<a href="' . route('employee-types.show', $employeeType) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-employee-types')) {
                        $actions .= '<a href="' . route('employee-types.edit', $employeeType) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-employee-types')) {
                        $actions .= '<form action="' . route('employee-types.destroy', $employeeType) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('created_at', function ($employeeType) {
                    return $employeeType->created_at->format('M d, Y');
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('employee-types.index');
    }

    public function create(): View
    {
        return view('employee-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        EmployeeType::create($validated);

        return to_route('employee-types.index')->with('status', 'Employee Type created successfully.');
    }

    public function show(EmployeeType $employeeType): View
    {
        return view('employee-types.show', compact('employeeType'));
    }

    public function edit(EmployeeType $employeeType): View
    {
        return view('employee-types.edit', compact('employeeType'));
    }

    public function update(Request $request, EmployeeType $employeeType): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $employeeType->update($validated);

        return to_route('employee-types.index')->with('status', 'Employee Type updated successfully.');
    }

    public function destroy(EmployeeType $employeeType): RedirectResponse
    {
        $employeeType->delete();

        return to_route('employee-types.index')->with('status', 'Employee Type deleted successfully.');
    }
}
