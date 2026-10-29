<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportPeriod
{
    /**
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    public function __construct(
        public readonly ?array $bounds,
        public readonly bool $allTime,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $year,
        public readonly mixed $month,
        public readonly string $label,
    ) {}

    public static function requestHasExplicitFilter(Request $request): bool
    {
        return $request->boolean('all_time')
            || $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('year')
            || $request->filled('month');
    }

    public static function fromRequest(Request $request): self
    {
        $bounds = static::resolveBounds($request);
        $allTime = $request->boolean('all_time');
        $today = now()->toDateString();
        $startDate = $bounds ? $bounds[0]->toDateString() : $today;
        $endDate = $bounds ? $bounds[1]->toDateString() : $today;
        $year = (int) $request->input('year', $bounds ? $bounds[0]->year : now()->year);
        $year = max(2000, min(2100, $year));

        return new self(
            bounds: $bounds,
            allTime: $allTime,
            startDate: $startDate,
            endDate: $endDate,
            year: $year,
            month: $request->input('month'),
            label: static::label($bounds),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return [
            'bounds' => $this->bounds,
            'periodLabel' => $this->label,
            'allTime' => $this->allTime,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'year' => $this->year,
            'month' => $this->month,
            'yearOptionsFrom' => 2020,
            'yearOptionsTo' => (int) now()->year + 1,
            'periodQuery' => $this->queryParams(),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    public function queryParams(): array
    {
        return array_filter([
            'all_time' => $this->allTime ? 1 : null,
            'start_date' => $this->allTime ? null : $this->startDate,
            'end_date' => $this->allTime ? null : $this->endDate,
            'year' => $this->allTime ? null : $this->year,
            'month' => $this->allTime ? null : $this->month,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function fileSuffix(): string
    {
        if ($this->allTime || $this->bounds === null) {
            return 'semua';
        }

        $start = $this->bounds[0]->toDateString();
        $end = $this->bounds[1]->toDateString();

        return $start === $end ? $start : $start.'_'.$end;
    }

    public function cacheSuffix(): string
    {
        if ($this->allTime || $this->bounds === null) {
            return 'all';
        }

        return $this->bounds[0]->format('Y-m-d').'_'.$this->bounds[1]->format('Y-m-d');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public static function resolveBounds(Request $request): ?array
    {
        if ($request->boolean('all_time')) {
            return null;
        }

        $start = static::parseDate($request->input('start_date'));
        $end = static::parseDate($request->input('end_date'), endOfDay: true);
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

    public static function parseDate(mixed $value, bool $endOfDay = false): ?Carbon
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
     * @param  array{0: Carbon, 1: Carbon}|null  $bounds
     */
    public static function label(?array $bounds): string
    {
        if ($bounds === null) {
            return __('Sepanjang waktu');
        }

        [$start, $end] = $bounds;
        $months = static::monthNames();

        if ($start->isSameDay($end)) {
            if ($start->isToday()) {
                return __('Hari ini (:date)', ['date' => static::formatDay($start)]);
            }

            return static::formatDay($start);
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

        return static::formatDay($start).' – '.static::formatDay($end);
    }

    public static function formatDay(Carbon $date): string
    {
        $months = static::monthNames();

        return $date->day.' '.($months[$date->month] ?? $date->format('F')).' '.$date->year;
    }

    /**
     * @return array<int, string>
     */
    public static function monthNames(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }
}
