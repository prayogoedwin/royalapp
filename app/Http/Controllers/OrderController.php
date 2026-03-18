<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAmbulance;
use App\Models\OrderCrew;
use App\Models\OrderPhoto;
use App\Models\OrderStatus;
use App\Models\OrderReport;
use App\Models\OrderExpense;
use App\Models\OrderEtollTransaction;
use App\Models\OrderTowing;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with(['division', 'orderStatus'])
                ->select('orders.*');
            
            return DataTables::of($orders)
                ->addColumn('division_name', function ($order) {
                    return $order->division->nama;
                })
                ->addColumn('status_badge', function ($order) {
                    $colors = [
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                    ];
                    $color = $colors[$order->orderStatus->color ?? 'yellow'] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 rounded-full text-xs font-medium ' . $color . '">' . $order->orderStatus->name . '</span>';
                })
                ->addColumn('actions', function ($order) {
                    $actions = '';
                    
                    if (auth()->user()->hasPermission('show-orders')) {
                        $actions .= '<a href="' . route('orders.show', $order) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    
                    if (auth()->user()->hasPermission('edit-orders')) {
                        $actions .= '<a href="' . route('orders.edit', $order) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    
                    if (auth()->user()->hasPermission('delete-orders')) {
                        $actions .= '<form action="' . route('orders.destroy', $order) . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }
                    
                    return $actions ?: '-';
                })
                ->editColumn('price', function ($order) {
                    return 'Rp ' . number_format($order->price, 0, ',', '.');
                })
                ->editColumn('pickup_datetime', function ($order) {
                    return $order->pickup_datetime->format('M d, Y H:i');
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('orders.index');
    }

    public function create(): View
    {
        $divisions = Division::orderBy('nama')->get();
        $orderStatuses = OrderStatus::orderBy('name')->get();
        $employees = Employee::with('position')->where('status', 'active')->orderBy('full_name')->get();
        $units = Unit::with('division')->orderBy('division_id')->orderBy('code')->get();

        return view('orders.create', compact('divisions', 'orderStatuses', 'employees', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'unit_code' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'pickup_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            'pickup_datetime' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            
            // Ambulance specific
            'patient_condition' => ['nullable', 'string'],
            'medical_needs' => ['nullable', 'string'],
            
            // Towing specific
            'car_type' => ['nullable', 'string'],
            'car_condition' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'payment_requirement' => ['nullable', 'string'],
            
            // Crews
            'crew_ids' => ['nullable', 'array'],
            'crew_ids.*' => ['exists:employees,id'],
            
            // Photos
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'photo_titles' => ['nullable', 'array'],
            'photo_descriptions' => ['nullable', 'array'],
        ]);

        DB::beginTransaction();
        try {
            // Generate order number
            $division = Division::find($validated['division_id']);
            $prefix = $division->nama === 'Royal Ambulance' ? 'RA' : 'RT';
            $lastOrder = Order::where('order_number', 'like', $prefix . '.%')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastOrder) {
                $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix) + 1);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            $orderNumber = $prefix . '.' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'unit_code' => $validated['unit_code'] ?? null,
                'division_id' => $validated['division_id'],
                'order_status_id' => $validated['order_status_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_address' => $validated['pickup_address'],
                'destination_address' => $validated['destination_address'],
                'pickup_datetime' => $validated['pickup_datetime'],
                'price' => $validated['price'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Create ambulance details if applicable
            if ($division->nama === 'Royal Ambulance' && ($validated['patient_condition'] || $validated['medical_needs'])) {
                OrderAmbulance::create([
                    'order_id' => $order->id,
                    'patient_condition' => $validated['patient_condition'],
                    'medical_needs' => $validated['medical_needs'],
                    'created_by' => auth()->id(),
                ]);
            }

            // Create towing details if applicable
            if ($division->nama === 'Royal Towing' && ($validated['car_type'] || $validated['car_condition'])) {
                OrderTowing::create([
                    'order_id' => $order->id,
                    'car_type' => $validated['car_type'],
                    'car_condition' => $validated['car_condition'],
                    'receiver_phone' => $validated['receiver_phone'],
                    'payment_requirement' => $validated['payment_requirement'],
                    'created_by' => auth()->id(),
                ]);
            }

            // Add crews (role auto-filled from employee position)
            if (!empty($validated['crew_ids'])) {
                foreach ($validated['crew_ids'] as $employeeId) {
                    $employee = Employee::with('position')->find($employeeId);
                    OrderCrew::create([
                        'order_id' => $order->id,
                        'employee_id' => $employeeId,
                        'role' => $employee->position->nama ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Upload photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('orders/' . $order->id, 'public');
                    
                    OrderPhoto::create([
                        'order_id' => $order->id,
                        'title' => $validated['photo_titles'][$index] ?? 'Photo ' . ($index + 1),
                        'description' => $validated['photo_descriptions'][$index] ?? null,
                        'path' => $path,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return to_route('orders.index')->with('status', 'Order created successfully with number: ' . $orderNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()]);
        }
    }

    public function show(Order $order): View
    {
        $order->load([
            'division', 
            'orderStatus', 
            'orderAmbulance', 
            'orderTowing', 
            'orderCrews.employee.position',
            'orderPhotos',
            'orderReport',
            'orderExpenses',
            'orderEtollTransactions',
            'orderVehicleIssues',
            'createdBy',
            'updatedBy'
        ]);
        
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load([
            'orderAmbulance',
            'orderTowing',
            'orderCrews',
            'orderPhotos',
            'orderReport',
            'orderExpenses',
            'orderEtollTransactions',
            'orderVehicleIssues',
        ]);
        $divisions = Division::orderBy('nama')->get();
        $orderStatuses = OrderStatus::orderBy('name')->get();
        $employees = Employee::with('position')->where('status', 'active')->orderBy('full_name')->get();
        $units = Unit::with('division')->orderBy('division_id')->orderBy('code')->get();

        return view('orders.edit', compact('order', 'divisions', 'orderStatuses', 'employees', 'units'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $section = $request->input('update_section', 'basic');

        if ($section === 'order_report') {
            $validated = $request->validate([
                'km_awal' => ['nullable', 'numeric', 'min:0'],
                'km_akhir' => ['nullable', 'numeric', 'min:0'],
                'status' => ['nullable', 'string', 'max:50'],
                'notes' => ['nullable', 'string'],
            ]);
            DB::beginTransaction();
            try {
                OrderReport::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'km_awal' => $validated['km_awal'] ?? 0,
                        'km_akhir' => $validated['km_akhir'] ?? 0,
                        'status' => $validated['status'] ?? 'draft',
                        'notes' => $validated['notes'] ?? null,
                        'updated_by' => auth()->id(),
                        'created_by' => $order->orderReport->created_by ?? auth()->id(),
                    ]
                );
                DB::commit();
                return redirect()->route('orders.edit', $order)->with('status', 'Order report berhasil diupdate.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal update order report: ' . $e->getMessage()]);
            }
        }

        if ($section === 'order_expenses') {
            $validated = $request->validate([
                'expenses' => ['nullable', 'array'],
                'expenses.*.expense_category' => ['required_with:expenses.*.amount', 'string', 'max:50'],
                'expenses.*.description' => ['nullable', 'string'],
                'expenses.*.amount' => ['nullable', 'numeric', 'min:0'],
            ]);
            DB::beginTransaction();
            try {
                $items = $validated['expenses'] ?? [];
                foreach ($items as $i => $item) {
                    if (!isset($item['amount']) || $item['amount'] === null) {
                        continue;
                    }
                    $receiptPath = null;
                    if ($request->hasFile('expenses.'.$i.'.receipt_photo')) {
                        $receiptPath = $request->file('expenses.'.$i.'.receipt_photo')
                            ->store('order-expenses/'.$order->id, 'public');
                    }
                    OrderExpense::create([
                        'order_id' => $order->id,
                        'expense_category' => $item['expense_category'] ?? 'lainnya',
                        'description' => $item['description'] ?? null,
                        'amount' => $item['amount'],
                        'receipt_photo' => $receiptPath,
                        'created_by' => auth()->id(),
                    ]);
                }
                DB::commit();
                return redirect()->route('orders.edit', $order)->with('status', 'Order expenses berhasil disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal menyimpan order expenses: ' . $e->getMessage()]);
            }
        }

        if ($section === 'order_etoll') {
            $validated = $request->validate([
                'etolls' => ['nullable', 'array'],
                'etolls.*.balance_before' => ['nullable', 'numeric', 'min:0'],
                'etolls.*.balance_after' => ['nullable', 'numeric', 'min:0'],
            ]);
            DB::beginTransaction();
            try {
                $rows = $validated['etolls'] ?? [];
                foreach ($rows as $i => $row) {
                    $balanceBefore = $row['balance_before'] ?? null;
                    $balanceAfter = $row['balance_after'] ?? null;
                    $hasFile = $request->hasFile('etolls.'.$i.'.receipt_photo');
                    if ($balanceBefore === null && $balanceAfter === null && !$hasFile) {
                        continue;
                    }
                    $receiptPath = null;
                    if ($hasFile) {
                        $receiptPath = $request->file('etolls.'.$i.'.receipt_photo')
                            ->store('order-etoll/'.$order->id, 'public');
                    }
                    OrderEtollTransaction::create([
                        'order_id' => $order->id,
                        'topup_amount' => null,
                        'usage_amount' => null,
                        'balance_before' => $balanceBefore ?? 0,
                        'balance_after' => $balanceAfter ?? 0,
                        'receipt_photo' => $receiptPath,
                        'created_by' => auth()->id(),
                    ]);
                }
                DB::commit();
                return redirect()->route('orders.edit', $order)->with('status', 'Data e-toll berhasil disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal menyimpan data e-toll: ' . $e->getMessage()]);
            }
        }

        if ($section === 'crew') {
            $validated = $request->validate([
                'crew_ids' => ['nullable', 'array'],
                'crew_ids.*' => ['exists:employees,id'],
            ]);
            DB::beginTransaction();
            try {
                $order->orderCrews()->delete();
                $crewIds = $validated['crew_ids'] ?? [];
                if (!empty($crewIds)) {
                    foreach ($crewIds as $employeeId) {
                        $employee = Employee::with('position')->find($employeeId);
                        if (!$employee) continue;
                        OrderCrew::create([
                            'order_id' => $order->id,
                            'employee_id' => $employeeId,
                            'role' => $employee->position->nama ?? null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
                DB::commit();
                return redirect()->route('orders.edit', $order)->with('status', 'Crew berhasil diupdate.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal update crew: ' . $e->getMessage()]);
            }
        }

        if ($section === 'photos') {
            $validated = $request->validate([
                'photos' => ['nullable', 'array'],
                'photos.*' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'photo_titles' => ['nullable', 'array'],
                'photo_descriptions' => ['nullable', 'array'],
            ]);
            DB::beginTransaction();
            try {
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $index => $photo) {
                        $path = $photo->store('orders/' . $order->id, 'public');
                        OrderPhoto::create([
                            'order_id' => $order->id,
                            'title' => $validated['photo_titles'][$index] ?? 'Photo ' . ($index + 1),
                            'description' => $validated['photo_descriptions'][$index] ?? null,
                            'path' => $path,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
                DB::commit();
                return redirect()->route('orders.edit', $order)->with('status', 'Foto berhasil ditambah.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Gagal menambah foto: ' . $e->getMessage()]);
            }
        }

        // default: basic
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'unit_code' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'pickup_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            'pickup_datetime' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'patient_condition' => ['nullable', 'string'],
            'medical_needs' => ['nullable', 'string'],
            'car_type' => ['nullable', 'string'],
            'car_condition' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'payment_requirement' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'division_id' => $validated['division_id'],
                'order_status_id' => $validated['order_status_id'],
                'unit_code' => $validated['unit_code'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_address' => $validated['pickup_address'],
                'destination_address' => $validated['destination_address'],
                'pickup_datetime' => $validated['pickup_datetime'],
                'price' => $validated['price'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'updated_by' => auth()->id(),
            ]);

            $division = Division::find($validated['division_id']);
            if ($division->nama === 'Royal Ambulance') {
                OrderAmbulance::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'patient_condition' => $validated['patient_condition'] ?? null,
                        'medical_needs' => $validated['medical_needs'] ?? null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }
            if ($division->nama === 'Royal Towing') {
                OrderTowing::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'car_type' => $validated['car_type'] ?? null,
                        'car_condition' => $validated['car_condition'] ?? null,
                        'receiver_phone' => $validated['receiver_phone'] ?? null,
                        'payment_requirement' => $validated['payment_requirement'] ?? null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('orders.edit', $order)->with('status', 'Order berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal update order: ' . $e->getMessage()]);
        }
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Delete photos from storage
            foreach ($order->orderPhotos as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->update(['deleted_by' => auth()->id()]);
                $photo->delete();
            }
            
            // Soft delete related records
            if ($order->orderAmbulance) {
                $order->orderAmbulance->update(['deleted_by' => auth()->id()]);
                $order->orderAmbulance->delete();
            }
            
            if ($order->orderTowing) {
                $order->orderTowing->update(['deleted_by' => auth()->id()]);
                $order->orderTowing->delete();
            }
            
            foreach ($order->orderCrews as $crew) {
                $crew->update(['deleted_by' => auth()->id()]);
                $crew->delete();
            }
            
            // Soft delete order
            $order->update(['deleted_by' => auth()->id()]);
            $order->delete();

            DB::commit();

            return to_route('orders.index')->with('status', 'Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete order: ' . $e->getMessage()]);
        }
    }
    
    public function deletePhoto(OrderPhoto $photo): RedirectResponse
    {
        try {
            Storage::disk('public')->delete($photo->path);
            $photo->update(['deleted_by' => auth()->id()]);
            $photo->delete();
            
            return back()->with('status', 'Photo deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete photo: ' . $e->getMessage()]);
        }
    }
}
