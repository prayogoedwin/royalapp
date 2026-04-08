<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /**
     * @return array{0: Carbon, 1: Carbon}|null null = all time
     */
    protected function resolvePeriodBounds(Request $request): ?array
    {
        if ($request->boolean('all_time')) {
            return null;
        }

        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));

        $monthRaw = $request->input('month');
        if ($monthRaw !== null && $monthRaw !== '' && $monthRaw !== '0') {
            $m = (int) $monthRaw;
            if ($m >= 1 && $m <= 12) {
                $start = Carbon::create($year, $m, 1)->startOfDay();
                $end = (clone $start)->endOfMonth()->endOfDay();

                return [$start, $end];
            }
        }

        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        return [$start, $end];
    }

    protected function periodLabel(?array $bounds): string
    {
        if ($bounds === null) {
            return __('Sepanjang waktu');
        }

        [$start, $end] = $bounds;

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($start->month === $end->month && $start->year === $end->year) {
            return ($bulan[$start->month] ?? $start->format('F')).' '.$start->year;
        }

        return __('Tahun :year (semua bulan)', ['year' => (string) $start->year]);
    }

    public function index(Request $request): View
    {
        $bounds = $this->resolvePeriodBounds($request);
        $periodLabel = $this->periodLabel($bounds);

        $allTime = $request->boolean('all_time');
        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));
        $month = $request->input('month');

        $yearOptionsFrom = 2020;
        $yearOptionsTo = (int) now()->year + 1;

        $doneStatusId = OrderStatus::where('name', 'Done')->value('id');

        $ordersDone = collect();
        $kmDone = collect();
        $tasksDone = collect();

        if ($doneStatusId) {
            $ordersDone = DB::table('order_crews')
                ->join('orders', 'orders.id', '=', 'order_crews.order_id')
                ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->where('orders.order_status_id', $doneStatusId)
                ->whereNull('orders.deleted_at')
                ->whereNull('order_crews.deleted_at')
                ->whereNotNull('employees.user_id')
                ->when($bounds, fn ($q) => $q->whereBetween('orders.created_at', [$bounds[0], $bounds[1]]))
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(DISTINCT orders.id) as total'))
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $kmDone = DB::table('order_crews')
                ->join('orders', 'orders.id', '=', 'order_crews.order_id')
                ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->join('order_reports', 'order_reports.order_id', '=', 'orders.id')
                ->where('orders.order_status_id', $doneStatusId)
                ->whereNull('orders.deleted_at')
                ->whereNull('order_crews.deleted_at')
                ->whereNull('order_reports.deleted_at')
                ->whereNotNull('employees.user_id')
                ->when($bounds, fn ($q) => $q->whereBetween('orders.created_at', [$bounds[0], $bounds[1]]))
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('SUM(COALESCE(order_reports.km_total, 0)) as total_km')
                )
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('total_km')
                ->limit(5)
                ->get();

            $tasksDone = DB::table('task_crews')
                ->join('tasks', 'tasks.id', '=', 'task_crews.task_id')
                ->join('employees', 'employees.id', '=', 'task_crews.employee_id')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->where('tasks.order_status_id', $doneStatusId)
                ->whereNull('tasks.deleted_at')
                ->whereNull('task_crews.deleted_at')
                ->whereNotNull('employees.user_id')
                ->when($bounds, fn ($q) => $q->whereBetween('tasks.created_at', [$bounds[0], $bounds[1]]))
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(DISTINCT tasks.id) as total'))
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        $absensiHadir = DB::table('absensis')
            ->join('employees', 'employees.id', '=', 'absensis.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('absensis.status', Absensi::STATUS_HADIR)
            ->whereNull('absensis.deleted_at')
            ->whereNotNull('employees.user_id')
            ->when($bounds, function ($q) use ($bounds) {
                $q->whereBetween('absensis.tanggal', [
                    $bounds[0]->toDateString(),
                    $bounds[1]->toDateString(),
                ]);
            })
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $taskComments = DB::table('task_comments')
            ->join('users', 'users.id', '=', 'task_comments.created_by')
            ->whereNull('task_comments.deleted_at')
            ->whereNotNull('task_comments.created_by')
            ->when($bounds, fn ($q) => $q->whereBetween('task_comments.created_at', [$bounds[0], $bounds[1]]))
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $taskAttachments = DB::table('task_attachments')
            ->join('users', 'users.id', '=', 'task_attachments.created_by')
            ->whereNull('task_attachments.deleted_at')
            ->whereNotNull('task_attachments.created_by')
            ->when($bounds, fn ($q) => $q->whereBetween('task_attachments.created_at', [$bounds[0], $bounds[1]]))
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('leaderboard.index', compact(
            'ordersDone',
            'kmDone',
            'tasksDone',
            'absensiHadir',
            'taskComments',
            'taskAttachments',
            'allTime',
            'year',
            'month',
            'yearOptionsFrom',
            'yearOptionsTo',
            'periodLabel'
        ));
    }
}
