<?php

namespace App\Http\Controllers;

use App\Models\Pool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PoolController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pools = Pool::query()->select('pools.*');

            return DataTables::of($pools)
                ->addColumn('actions', function (Pool $pool) {
                    $actions = '';

                    if (auth()->user()->hasPermission('show-pools')) {
                        $actions .= '<a href="' . route('pools.show', $pool) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }

                    if (auth()->user()->hasPermission('edit-pools')) {
                        $actions .= '<a href="' . route('pools.edit', $pool) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }

                    if (auth()->user()->hasPermission('delete-pools')) {
                        $actions .= '<form action="' . route('pools.destroy', $pool) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    return $actions ?: '-';
                })
                ->editColumn('created_at', function (Pool $pool) {
                    return $pool->created_at?->format('M d, Y') ?? '-';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('pools.index');
    }

    public function create(): View
    {
        return view('pools.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pool_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'lng' => ['nullable', 'numeric', 'min:-180', 'max:180'],
        ]);

        Pool::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return to_route('pools.index')->with('status', 'Pool created successfully.');
    }

    public function show(Pool $pool): View
    {
        $pool->load(['createdBy', 'updatedBy', 'deletedBy']);
        return view('pools.show', compact('pool'));
    }

    public function edit(Pool $pool): View
    {
        $pool->load(['createdBy', 'updatedBy', 'deletedBy']);
        return view('pools.edit', compact('pool'));
    }

    public function update(Request $request, Pool $pool): RedirectResponse
    {
        $validated = $request->validate([
            'pool_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'lng' => ['nullable', 'numeric', 'min:-180', 'max:180'],
        ]);

        $pool->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return to_route('pools.index')->with('status', 'Pool updated successfully.');
    }

    public function destroy(Request $request, Pool $pool): RedirectResponse
    {
        $pool->update(['deleted_by' => auth()->id()]);
        $pool->delete();

        return to_route('pools.index')->with('status', 'Pool deleted successfully.');
    }
}

