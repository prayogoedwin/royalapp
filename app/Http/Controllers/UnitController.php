<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $units = Unit::with('division')->select('units.*');
            
            return DataTables::of($units)
                ->addColumn('division_name', function ($unit) {
                    return $unit->division->nama;
                })
                ->addColumn('actions', function ($unit) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-units')) {
                        $actions .= '<a href="' . route('units.show', $unit) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-units')) {
                        $actions .= '<a href="' . route('units.edit', $unit) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-units')) {
                        $actions .= '<form action="' . route('units.destroy', $unit) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('created_at', function ($unit) {
                    return $unit->created_at->format('M d, Y');
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('units.index');
    }

    public function create(): View
    {
        $divisions = Division::orderBy('nama')->get();
        return view('units.create', compact('divisions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'code' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'nopol' => ['nullable', 'string', 'max:255'],
            'tahun_pembelian' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'tgl_perpanjangan_pajak' => ['nullable', 'date'],
            'tgl_perpanjangan_pajak_berikutnya' => ['nullable', 'date'],
            'tgl_ganti_plat' => ['nullable', 'date'],
            'tgl_ganti_plat_berikutnya' => ['nullable', 'date'],
            'tgl_kir_terakhir' => ['nullable', 'date'],
            'tgl_kir_berikutnya' => ['nullable', 'date'],
        ]);

        Unit::create($validated);

        return to_route('units.index')->with('status', 'Unit created successfully.');
    }

    public function show(Unit $unit): View
    {
        $unit->load('division');
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit): View
    {
        $divisions = Division::orderBy('nama')->get();
        return view('units.edit', compact('unit', 'divisions'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'code' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'nopol' => ['nullable', 'string', 'max:255'],
            'tahun_pembelian' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'tgl_perpanjangan_pajak' => ['nullable', 'date'],
            'tgl_perpanjangan_pajak_berikutnya' => ['nullable', 'date'],
            'tgl_ganti_plat' => ['nullable', 'date'],
            'tgl_ganti_plat_berikutnya' => ['nullable', 'date'],
            'tgl_kir_terakhir' => ['nullable', 'date'],
            'tgl_kir_berikutnya' => ['nullable', 'date'],
        ]);

        $unit->update($validated);

        return to_route('units.index')->with('status', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return to_route('units.index')->with('status', 'Unit deleted successfully.');
    }
}
