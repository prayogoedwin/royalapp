<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderVehicleIssue;
use App\Support\UploadPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderVehicleIssueController extends Controller
{
    public function index(Request $request): View
    {
        $issues = OrderVehicleIssue::with(['order', 'createdBy'])
            ->latest()
            ->paginate(15);

        return view('order-vehicle-issues.index', compact('issues'));
    }

    public function create(Order $order): View
    {
        return view('order-vehicle-issues.create', compact('order'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'issue_category' => ['required', 'in:mechanical,body,interior,safety,medical_equipment,other'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'issue_photo' => ['nullable', 'image', 'max:2048'],
            'repair_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $validated;
        $data['order_id'] = $order->id;
        $data['unit_code'] = $order->unit_code;
        $data['created_by'] = auth()->id();

        if ($request->hasFile('issue_photo')) {
            $data['issue_photo'] = $request->file('issue_photo')->store(UploadPath::dir('order-issues'), 'public');
        }

        if ($request->hasFile('repair_photo')) {
            $data['repair_photo'] = $request->file('repair_photo')->store(UploadPath::dir('order-issues'), 'public');
        }

        OrderVehicleIssue::create($data);

        if (request()->has('from') && request('from') === 'edit') {
            return redirect()->route('orders.edit', $order)->with('status', 'Vehicle issue berhasil ditambah.');
        }
        return to_route('orders.show', $order)->with('status', 'Vehicle issue created successfully.');
    }

    public function show(OrderVehicleIssue $orderVehicleIssue): View
    {
        $orderVehicleIssue->load(['order', 'createdBy', 'resolvedBy']);

        return view('order-vehicle-issues.show', compact('orderVehicleIssue'));
    }

    public function edit(OrderVehicleIssue $orderVehicleIssue): View
    {
        $orderVehicleIssue->load('order');

        return view('order-vehicle-issues.edit', compact('orderVehicleIssue'));
    }

    public function update(Request $request, OrderVehicleIssue $orderVehicleIssue): RedirectResponse
    {
        $validated = $request->validate([
            'issue_category' => ['required', 'in:mechanical,body,interior,safety,medical_equipment,other'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'issue_photo' => ['nullable', 'image', 'max:2048'],
            'repair_photo' => ['nullable', 'image', 'max:2048'],
            'is_resolved' => ['nullable', 'boolean'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $data = $validated;

        if ($request->hasFile('issue_photo')) {
            if ($orderVehicleIssue->issue_photo) {
                \Storage::disk('public')->delete($orderVehicleIssue->issue_photo);
            }
            $data['issue_photo'] = $request->file('issue_photo')->store(UploadPath::dir('order-issues'), 'public');
        }

        if ($request->hasFile('repair_photo')) {
            if ($orderVehicleIssue->repair_photo) {
                \Storage::disk('public')->delete($orderVehicleIssue->repair_photo);
            }
            $data['repair_photo'] = $request->file('repair_photo')->store(UploadPath::dir('order-issues'), 'public');
        }

        $isResolved = (bool) ($data['is_resolved'] ?? $orderVehicleIssue->is_resolved);
        $data['is_resolved'] = $isResolved;

        if ($isResolved && !$orderVehicleIssue->resolved_at) {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->id();
        } elseif (!$isResolved) {
            $data['resolved_at'] = null;
            $data['resolved_by'] = null;
        }

        $orderVehicleIssue->update($data);

        return to_route('order-vehicle-issues.show', $orderVehicleIssue)->with('status', 'Vehicle issue updated successfully.');
    }

    public function destroy(OrderVehicleIssue $orderVehicleIssue): RedirectResponse
    {
        if ($orderVehicleIssue->issue_photo) {
            \Storage::disk('public')->delete($orderVehicleIssue->issue_photo);
        }
        if ($orderVehicleIssue->repair_photo) {
            \Storage::disk('public')->delete($orderVehicleIssue->repair_photo);
        }

        $orderVehicleIssue->delete();

        return back()->with('status', 'Vehicle issue deleted successfully.');
    }
}
