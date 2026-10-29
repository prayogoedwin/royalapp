<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\LokasiTerkini;
use App\Models\OrderStatus;
use App\Support\GoogleMapsLink;
use App\Support\ReportPeriod;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    protected function periodViewData(Request $request): array
    {
        $period = ReportPeriod::fromRequest($request);
        $data = $period->viewData();
        $driverScope = $this->resolveDriverScope($request);
        $data['driverScope'] = $driverScope;
        $data['periodQuery'] = array_filter(array_merge($period->queryParams(), [
            'drivers' => $driverScope === 'all' ? 'all' : null,
        ]), fn ($value) => $value !== null && $value !== '');

        return $data;
    }

    protected function resolveDriverScope(Request $request): string
    {
        return $request->input('drivers') === 'all' ? 'all' : 'ongoing';
    }

    public function index(Request $request): View
    {
        $period = $this->periodViewData($request);
        $bounds = $period['bounds'];
        $doneStatusId = OrderStatus::where('name', 'Done')->value('id');

        $ordersDone = collect();
        $kmDone = collect();
        $tasksDone = collect();
        $driverScope = $period['driverScope'];
        $mapDrivers = $this->assignDriverColors(
            $driverScope === 'all'
                ? $this->allDriversRanking()
                : $this->ongoingDriversRanking()
        );
        $lokasiPoints = $this->driversLocations($mapDrivers);

        if ($doneStatusId) {
            $ordersDone = $this->ordersDoneRanking($bounds, (int) $doneStatusId);
            $kmDone = $this->kmDoneRanking($bounds, (int) $doneStatusId);
            $tasksDone = $this->tasksDoneRanking($bounds, (int) $doneStatusId, 5);
        }

        $absensiHadir = $this->absensiHadirRanking($bounds, 5);
        $taskComments = $this->taskCommentsRanking($bounds, 5);
        $taskAttachments = $this->taskAttachmentsRanking($bounds, 5);

        return view('leaderboard.index', array_merge($period, compact(
            'mapDrivers',
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

    protected function allDriversRanking(): Collection
    {
        $ongoing = $this->ongoingDriversRanking()->keyBy('id');
        $statusCounts = $this->driverStatusCountsByUser();

        $positionDrivers = DB::table('employees')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->whereNull('employees.deleted_at')
            ->whereNotNull('employees.user_id')
            ->where(function ($query) {
                $query->whereNull('employees.status')
                    ->orWhere('employees.status', 'active');
            })
            ->where(function ($query) {
                $query->whereRaw('LOWER(positions.nama) in (?, ?)', ['driver', 'sopir'])
                    ->orWhereRaw('LOWER(COALESCE(positions.key, "")) in (?, ?)', ['driver', 'sopir']);
            })
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        $crewDrivers = DB::table('order_crews')
            ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->whereNull('order_crews.deleted_at')
            ->whereNull('employees.deleted_at')
            ->whereNotNull('employees.user_id')
            ->whereRaw('LOWER(order_crews.role) in (?, ?)', ['driver', 'sopir'])
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        return $positionDrivers
            ->concat($crewDrivers)
            ->unique('id')
            ->values()
            ->map(function ($row) use ($ongoing, $statusCounts) {
                $current = $ongoing->get($row->id) ?? $ongoing->get((string) $row->id);
                $counts = $statusCounts->get($row->id)
                    ?? $statusCounts->get((string) $row->id)
                    ?? [
                        'pending' => 0,
                        'waiting' => 0,
                        'ongoing' => 0,
                        'done' => 0,
                        'cancelled' => 0,
                    ];

                $row->pending = (int) $counts['pending'];
                $row->waiting = (int) $counts['waiting'];
                $row->ongoing = (int) $counts['ongoing'];
                $row->done = (int) $counts['done'];
                $row->cancelled = (int) $counts['cancelled'];
                $row->total = (int) ($current?->total ?? $row->ongoing);
                $row->order_numbers = $current?->order_numbers ?? '';

                return $row;
            })
            ->sortBy([
                ['total', 'desc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    /**
     * @return Collection<int|string, array{pending: int, waiting: int, ongoing: int, done: int, cancelled: int}>
     */
    protected function driverStatusCountsByUser(): Collection
    {
        $rows = DB::table('order_crews')
            ->join('orders', 'orders.id', '=', 'order_crews.order_id')
            ->join('order_statuses', 'order_statuses.id', '=', 'orders.order_status_id')
            ->join('employees', 'employees.id', '=', 'order_crews.employee_id')
            ->whereNull('orders.deleted_at')
            ->whereNull('order_crews.deleted_at')
            ->whereNotNull('employees.user_id')
            ->whereRaw('LOWER(order_crews.role) in (?, ?)', ['driver', 'sopir'])
            ->select(
                'employees.user_id',
                DB::raw('LOWER(order_statuses.name) as status_name'),
                DB::raw('COUNT(DISTINCT orders.id) as total')
            )
            ->groupBy('employees.user_id', DB::raw('LOWER(order_statuses.name)'))
            ->get();

        return $rows->groupBy('user_id')->map(function (Collection $group): array {
            $counts = [
                'pending' => 0,
                'waiting' => 0,
                'ongoing' => 0,
                'done' => 0,
                'cancelled' => 0,
            ];

            foreach ($group as $row) {
                $key = strtolower((string) $row->status_name);
                if (array_key_exists($key, $counts)) {
                    $counts[$key] = (int) $row->total;
                }
            }

            return $counts;
        });
    }

    /**
     * @return list<string>
     */
    protected function driverColorPalette(): array
    {
        return [
            '#dc2626', '#2563eb', '#16a34a', '#d97706', '#7c3aed', '#db2777',
            '#0891b2', '#65a30d', '#ea580c', '#4f46e5', '#0f766e', '#9f1239',
        ];
    }

    protected function assignDriverColors(Collection $drivers): Collection
    {
        $palette = $this->driverColorPalette();

        return $drivers->values()->map(function ($row, int $index) use ($palette) {
            $row->color = $palette[$index % count($palette)];

            return $row;
        });
    }

    /**
     * @return list<array{id: int, user_id: int, name: string, latitude: float, longitude: float, waktu_hp: string, waktu_label: string, tanggal_label: string, maps_url: string|null, is_latest: bool}>
     */
    protected function driversLocations(Collection $drivers): array
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

        $grouped = $rows->groupBy('user_id');
        $latestIds = $grouped->map(fn (Collection $group) => $group->last()?->id);
        $counts = $grouped->map->count();
        $names = $drivers->keyBy('id');

        return $rows->map(function (LokasiTerkini $row) use ($latestIds, $counts, $names): array {
            $latitude = (float) $row->latitude;
            $longitude = (float) $row->longitude;
            $waktu = $row->waktu_hp;
            $isLatest = $latestIds->get($row->user_id) === $row->id
                || $latestIds->get((string) $row->user_id) === $row->id;
            $driver = $names->get($row->user_id) ?? $names->get((string) $row->user_id);

            return [
                'id' => (int) $row->id,
                'user_id' => (int) $row->user_id,
                'name' => $row->user?->name ?? ($driver?->name ?? ''),
                'color' => $driver?->color ?? '#2563eb',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'waktu_hp' => $waktu?->toDateTimeString() ?? '',
                'waktu_label' => $waktu?->format('H:i') ?? '',
                'tanggal_label' => $waktu ? ReportPeriod::formatDay($waktu).' '.$waktu->format('H:i') : '',
                'maps_url' => GoogleMapsLink::url($latitude, $longitude),
                'is_latest' => $isLatest,
                'has_previous' => $isLatest && (int) $counts->get($row->user_id, $counts->get((string) $row->user_id, 0)) > 1,
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
