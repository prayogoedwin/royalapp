<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\WhatsAppLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $stats = Order::query()
                ->selectRaw('customer_phone, COUNT(*) as total_orders, MAX(id) as last_order_id, MAX(created_at) as last_order_at')
                ->whereNotNull('customer_phone')
                ->where('customer_phone', '!=', '')
                ->groupBy('customer_phone');

            $query = DB::query()
                ->fromSub($stats, 'customer_stats')
                ->join('orders', 'orders.id', '=', 'customer_stats.last_order_id')
                ->select([
                    'customer_stats.customer_phone as customer_phone',
                    'orders.customer_name as customer_name',
                    'customer_stats.total_orders as total_orders',
                    'customer_stats.last_order_at as last_order_at',
                    'customer_stats.last_order_id as last_order_id',
                ]);

            return DataTables::of($query)
                ->filterColumn('customer_phone', function ($query, string $keyword): void {
                    $query->where('customer_stats.customer_phone', 'like', '%'.$keyword.'%');
                })
                ->filterColumn('customer_name', function ($query, string $keyword): void {
                    $query->where('orders.customer_name', 'like', '%'.$keyword.'%');
                })
                ->editColumn('customer_phone', function ($row) {
                    $phone = (string) $row->customer_phone;
                    $display = WhatsAppLink::localDisplay($phone) ?: $phone;

                    return '<span class="font-medium">'.e($display).'</span>
                        <button type="button" class="ml-2 text-xs text-blue-600 dark:text-blue-400 hover:underline" data-copy="'.e($display).'">Copy</button>';
                })
                ->editColumn('last_order_at', function ($row) {
                    return $row->last_order_at
                        ? Carbon::parse($row->last_order_at)->format('M d, Y H:i')
                        : '-';
                })
                ->addColumn('actions', function ($row) {
                    $phone = (string) $row->customer_phone;
                    $name = (string) $row->customer_name;
                    $actions = '<a href="'.route('customers.show', ['phone' => $phone]).'" class="text-green-600 dark:text-green-400 hover:underline mr-3">Detail</a>';

                    if (auth()->user()->hasPermission('create-orders')) {
                        $actions .= '<a href="'.route('orders.create', [
                            'customer_name' => $name,
                            'customer_phone' => WhatsAppLink::localDisplay($phone) ?: $phone,
                        ]).'" class="text-blue-600 dark:text-blue-400 hover:underline">Repeat Order</a>';
                    }

                    return $actions;
                })
                ->rawColumns(['customer_phone', 'actions'])
                ->orderColumn('last_order_at', function ($query, string $order): void {
                    $query->orderBy('customer_stats.last_order_at', $order);
                })
                ->orderColumn('total_orders', function ($query, string $order): void {
                    $query->orderBy('customer_stats.total_orders', $order);
                })
                ->make(true);
        }

        return view('customers.index');
    }

    public function show(Request $request): View
    {
        $phone = trim((string) $request->query('phone', ''));
        abort_if($phone === '', 404);

        $latest = Order::query()
            ->where('customer_phone', $phone)
            ->orderByDesc('id')
            ->first();

        abort_if($latest === null, 404);

        $totalOrders = Order::query()->where('customer_phone', $phone)->count();

        return view('customers.show', [
            'phone' => $phone,
            'displayPhone' => WhatsAppLink::localDisplay($phone) ?: $phone,
            'name' => $latest->customer_name,
            'totalOrders' => $totalOrders,
            'lastOrder' => $latest,
        ]);
    }
}
