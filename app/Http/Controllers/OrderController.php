<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAmbulance;
use App\Models\OrderCrew;
use App\Models\OrderPhoto;
use App\Models\OrderStatus;
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
            'createdBy',
            'updatedBy'
        ]);
        
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load(['orderAmbulance', 'orderTowing', 'orderCrews', 'orderPhotos']);
        $divisions = Division::orderBy('nama')->get();
        $orderStatuses = OrderStatus::orderBy('name')->get();
        $employees = Employee::with('position')->where('status', 'active')->orderBy('full_name')->get();
        $units = Unit::with('division')->orderBy('division_id')->orderBy('code')->get();

        return view('orders.edit', compact('order', 'divisions', 'orderStatuses', 'employees', 'units'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
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
            
            'crew_ids' => ['nullable', 'array'],
            'crew_ids.*' => ['exists:employees,id'],
            
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'photo_titles' => ['nullable', 'array'],
            'photo_descriptions' => ['nullable', 'array'],
        ]);

        DB::beginTransaction();
        try {
            $order->update([
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
                'updated_by' => auth()->id(),
            ]);

            $division = Division::find($validated['division_id']);

            // Update or create ambulance details
            if ($division->nama === 'Royal Ambulance') {
                OrderAmbulance::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'patient_condition' => $validated['patient_condition'],
                        'medical_needs' => $validated['medical_needs'],
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            // Update or create towing details
            if ($division->nama === 'Royal Towing') {
                OrderTowing::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'car_type' => $validated['car_type'],
                        'car_condition' => $validated['car_condition'],
                        'receiver_phone' => $validated['receiver_phone'],
                        'payment_requirement' => $validated['payment_requirement'],
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            // Update crews
            $order->orderCrews()->delete();
            if (!empty($validated['crew_ids'])) {
                foreach ($validated['crew_ids'] as $index => $employeeId) {
                    OrderCrew::create([
                        'order_id' => $order->id,
                        'employee_id' => $employeeId,
                        'role' => $validated['crew_roles'][$index] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // Add new photos
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

            return to_route('orders.index')->with('status', 'Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update order: ' . $e->getMessage()]);
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
