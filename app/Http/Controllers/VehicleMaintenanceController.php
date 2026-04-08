<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Unit;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceCostDetail;
use App\Models\VehicleMaintenancePic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VehicleMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rows = VehicleMaintenance::with(['unit', 'order', 'orderStatus'])->select('vehicle_maintenances.*');

            return DataTables::of($rows)
                ->addColumn('unit_code', fn (VehicleMaintenance $m) => e($m->unit?->code ?? '-'))
                ->addColumn('order_ref', fn (VehicleMaintenance $m) => e($m->order?->order_number ?? '-'))
                ->addColumn('status_name', fn (VehicleMaintenance $m) => e($m->orderStatus?->name ?? '-'))
                ->editColumn('total_cost', fn (VehicleMaintenance $m) => 'Rp ' . number_format((float) $m->total_cost, 0, ',', '.'))
                ->addColumn('actions', function (VehicleMaintenance $m) {
                    $actions = '';
                    if (auth()->user()->hasPermission('show-vehicle-maintenances')) {
                        $actions .= '<a href="' . route('vehicle-maintenances.show', $m) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    if (auth()->user()->hasPermission('edit-vehicle-maintenances')) {
                        $actions .= '<a href="' . route('vehicle-maintenances.edit', $m) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    if (auth()->user()->hasPermission('delete-vehicle-maintenances')) {
                        $actions .= '<form action="' . route('vehicle-maintenances.destroy', $m) . '" method="POST" class="inline" onsubmit="return confirm(\'Delete vehicle maintenance?\')">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button></form>';
                    }
                    return $actions ?: '-';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('vehicle-maintenances.index');
    }

    public function create(): View
    {
        $units = Unit::with('division')->orderBy('code')->get();
        $orders = Order::with('orderStatus')->orderByDesc('id')->limit(300)->get();
        $statuses = OrderStatus::orderBy('name')->get();

        return view('vehicle-maintenances.create', compact('units', 'orders', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_type' => ['required', 'in:PERBAIKAN,PERAWATAN,ADMINISTRASI,LAINNYA'],
            'damage_description' => ['nullable', 'string'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'odo_identification' => ['nullable', 'numeric', 'min:0'],
            'identified_at' => ['nullable', 'date'],
            'odo_repair' => ['nullable', 'numeric', 'min:0'],
            'repaired_at' => ['nullable', 'date'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'identification_pic_ids' => ['nullable', 'array'],
            'identification_pic_ids.*' => ['exists:employees,id'],
            'repair_pic_ids' => ['nullable', 'array'],
            'repair_pic_ids.*' => ['exists:employees,id'],
            'cost_details' => ['nullable', 'array'],
            'cost_details.*.type' => ['required_with:cost_details.*.amount', 'in:JASA,BARANG,LAINNYA'],
            'cost_details.*.description' => ['nullable', 'string'],
            'cost_details.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $maintenance = VehicleMaintenance::create([
                'maintenance_type' => $validated['maintenance_type'],
                'damage_description' => $validated['damage_description'] ?? null,
                'order_id' => $validated['order_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'odo_identification' => $validated['odo_identification'] ?? null,
                'identified_at' => $validated['identified_at'] ?? null,
                'odo_repair' => $validated['odo_repair'] ?? null,
                'repaired_at' => $validated['repaired_at'] ?? null,
                'order_status_id' => $validated['order_status_id'],
                'total_cost' => 0,
                'created_by' => auth()->id(),
            ]);

            foreach (($validated['identification_pic_ids'] ?? []) as $employeeId) {
                VehicleMaintenancePic::create([
                    'vehicle_maintenance_id' => $maintenance->id,
                    'employee_id' => $employeeId,
                    'type' => 'identification',
                ]);
            }

            foreach (($validated['repair_pic_ids'] ?? []) as $employeeId) {
                VehicleMaintenancePic::create([
                    'vehicle_maintenance_id' => $maintenance->id,
                    'employee_id' => $employeeId,
                    'type' => 'repair',
                ]);
            }

            $totalCost = 0;
            foreach (($validated['cost_details'] ?? []) as $detail) {
                if (!isset($detail['amount']) || $detail['amount'] === null) {
                    continue;
                }
                VehicleMaintenanceCostDetail::create([
                    'vehicle_maintenance_id' => $maintenance->id,
                    'type' => $detail['type'],
                    'description' => $detail['description'] ?? null,
                    'amount' => $detail['amount'],
                ]);
                $totalCost += (float) $detail['amount'];
            }

            $maintenance->update(['total_cost' => $totalCost]);
            DB::commit();

            return redirect()->route('vehicle-maintenances.show', $maintenance)->with('status', 'Vehicle maintenance created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create maintenance: ' . $e->getMessage()]);
        }
    }

    public function show(VehicleMaintenance $vehicleMaintenance): View
    {
        $vehicleMaintenance->load([
            'unit.division',
            'order',
            'orderStatus',
            'identificationPics.employee.position',
            'repairPics.employee.position',
            'costDetails',
        ]);

        return view('vehicle-maintenances.show', compact('vehicleMaintenance'));
    }

    public function edit(VehicleMaintenance $vehicleMaintenance): View
    {
        $vehicleMaintenance->load([
            'pics',
            'costDetails',
            'identificationPics.employee.position',
            'repairPics.employee.position',
        ]);
        $units = Unit::with('division')->orderBy('code')->get();
        $orders = Order::with('orderStatus')->orderByDesc('id')->limit(300)->get();
        $statuses = OrderStatus::orderBy('name')->get();

        return view('vehicle-maintenances.edit', compact('vehicleMaintenance', 'units', 'orders', 'statuses'));
    }

    public function searchPic(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->with('position')
            ->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($qUser) use ($q) {
                        $qUser->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get();

        return response()->json($employees->map(function (Employee $employee) {
            return [
                'id' => $employee->id,
                'name' => trim($employee->full_name . ($employee->position?->nama ? ' - ' . $employee->position->nama : '')),
            ];
        })->values());
    }

    public function update(Request $request, VehicleMaintenance $vehicleMaintenance): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_type' => ['required', 'in:PERBAIKAN,PERAWATAN,ADMINISTRASI,LAINNYA'],
            'damage_description' => ['nullable', 'string'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'odo_identification' => ['nullable', 'numeric', 'min:0'],
            'identified_at' => ['nullable', 'date'],
            'odo_repair' => ['nullable', 'numeric', 'min:0'],
            'repaired_at' => ['nullable', 'date'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'identification_pic_ids' => ['nullable', 'array'],
            'identification_pic_ids.*' => ['exists:employees,id'],
            'repair_pic_ids' => ['nullable', 'array'],
            'repair_pic_ids.*' => ['exists:employees,id'],
            'cost_details' => ['nullable', 'array'],
            'cost_details.*.type' => ['required_with:cost_details.*.amount', 'in:JASA,BARANG,LAINNYA'],
            'cost_details.*.description' => ['nullable', 'string'],
            'cost_details.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $vehicleMaintenance->update([
                'maintenance_type' => $validated['maintenance_type'],
                'damage_description' => $validated['damage_description'] ?? null,
                'order_id' => $validated['order_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'odo_identification' => $validated['odo_identification'] ?? null,
                'identified_at' => $validated['identified_at'] ?? null,
                'odo_repair' => $validated['odo_repair'] ?? null,
                'repaired_at' => $validated['repaired_at'] ?? null,
                'order_status_id' => $validated['order_status_id'],
                'updated_by' => auth()->id(),
            ]);

            $vehicleMaintenance->pics()->delete();
            foreach (($validated['identification_pic_ids'] ?? []) as $employeeId) {
                VehicleMaintenancePic::create([
                    'vehicle_maintenance_id' => $vehicleMaintenance->id,
                    'employee_id' => $employeeId,
                    'type' => 'identification',
                ]);
            }
            foreach (($validated['repair_pic_ids'] ?? []) as $employeeId) {
                VehicleMaintenancePic::create([
                    'vehicle_maintenance_id' => $vehicleMaintenance->id,
                    'employee_id' => $employeeId,
                    'type' => 'repair',
                ]);
            }

            $vehicleMaintenance->costDetails()->delete();
            $totalCost = 0;
            foreach (($validated['cost_details'] ?? []) as $detail) {
                if (!isset($detail['amount']) || $detail['amount'] === null) {
                    continue;
                }
                VehicleMaintenanceCostDetail::create([
                    'vehicle_maintenance_id' => $vehicleMaintenance->id,
                    'type' => $detail['type'],
                    'description' => $detail['description'] ?? null,
                    'amount' => $detail['amount'],
                ]);
                $totalCost += (float) $detail['amount'];
            }

            $vehicleMaintenance->update(['total_cost' => $totalCost]);
            DB::commit();

            return redirect()->route('vehicle-maintenances.show', $vehicleMaintenance)->with('status', 'Vehicle maintenance updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update maintenance: ' . $e->getMessage()]);
        }
    }

    public function destroy(VehicleMaintenance $vehicleMaintenance): RedirectResponse
    {
        $vehicleMaintenance->update(['deleted_by' => auth()->id()]);
        $vehicleMaintenance->delete();

        return redirect()->route('vehicle-maintenances.index')->with('status', 'Vehicle maintenance deleted.');
    }
}
