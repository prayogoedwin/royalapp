<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\LokasiTerkini;
use App\Models\OrderStatus;
use App\Support\GoogleMapsLink;
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

        $start = $this->parseDate($request->input('start_date'));
        $end = $this->parseDate($request->input('end_date'), endOfDay: true);
        if ($start !== null && $end !== null) {
            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [$start, $end];
        }

        $year = (int) $request->input('year', 0);
        if ($year >= 2000 && $year <= 2100) {
            $monthRaw = $request->input('month');
            if ($monthRaw !== null && $monthRaw !== '' && $monthRaw !== '0') {
                $month = (int) $monthRaw;
                if ($month >= 1 && $month <= 12) {
                    $monthStart = Carbon::create($year, $month, 1)->startOfDay();

                    return [$monthStart, (clone $monthStart)->endOfMonth()->endOfDay()];
                }
            }

            return [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        $today = now();

        return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
    }

    protected function parseDate(mixed $value, bool $endOfDay = false): ?Carbon
    {
        if (! is_string($value) || ! filled($value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        if ($date === false) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    /**
     * @return array<int, string>
     */
    protected function monthNames(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    protected function formatDay(Carbon $date): string
    {
        $months = $this->monthNames();

        return $date->day.' '.($months[$date->month] ?? $date->format('F')).' '.$date->year;
    }

    protected function periodLabel(?array $bounds): string
    {
        if ($bounds === null) {
            return __('Sepanjang waktu');
        }

        [$start, $end] = $bounds;
        $months = $this->monthNames();

        if ($start->isSameDay($end)) {
            if ($start->isToday()) {
                return __('Hari ini (:date)', ['date' => $this->formatDay($start)]);
            }

            return $this->formatDay($start);
        }

        if ($start->isSameMonth($end) && $start->day === 1 && $end->isSameDay($start->copy()->endOfMonth())) {
            return ($months[$start->month] ?? $start->format('F')).' '.$start->year;
        }

        if (
            $start->month === 1 && $start->day === 1
            && $end->month === 12 && $end->day === 31
            && $start->year === $end->year
        ) {
            return __('Tahun :year', ['year' => (string) $start->year]);
        }

        return $this->formatDay($start).' – '.$this->formatDay($end);
    }

    /**
     * @return array<string, mixed>
     */
    protected function periodViewData(Request $request): array
    {
        $bounds = $this->resolvePeriodBounds($request);
        $allTime = $request->boolean('all_time');
        $today = now()->toDateString();
        $startDate = $bounds ? $bounds[0]->toDateString() : $today;
        $endDate = $bounds ? $bounds[1]->toDateString() : $today;
        $year = (int) $request->input('year', $bounds ? $bounds[0]->year : now()->year);
        $year = max(2000, min(2100, $year));
        $month = $request->input('month');

        return [
            'bounds' => $bounds,
            'periodLabel' => $this->periodLabel($bounds),
            'allTime' => $allTime,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'month' => $month,
            'yearOptionsFrom' => 2020,
            'yearOptionsTo' => (int) now()->year + 1,
            'periodQuery' => array_filter([
                'all_time' => $allTime ? 1 : null,
                'start_date' => $allTime ? null : $startDate,
                'end_date' => $allTime ? null : $endDate,
                'year' => $allTime ? null : $year,
                'month' => $allTime ? null : $month,
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
        $ongoingDrivers = $this->ongoingDriversRanking();
        $lokasiPoints = $this->ongoingDriversLocations($ongoingDrivers);

        if ($doneStatusId) {
            $ordersDone = $this->ordersDoneRanking($bounds, (int) $doneStatusId);
            $kmDone = $this->kmDoneRanking($bounds, (int) $doneStatusId);
            $tasksDone = $this->tasksDoneRanking($bounds, (int) $doneStatusId, 5);
        }

        $absensiHadir = $this->absensiHadirRanking($bounds, 5);
        $taskComments = $this->taskCommentsRanking($bounds, 5);
        $taskAttachments = $this->taskAttachmentsRanking($bounds, 5);

        return view('leaderboard.index', array_merge($period, compact(
            'ongoingDrivers',
            'lokasiPoints',
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

    protected function ongoingDriversRanking(): Collection
    {
        $ongoingStatusId = OrderStatus::where('name', 'Ongoing')->value('id');
        if (! $ongoingStatusId) {
            return collect();
        }

        $driver = $this->driverOrdersQuery((int) $ongoingStatusId)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT orders.id) as total')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->orderBy('users.name')
            ->get();

        $orderNumbers = DB::table('order_crews')
            ->join('orders', 'orders.id', '=', 'order_crews.order_id')
            ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
            ->where('orders.order_status_id', $ongoingStatusId)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_crews.deleted_at')
            ->whereRaw('LOWER(order_crews.role) in (?, ?)', ['driver', 'sopir'])
            ->orderBy('orders.id')
            ->get(['employees.user_id', 'orders.order_number'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('order_number')->unique()->values()->implode(', '));

        return $driver->map(function ($row) use ($orderNumbers) {
            $row->order_numbers = $orderNumbers->get($row->id)
                ?? $orderNumbers->get((string) $row->id)
                ?? '';

            return $row;
        });
    }

    /**
     * @return list<array{id: int, user_id: int, name: string, latitude: float, longitude: float, waktu_hp: string, waktu_label: string, tanggal_label: string, maps_url: string|null, is_latest: bool}>
     */
    protected function ongoingDriversLocations(Collection $drivers): array
    {
        $userIds = $drivers->pluck('id')->filter()->values();
        if ($userIds->isEmpty()) {
            return [];
        }

        $today = now();
        $rows = LokasiTerkini::query()
            ->with('user:id,name')
            ->whereIn('user_id', $userIds)
            ->whereBetween('waktu_hp', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->orderBy('waktu_hp')
            ->orderBy('id')
            ->get();

        $latestIds = $rows
            ->groupBy('user_id')
            ->map(fn (Collection $group) => $group->last()?->id);

        $names = $drivers->keyBy('id');

        return $rows->map(function (LokasiTerkini $row) use ($latestIds, $names): array {
            $latitude = (float) $row->latitude;
            $longitude = (float) $row->longitude;
            $waktu = $row->waktu_hp;

            return [
                'id' => (int) $row->id,
                'user_id' => (int) $row->user_id,
                'name' => $row->user?->name ?? ($names->get($row->user_id)?->name ?? ''),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'waktu_hp' => $waktu?->toDateTimeString() ?? '',
                'waktu_label' => $waktu?->format('H:i') ?? '',
                'tanggal_label' => $waktu ? $this->formatDay($waktu).' '.$waktu->format('H:i') : '',
                'maps_url' => GoogleMapsLink::url($latitude, $longitude),
                'is_latest' => $latestIds->get($row->user_id) === $row->id
                    || $latestIds->get((string) $row->user_id) === $row->id,
            ];
        })->all();
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    protected function driverDoneOrdersQuery(?array $bounds, int $doneStatusId): Builder
    {
        return $this->driverOrdersQuery($doneStatusId)
            ->when($bounds, fn ($q) => $this->applyPeriodBounds($q, 'COALESCE(orders.pickup_datetime, orders.created_at)', $bounds));
    }

    protected function driverOrdersQuery(int $statusId): Builder
    {
        return DB::table('order_crews')
            ->join('orders', 'orders.id', '=', 'order_crews.order_id')
            ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('orders.order_status_id', $statusId)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_crews.deleted_at')
            ->whereNotNull('employees.user_id')
            ->whereRaw('LOWER(order_crews.role) in (?, ?)', ['driver', 'sopir']);
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

    /**
     * @param  array{0: Carbon, 1: Carbon}  $bounds
     */
    protected function applyPeriodBounds(Builder $query, string $columnSql, array $bounds): Builder
    {
        return $query->whereRaw($columnSql.' BETWEEN ? AND ?', [
            $bounds[0]->format('Y-m-d H:i:s'),
            $bounds[1]->format('Y-m-d H:i:s'),
        ]);
    }
}
