<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAmbulance;
use App\Models\OrderCrew;
use App\Models\OrderEtollTransaction;
use App\Models\OrderExpense;
use App\Models\OrderPhoto;
use App\Models\OrderReport;
use App\Models\OrderStatus;
use App\Models\OrderTowing;
use App\Models\Unit;
use App\Services\Firebase\CrewPushNotifier;
use App\Services\SuratJalanChecklistService;
use App\Support\GoogleMapsLink;
use App\Support\OrderCategoryOptions;
use App\Support\OrderOptions;
use App\Support\UploadPath;
use App\Support\WhatsAppLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    private function buildEmployeeAvailability($employees, ?int $excludeOrderId = null)
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return $employees->mapWithKeys(function (Employee $employee) use ($excludeOrderId, $monthStart, $monthEnd) {
            $hasOngoing = false;
            $pickupSlots = [];
            $monthlyOrderIds = [];
            $monthlyKm = 0.0;

            foreach ($employee->orderCrews as $crew) {
                $order = $crew->order;
                if (! $order || ! $order->orderStatus) {
                    continue;
                }

                if ($excludeOrderId && (int) $order->id === $excludeOrderId) {
                    continue;
                }

                // Extra guard: ignore soft deleted rows for precision.
                if ($order->deleted_at || $crew->deleted_at) {
                    continue;
                }

                $statusName = strtolower((string) $order->orderStatus->name);
                if ($statusName === 'ongoing') {
                    $hasOngoing = true;
                }

                // Conflict only for active schedules, exclude Done/Cancelled.
                if (in_array($statusName, ['pending', 'waiting', 'ongoing'], true) && $order->pickup_datetime) {
                    $pickupSlots[] = $order->pickup_datetime->format('Y-m-d\TH:i');
                }

                if (
                    $order->pickup_datetime
                    && $statusName !== 'cancelled'
                    && $order->pickup_datetime->between($monthStart, $monthEnd)
                ) {
                    $monthlyOrderIds[(int) $order->id] = true;
                    $monthlyKm += (float) ($order->orderReport?->km_total ?? 0);
                }
            }

            return [
                $employee->id => [
                    'has_ongoing' => $hasOngoing,
                    'pickup_slots' => array_values(array_unique($pickupSlots)),
                    'monthly_orders' => count($monthlyOrderIds),
                    'monthly_km' => $monthlyKm,
                ],
            ];
        });
    }

    public function index(Request $request)
    {
        $customerPhone = trim((string) $request->query('customer_phone', ''));

        if ($request->ajax()) {
            $orders = Order::with(['division', 'orderStatus'])
                ->select('orders.*')
                ->when($customerPhone !== '', fn ($query) => $query->where('customer_phone', $customerPhone))
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            return DataTables::of($orders)
                ->orderColumn('created_at', function ($query, string $order): void {
                    $query->orderBy('created_at', $order)->orderBy('id', $order);
                })
                ->addColumn('division_name', function ($order) {
                    return $order->division->nama;
                })
                ->addColumn('status_badge', function ($order) {
                    $colors = [
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                    ];
                    $color = $colors[$order->orderStatus->color ?? 'yellow'] ?? 'bg-gray-100 text-gray-800';

                    return '<span class="px-2 py-1 rounded-full text-xs font-medium '.$color.'">'.$order->orderStatus->name.'</span>';
                })
                ->addColumn('actions', function ($order) {
                    $actions = '';

                    if (auth()->user()->hasPermission('show-orders')) {
                        $actions .= '<a href="'.route('orders.show', $order).'" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                        $actions .= '<a href="'.route('orders.surat-jalan', $order).'" class="text-indigo-600 dark:text-indigo-400 hover:underline mr-3" target="_blank">Surat Jalan</a>';
                    }

                    if (auth()->user()->hasPermission('edit-orders')) {
                        $actions .= '<a href="'.route('orders.edit', $order).'" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }

                    if (auth()->user()->hasPermission('delete-orders')) {
                        $actions .= '<form action="'.route('orders.destroy', $order).'" method="POST" class="inline" onsubmit="return confirm(\'Are you sure?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </form>';
                    }

                    return $actions ?: '-';
                })
                ->editColumn('price', function ($order) {
                    return 'Rp '.number_format($order->price, 0, ',', '.');
                })
                ->editColumn('pickup_datetime', function ($order) {
                    return $order->pickup_datetime->format('M d, Y H:i');
                })
                ->editColumn('created_at', function ($order) {
                    return $order->created_at?->format('M d, Y H:i') ?? '-';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('orders.index', [
            'customerPhone' => $customerPhone,
        ]);
    }

    public function create(Request $request): View
    {
        $divisions = Division::orderBy('nama')->get();
        $orderStatuses = OrderStatus::orderBy('name')->get();
        $employees = Employee::with([
            'position',
            'orderCrews.order.orderStatus',
            'orderCrews.order.orderReport',
        ])->whereHas('position', function ($query) {
            $query->whereIn(DB::raw('LOWER(nama)'), ['driver', 'nurse']);
        })->orderBy('full_name')->get();
        $units = Unit::with('division')->orderBy('division_id')->orderBy('code')->get();
        $paymentMethods = OrderOptions::paymentMethods();
        $paymentStatuses = OrderOptions::paymentStatuses();
        $employeeAvailability = $this->buildEmployeeAvailability($employees);

        $prefillName = $request->query('customer_name');
        $prefillPhone = WhatsAppLink::localDisplay($request->query('customer_phone'));

        return view('orders.create', compact(
            'divisions',
            'orderStatuses',
            'employees',
            'units',
            'employeeAvailability',
            'paymentMethods',
            'paymentStatuses',
            'prefillName',
            'prefillPhone',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'unit_code' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'appointment' => ['nullable', 'string', 'max:255'],
            'pickup_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            ...$this->locationRules(),
            'pickup_datetime' => ['required', 'date'],
            'price' => ['required', 'integer', 'min:0'],
            'payment_method' => ['nullable', Rule::in(array_keys(OrderOptions::paymentMethods()))],
            'payment_status' => ['required', Rule::in(array_keys(OrderOptions::paymentStatuses()))],
            'notes' => ['nullable', 'string'],

            // Ambulance specific
            'patient_condition' => ['nullable', 'string'],
            'medical_needs' => ['nullable', 'string'],

            // Towing specific
            'car_type' => ['nullable', 'string'],
            'car_condition' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'payment_requirement' => ['nullable', 'string'],

            // Crews (empty "Select Employee" rows are ignored)
            'crew_ids' => ['nullable', 'array'],
            'crew_ids.*' => ['nullable', 'exists:employees,id'],

            // Photos
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'photo_titles' => ['nullable', 'array'],
            'photo_descriptions' => ['nullable', 'array'],
            'payment_proof' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        DB::beginTransaction();
        try {
            $division = Division::findOrFail($validated['division_id']);
            $prefix = $division->nama === 'Royal Ambulance' ? 'RA' : 'RT';

            // Include soft-deleted rows: unique(order_number) still applies to them,
            // so generating from non-trashed-only can collide and fail every submit.
            $offset = strlen($prefix) + 2; // 1-based MySQL SUBSTRING after "RA." / "RT."
            $lastNumber = (int) Order::withTrashed()
                ->where('order_number', 'like', $prefix.'.%')
                ->selectRaw('MAX(CAST(SUBSTRING(order_number, ?) AS UNSIGNED)) as max_num', [$offset])
                ->value('max_num');
            $orderNumber = $prefix.'.'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number' => $orderNumber,
                'unit_code' => $validated['unit_code'] ?? null,
                'division_id' => $validated['division_id'],
                'order_status_id' => $validated['order_status_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'appointment' => $validated['appointment'] ?? null,
                'pickup_address' => $validated['pickup_address'],
                'destination_address' => $validated['destination_address'],
                ...$this->locationPayload($validated),
                'pickup_datetime' => $validated['pickup_datetime'],
                'price' => $validated['price'],
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($division->nama === 'Royal Ambulance' && (! empty($validated['patient_condition']) || ! empty($validated['medical_needs']))) {
                OrderAmbulance::create([
                    'order_id' => $order->id,
                    'patient_condition' => $validated['patient_condition'] ?? null,
                    'medical_needs' => $validated['medical_needs'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }

            if ($division->nama === 'Royal Towing' && (! empty($validated['car_type']) || ! empty($validated['car_condition']))) {
                OrderTowing::create([
                    'order_id' => $order->id,
                    'car_type' => $validated['car_type'] ?? null,
                    'car_condition' => $validated['car_condition'] ?? null,
                    'receiver_phone' => $validated['receiver_phone'] ?? null,
                    'payment_requirement' => $validated['payment_requirement'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }

            $crewIds = array_values(array_filter($validated['crew_ids'] ?? []));
            foreach ($crewIds as $employeeId) {
                $employee = Employee::with('position')->find($employeeId);
                if (! $employee) {
                    continue;
                }
                OrderCrew::create([
                    'order_id' => $order->id,
                    'employee_id' => $employeeId,
                    'role' => $employee->position->nama ?? null,
                    'created_by' => auth()->id(),
                ]);
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    if (! $photo) {
                        continue;
                    }
                    $path = $photo->store(UploadPath::dir('orders'), 'public');

                    OrderPhoto::create([
                        'order_id' => $order->id,
                        'title' => $validated['photo_titles'][$index] ?? 'Photo '.($index + 1),
                        'description' => $validated['photo_descriptions'][$index] ?? null,
                        'path' => $path,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $this->storePaymentProof($request, $order);

            DB::commit();

            app(CrewPushNotifier::class)->notifyOrderCreated(
                $order->fresh(['orderStatus', 'orderCrews.employee'])
            );

            return to_route('orders.index')->with('status', 'Order created successfully with number: '.$orderNumber);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->withErrors(['error' => 'Failed to create order: '.$e->getMessage()]);
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
            'updatedBy',
        ]);

        return view('orders.show', compact('order'));
    }

    public function suratJalan(Order $order, SuratJalanChecklistService $suratJalan): View
    {
        return view('orders.pdf.surat_jalan', $suratJalan->viewPayload($order, [
            'editable' => true,
            'is_public' => false,
            'is_webview' => false,
            'save_url' => route('orders.surat-jalan.save', $order),
            'csrf' => csrf_token(),
            'save_token' => null,
        ]));
    }

    public function saveSuratJalan(Request $request, Order $order, SuratJalanChecklistService $suratJalan): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.status' => ['nullable', 'string', 'in:ada,tidak'],
            'signatures' => ['nullable', 'array'],
            'signatures.pengirim' => ['nullable', 'string'],
            'signatures.penerima' => ['nullable', 'string'],
            'signatures.driver' => ['nullable', 'string'],
        ]);

        try {
            $suratJalan->save(
                $order,
                $validated['items'],
                $validated['signatures'] ?? [],
                $request->user()?->id,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 422);
        }

        $order->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Surat jalan tersimpan.',
            'data' => [
                'public_url' => $suratJalan->publicUrl($order),
                'wa_url' => $suratJalan->waUrl($order),
                'wa_native_url' => $suratJalan->waNativeUrl($order),
                'wa_intent_url' => $suratJalan->waIntentUrl($order),
            ],
        ]);
    }

    public function publicSuratJalan(Request $request, string $token, SuratJalanChecklistService $suratJalan): View|Response
    {
        $order = Order::query()->where('surat_jalan_public_token', $token)->firstOrFail();
        $view = view('orders.pdf.surat_jalan', $suratJalan->viewPayload($order, [
            'editable' => false,
            'is_public' => true,
            'is_webview' => false,
            'save_url' => null,
            'csrf' => null,
            'save_token' => null,
        ]));

        if (! $request->boolean('download')) {
            return $view;
        }

        $fileName = 'surat-jalan-'.str_replace(['/', '\\'], '-', $order->order_number).'.html';

        return response($view->render(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
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
        $employees = Employee::with([
            'position',
            'orderCrews.order.orderStatus',
            'orderCrews.order.orderReport',
        ])->whereHas('position', function ($query) {
            $query->whereIn(DB::raw('LOWER(nama)'), ['driver', 'nurse']);
        })->orderBy('full_name')->get();
        $units = Unit::with('division')->orderBy('division_id')->orderBy('code')->get();
        $paymentMethods = OrderOptions::paymentMethods();
        $paymentStatuses = OrderOptions::paymentStatuses();
        $employeeAvailability = $this->buildEmployeeAvailability($employees, (int) $order->id);

        return view('orders.edit', compact('order', 'divisions', 'orderStatuses', 'employees', 'units', 'employeeAvailability', 'paymentMethods', 'paymentStatuses'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $section = $request->input('update_section', 'basic');

        if ($section === 'order_report') {
            if (! $request->user()->hasPermission('edit-order-report')) {
                abort(403, 'Unauthorized action.');
            }
            $validated = $request->validate([
                'km_awal' => ['nullable', 'numeric', 'min:0'],
                'km_akhir' => ['nullable', 'numeric', 'min:0'],
                'order_status_id' => ['required', 'exists:order_statuses,id'],
                'saldo_etoll_before' => ['nullable', 'numeric', 'min:0'],
                'saldo_etoll_after' => ['nullable', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string'],
            ]);
            DB::beginTransaction();
            try {
                $deliverDatetime = $order->completionTimeForStatus((int) $validated['order_status_id']);

                $order->update([
                    'order_status_id' => $validated['order_status_id'],
                    'updated_by' => auth()->id(),
                ]);

                OrderReport::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'km_awal' => $validated['km_awal'] ?? 0,
                        'km_akhir' => $validated['km_akhir'] ?? 0,
                        'saldo_etoll_before' => $validated['saldo_etoll_before'] ?? null,
                        'saldo_etoll_after' => $validated['saldo_etoll_after'] ?? null,
                        'deliver_datetime' => $deliverDatetime,
                        'notes' => $validated['notes'] ?? null,
                        'updated_by' => auth()->id(),
                        'created_by' => $order->orderReport?->created_by ?? auth()->id(),
                    ]
                );
                DB::commit();

                app(CrewPushNotifier::class)->notifyOrderUpdated(
                    $order->fresh(['orderStatus', 'orderCrews.employee']),
                    'Status order diubah'
                );

                return redirect()->route('orders.edit', $order)->with('status', 'Order report berhasil diupdate.');
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Gagal update order report: '.$e->getMessage()]);
            }
        }

        if ($section === 'order_expenses') {
            if (! $request->user()->hasPermission('create-order-expenses')) {
                abort(403, 'Unauthorized action.');
            }
            $validated = $request->validate([
                'expenses' => ['nullable', 'array'],
                'expenses.*.expense_category' => ['required_with:expenses.*.amount', 'in:'.implode(',', array_keys(OrderCategoryOptions::expenseCategories()))],
                'expenses.*.description' => ['nullable', 'string'],
                'expenses.*.amount' => ['nullable', 'numeric', 'min:0'],
            ]);
            DB::beginTransaction();
            try {
                $items = $validated['expenses'] ?? [];
                foreach ($items as $i => $item) {
                    if (! isset($item['amount']) || $item['amount'] === null) {
                        continue;
                    }
                    $receiptPath = null;
                    if ($request->hasFile('expenses.'.$i.'.receipt_photo')) {
                        $receiptPath = $request->file('expenses.'.$i.'.receipt_photo')
                            ->store(UploadPath::dir('order-expenses'), 'public');
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

                return back()->withErrors(['error' => 'Gagal menyimpan order expenses: '.$e->getMessage()]);
            }
        }

        if ($section === 'order_etoll') {
            if (! $request->user()->hasPermission('create-order-etoll')) {
                abort(403, 'Unauthorized action.');
            }
            $validated = $request->validate([
                'etolls' => ['nullable', 'array'],
                'etolls.*.amount' => ['nullable', 'numeric', 'min:0'],
            ]);
            DB::beginTransaction();
            try {
                $rows = $validated['etolls'] ?? [];
                foreach ($rows as $i => $row) {
                    $amount = $row['amount'] ?? null;
                    $hasFile = $request->hasFile('etolls.'.$i.'.receipt_photo');
                    if ($amount === null && ! $hasFile) {
                        continue;
                    }
                    $receiptPath = null;
                    if ($hasFile) {
                        $receiptPath = $request->file('etolls.'.$i.'.receipt_photo')
                            ->store(UploadPath::dir('order-etoll'), 'public');
                    }
                    OrderEtollTransaction::create([
                        'order_id' => $order->id,
                        'topup_amount' => null,
                        'usage_amount' => $amount ?? 0,
                        'balance_before' => null,
                        'balance_after' => null,
                        'receipt_photo' => $receiptPath,
                        'created_by' => auth()->id(),
                    ]);
                }
                DB::commit();

                return redirect()->route('orders.edit', $order)->with('status', 'Data e-toll berhasil disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Gagal menyimpan data e-toll: '.$e->getMessage()]);
            }
        }

        if ($section === 'crew') {
            if (! $request->user()->hasPermission('edit-orders')) {
                abort(403, 'Unauthorized action.');
            }
            $validated = $request->validate([
                'crew_ids' => ['nullable', 'array'],
                'crew_ids.*' => ['exists:employees,id'],
            ]);
            DB::beginTransaction();
            try {
                $order->orderCrews()->delete();
                $crewIds = $validated['crew_ids'] ?? [];
                if (! empty($crewIds)) {
                    foreach ($crewIds as $employeeId) {
                        $employee = Employee::with('position')->find($employeeId);
                        if (! $employee) {
                            continue;
                        }
                        OrderCrew::create([
                            'order_id' => $order->id,
                            'employee_id' => $employeeId,
                            'role' => $employee->position->nama ?? null,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
                DB::commit();

                app(CrewPushNotifier::class)->notifyOrderCrewChanged(
                    $order->fresh(['orderStatus', 'orderCrews.employee'])
                );

                return redirect()->route('orders.edit', $order)->with('status', 'Crew berhasil diupdate.');
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Gagal update crew: '.$e->getMessage()]);
            }
        }

        if ($section === 'photos') {
            if (! $request->user()->hasPermission('create-order-photos')) {
                abort(403, 'Unauthorized action.');
            }
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
                        $path = $photo->store(UploadPath::dir('orders'), 'public');
                        OrderPhoto::create([
                            'order_id' => $order->id,
                            'title' => $validated['photo_titles'][$index] ?? 'Photo '.($index + 1),
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

                return back()->withErrors(['error' => 'Gagal menambah foto: '.$e->getMessage()]);
            }
        }

        // default: basic
        if (! $request->user()->hasPermission('edit-orders')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'unit_code' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'appointment' => ['nullable', 'string', 'max:255'],
            'pickup_address' => ['required', 'string'],
            'destination_address' => ['required', 'string'],
            ...$this->locationRules(),
            'pickup_datetime' => ['required', 'date'],
            'price' => ['required', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'in:'.implode(',', array_keys(OrderOptions::paymentMethods()))],
            'payment_status' => ['required', 'in:'.implode(',', array_keys(OrderOptions::paymentStatuses()))],
            'notes' => ['nullable', 'string'],
            'patient_condition' => ['nullable', 'string'],
            'medical_needs' => ['nullable', 'string'],
            'car_type' => ['nullable', 'string'],
            'car_condition' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'payment_requirement' => ['nullable', 'string'],
            'payment_proof' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        DB::beginTransaction();
        try {
            // Division is fixed at order creation; do not allow changing via request.
            $divisionId = $order->division_id;

            $order->update([
                'division_id' => $divisionId,
                'order_status_id' => $validated['order_status_id'],
                'unit_code' => $validated['unit_code'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'appointment' => $validated['appointment'] ?? null,
                'pickup_address' => $validated['pickup_address'],
                'destination_address' => $validated['destination_address'],
                ...$this->locationPayload($validated),
                'pickup_datetime' => $validated['pickup_datetime'],
                'price' => $validated['price'],
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $this->storePaymentProof($request, $order);

            $report = $order->orderReport;
            if ($report !== null && $report->deliver_datetime === null) {
                $completionTime = $order->completionTimeForStatus((int) $validated['order_status_id']);
                if ($completionTime !== null) {
                    $report->update([
                        'deliver_datetime' => $completionTime,
                        'updated_by' => auth()->id(),
                    ]);
                }
            }

            $division = Division::find($divisionId);
            if ($division && $division->nama === 'Royal Ambulance') {
                OrderAmbulance::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'patient_condition' => $validated['patient_condition'] ?? null,
                        'medical_needs' => $validated['medical_needs'] ?? null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }
            if ($division && $division->nama === 'Royal Towing') {
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

            app(CrewPushNotifier::class)->notifyOrderUpdated(
                $order->fresh(['orderStatus', 'orderCrews.employee'])
            );

            return redirect()->route('orders.edit', $order)->with('status', 'Order berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => 'Gagal update order: '.$e->getMessage()]);
        }
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::beginTransaction();
        try {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }

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

            return back()->withErrors(['error' => 'Failed to delete order: '.$e->getMessage()]);
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
            return back()->withErrors(['error' => 'Failed to delete photo: '.$e->getMessage()]);
        }
    }

    public function deleteOrderReport(Request $request, Order $order): RedirectResponse
    {
        if (! $request->user()->hasPermission('delete-order-report')) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->orderReport) {
            $order->orderReport()->delete();
        }

        return redirect()->route('orders.edit', $order)->with('status', 'Order report berhasil dihapus.');
    }

    public function deleteExpense(Request $request, OrderExpense $expense): RedirectResponse
    {
        if (! $request->user()->hasPermission('delete-order-expenses')) {
            abort(403, 'Unauthorized action.');
        }

        $orderId = $expense->order_id;
        if ($expense->receipt_photo) {
            Storage::disk('public')->delete($expense->receipt_photo);
        }
        $expense->delete();

        return redirect()->route('orders.edit', $orderId)->with('status', 'Expense berhasil dihapus.');
    }

    public function deleteEtoll(Request $request, OrderEtollTransaction $trx): RedirectResponse
    {
        if (! $request->user()->hasPermission('delete-order-etoll')) {
            abort(403, 'Unauthorized action.');
        }

        $orderId = $trx->order_id;
        if ($trx->receipt_photo) {
            Storage::disk('public')->delete($trx->receipt_photo);
        }
        $trx->delete();

        return redirect()->route('orders.edit', $orderId)->with('status', 'Transaksi e-toll berhasil dihapus.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function locationRules(): array
    {
        return [
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:pickup_longitude'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:pickup_latitude'],
            'pickup_maps_url' => ['nullable', 'string', 'max:2048'],
            'destination_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:destination_longitude'],
            'destination_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:destination_latitude'],
            'destination_maps_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function locationPayload(array $validated): array
    {
        return array_merge(
            $this->resolvedLocation('pickup', $validated),
            $this->resolvedLocation('destination', $validated),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function resolvedLocation(string $prefix, array $validated): array
    {
        $lat = $validated["{$prefix}_latitude"] ?? null;
        $lng = $validated["{$prefix}_longitude"] ?? null;
        $url = isset($validated["{$prefix}_maps_url"]) ? trim((string) $validated["{$prefix}_maps_url"]) : '';
        $url = $url === '' ? null : $url;

        if (($lat === null || $lng === null) && $url !== null) {
            $parsed = GoogleMapsLink::parse($url);
            if ($parsed !== null) {
                [$lat, $lng] = $parsed;
            }
        }

        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;

        if ($url === null && $lat !== null && $lng !== null) {
            $url = GoogleMapsLink::url($lat, $lng);
        }

        return [
            "{$prefix}_latitude" => $lat,
            "{$prefix}_longitude" => $lng,
            "{$prefix}_maps_url" => $url,
        ];
    }

    private function storePaymentProof(Request $request, Order $order): void
    {
        if (! $request->hasFile('payment_proof')) {
            return;
        }

        $order->storePaymentProofFile($request->file('payment_proof'));
    }
}
