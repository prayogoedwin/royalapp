<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    /**
     * @return array<string, mixed>
     */
    protected function periodViewData(Request $request): array
    {
        $bounds = $this->resolvePeriodBounds($request);
        $allTime = $request->boolean('all_time');
        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));

        return [
            'bounds' => $bounds,
            'periodLabel' => $this->periodLabel($bounds),
            'allTime' => $allTime,
            'year' => $year,
            'month' => $request->input('month'),
            'yearOptionsFrom' => 2020,
            'yearOptionsTo' => (int) now()->year + 1,
            'periodQuery' => array_filter([
                'all_time' => $allTime ? 1 : null,
                'year' => $allTime ? null : $year,
                'month' => $allTime ? null : $request->input('month'),
            ], fn ($value) => $value !== null && $value !== ''),
        ];
    }

    public function index(Request $request): View
    {
        $period = $this->periodViewData($request);
        $bounds = $period['bounds'];
        $doneStatusId = OrderStatus::where('name', 'Done')->value('id');

        $ordersDone = collect();
        $kmDone = collect();
        $tasksDone = collect();

        if ($doneStatusId) {
            $ordersDone = $this->ordersDoneRanking($bounds, (int) $doneStatusId, 5);
            $kmDone = $this->kmDoneRanking($bounds, (int) $doneStatusId, 5);
            $tasksDone = $this->tasksDoneRanking($bounds, (int) $doneStatusId, 5);
        }

        $absensiHadir = $this->absensiHadirRanking($bounds, 5);
        $taskComments = $this->taskCommentsRanking($bounds, 5);
        $taskAttachments = $this->taskAttachmentsRanking($bounds, 5);

        return view('leaderboard.index', array_merge($period, compact(
            'ordersDone',
            'kmDone',
            'tasksDone',
            'absensiHadir',
            'taskComments',
            'taskAttachments',
        )));
    }

    public function ranking(Request $request, string $metric): View
    {
        abort_unless(in_array($metric, ['orders-done', 'km-done'], true), 404);

        $period = $this->periodViewData($request);
        $doneStatusId = OrderStatus::where('name', 'Done')->value('id');
        $isKm = $metric === 'km-done';

        $rows = collect();
        if ($doneStatusId) {
            $rows = $isKm
                ? $this->kmDoneRanking($period['bounds'], (int) $doneStatusId)
                : $this->ordersDoneRanking($period['bounds'], (int) $doneStatusId);
        }

        return view('leaderboard.ranking', array_merge($period, [
            'metric' => $metric,
            'title' => $isKm ? __('Total KM (order Done)') : __('Order Done terbanyak'),
            'description' => $isKm
                ? __('Jumlah KM selisih (order report) pada order Done sebagai driver.')
                : __('Jumlah order berstatus Done sebagai driver.'),
            'rows' => $rows,
            'valueKey' => $isKm ? 'total_km' : 'total',
            'valueLabel' => $isKm ? __('Total KM') : __('Jumlah'),
            'formatKm' => $isKm,
        ]));
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function ordersDoneRanking(?array $bounds, int $doneStatusId, ?int $limit = null): Collection
    {
        $query = $this->driverDoneOrdersQuery($bounds, $doneStatusId)
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(DISTINCT orders.id) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function kmDoneRanking(?array $bounds, int $doneStatusId, ?int $limit = null): Collection
    {
        $query = $this->driverDoneOrdersQuery($bounds, $doneStatusId)
            ->join('order_reports', 'order_reports.order_id', '=', 'orders.id')
            ->whereNull('order_reports.deleted_at')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('SUM(COALESCE(order_reports.km_total, 0)) as total_km')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_km');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function driverDoneOrdersQuery(?array $bounds, int $doneStatusId): Builder
    {
        return DB::table('order_crews')
            ->join('orders', 'orders.id', '=', 'order_crews.order_id')
            ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('orders.order_status_id', $doneStatusId)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_crews.deleted_at')
            ->whereNotNull('employees.user_id')
            ->whereRaw('LOWER(order_crews.role) in (?, ?)', ['driver', 'sopir'])
            ->when($bounds, fn ($q) => $q->whereBetween('orders.created_at', [$bounds[0], $bounds[1]]));
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function tasksDoneRanking(?array $bounds, int $doneStatusId, int $limit): Collection
    {
        return DB::table('task_crews')
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
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function absensiHadirRanking(?array $bounds, int $limit): Collection
    {
        return DB::table('absensis')
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
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function taskCommentsRanking(?array $bounds, int $limit): Collection
    {
        return DB::table('task_comments')
            ->join('users', 'users.id', '=', 'task_comments.created_by')
            ->whereNull('task_comments.deleted_at')
            ->whereNotNull('task_comments.created_by')
            ->when($bounds, fn ($q) => $q->whereBetween('task_comments.created_at', [$bounds[0], $bounds[1]]))
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function taskAttachmentsRanking(?array $bounds, int $limit): Collection
    {
        return DB::table('task_attachments')
            ->join('users', 'users.id', '=', 'task_attachments.created_by')
            ->whereNull('task_attachments.deleted_at')
            ->whereNotNull('task_attachments.created_by')
            ->when($bounds, fn ($q) => $q->whereBetween('task_attachments.created_at', [$bounds[0], $bounds[1]]))
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }
}
