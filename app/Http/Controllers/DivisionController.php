<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $divisions = Division::select('divisions.*');
            
            return DataTables::of($divisions)
                ->addColumn('logo_display', function ($division) {
                    if ($division->logo) {
                        return '<img src="' . asset('storage/' . $division->logo) . '" class="h-10 w-10 object-contain" alt="' . $division->nama . '">';
                    }
                    return '<span class="text-gray-400 dark:text-gray-500">No logo</span>';
                })
                ->addColumn('actions', function ($division) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-divisions')) {
                        $actions .= '<a href="' . route('divisions.show', $division) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-divisions')) {
                        $actions .= '<a href="' . route('divisions.edit', $division) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-divisions')) {
                        $actions .= '<form action="' . route('divisions.destroy', $division) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('created_at', function ($division) {
                    return $division->created_at->format('M d, Y');
                })
                ->rawColumns(['logo_display', 'actions'])
                ->make(true);
        }

        return view('divisions.index');
    }

    public function create(): View
    {
        return view('divisions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('divisions', 'public');
        }

        Division::create($validated);

        return to_route('divisions.index')->with('status', 'Division created successfully.');
    }

    public function show(Division $division): View
    {
        return view('divisions.show', compact('division'));
    }

    public function edit(Division $division): View
    {
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($division->logo) {
                \Storage::disk('public')->delete($division->logo);
            }
            $validated['logo'] = $request->file('logo')->store('divisions', 'public');
        }

        $division->update($validated);

        return to_route('divisions.index')->with('status', 'Division updated successfully.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        if ($division->logo) {
            \Storage::disk('public')->delete($division->logo);
        }
        
        $division->delete();

        return to_route('divisions.index')->with('status', 'Division deleted successfully.');
    }
}
