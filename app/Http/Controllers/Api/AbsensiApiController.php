<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Support\UploadPath;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiApiController extends Controller
{
    private function okResponse(mixed $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [],
        ], $status);
    }

    private function getEmployeeFromRequest(Request $request)
    {
        return $request->user()?->employee;
    }

    public function storeMasuk(Request $request): JsonResponse
    {
        $employee = $this->getEmployeeFromRequest($request);
        if (!$employee) {
            return $this->errorResponse('Employee not found for this user.', 404);
        }

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'keterangan' => ['nullable', 'string'],
            'foto_masuk' => ['nullable', 'image', 'max:2048'],
            'device_info' => ['nullable', 'array'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();
        $jamMasuk = !empty($validated['jam_masuk'])
            ? Carbon::createFromFormat('H:i', $validated['jam_masuk'])->format('H:i:s')
            : now()->format('H:i:s');

        $lockedStatuses = [
            Absensi::STATUS_ALPHA,
            Absensi::STATUS_IZIN,
            Absensi::STATUS_SAKIT,
            Absensi::STATUS_CUTI,
            Absensi::STATUS_LIBUR,
            Absensi::STATUS_TIDAK_ABSEN_MASUK,
        ];

        $absensi = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (!$absensi) {
            $absensi = Absensi::create([
                'employee_id' => $employee->id,
                'pool_id' => $employee->pool_id,
                'tanggal' => $tanggal,
                'jam_masuk' => $jamMasuk,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'lat_masuk' => $validated['lat'] ?? null,
                'lng_masuk' => $validated['lng'] ?? null,
                'status' => Absensi::STATUS_HADIR,
                'is_overnight' => false,
                'device_info' => $validated['device_info'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        } else {
            if ($absensi->status && in_array($absensi->status, $lockedStatuses, true)) {
                return $this->errorResponse('Jam masuk tidak bisa diinput karena status sudah terkunci.', 422);
            }
            if ($absensi->jam_masuk !== null) {
                return $this->errorResponse('Jam masuk sudah diisi.', 422);
            }

            $absensi->jam_masuk = $jamMasuk;
            $absensi->lat = $validated['lat'] ?? $absensi->lat;
            $absensi->lng = $validated['lng'] ?? $absensi->lng;
            $absensi->lat_masuk = $validated['lat'] ?? $absensi->lat_masuk;
            $absensi->lng_masuk = $validated['lng'] ?? $absensi->lng_masuk;
            $absensi->status = Absensi::STATUS_HADIR;
            $absensi->pool_id = $employee->pool_id;
            $absensi->device_info = $validated['device_info'] ?? $absensi->device_info;
            $absensi->keterangan = $validated['keterangan'] ?? $absensi->keterangan;
            $absensi->save();
        }

        if ($request->hasFile('foto_masuk')) {
            $path = $request->file('foto_masuk')->store(UploadPath::dir('absensi'), 'public');
            $absensi->foto_masuk = $path;
            $absensi->save();
        }

        return $this->okResponse($absensi->fresh(), 'Absen masuk berhasil.');
    }

    public function storePulang(Request $request): JsonResponse
    {
        $employee = $this->getEmployeeFromRequest($request);
        if (!$employee) {
            return $this->errorResponse('Employee not found for this user.', 404);
        }

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'keterangan' => ['nullable', 'string'],
            'foto_pulang' => ['nullable', 'image', 'max:2048'],
            'device_info' => ['nullable', 'array'],
        ]);

        $tanggalInput = $validated['tanggal'] ?? now()->toDateString();
        $jamPulang = !empty($validated['jam_pulang'])
            ? Carbon::createFromFormat('H:i', $validated['jam_pulang'])->format('H:i:s')
            : now()->format('H:i:s');

        $lockedStatuses = [
            Absensi::STATUS_ALPHA,
            Absensi::STATUS_IZIN,
            Absensi::STATUS_SAKIT,
            Absensi::STATUS_CUTI,
            Absensi::STATUS_LIBUR,
            Absensi::STATUS_TIDAK_ABSEN_PULANG,
        ];

        $absensi = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->whereDate('tanggal', $tanggalInput)
            ->first();

        if (!$absensi) {
            // Fallback untuk shift lintas hari (window 3 hari).
            $rangeStart = Carbon::today()->subDays(3)->toDateString();
            $rangeEnd = Carbon::today()->addDays(1)->toDateString();

            $absensi = Absensi::query()
                ->where('employee_id', $employee->id)
                ->whereNotNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->first();
        }

        if (!$absensi) {
            return $this->errorResponse('Absensi pulang tidak ditemukan atau sudah diisi.', 422);
        }

        if ($absensi->status && in_array($absensi->status, $lockedStatuses, true)) {
            return $this->errorResponse('Jam pulang tidak bisa diinput karena status sudah terkunci.', 422);
        }

        $jamMasuk = $absensi->jam_masuk;
        $isOvernight = $jamMasuk && $jamPulang ? $jamPulang <= $jamMasuk : false;

        $absensi->jam_pulang = $jamPulang;
        $absensi->lat = $validated['lat'] ?? $absensi->lat;
        $absensi->lng = $validated['lng'] ?? $absensi->lng;
        $absensi->lat_pulang = $validated['lat'] ?? $absensi->lat_pulang;
        $absensi->lng_pulang = $validated['lng'] ?? $absensi->lng_pulang;
        $absensi->is_overnight = $absensi->is_overnight ?? $isOvernight;
        $absensi->status = Absensi::STATUS_HADIR;
        $absensi->pool_id = $employee->pool_id;
        $absensi->device_info = $validated['device_info'] ?? $absensi->device_info;
        $absensi->keterangan = $validated['keterangan'] ?? $absensi->keterangan;
        $absensi->save();

        if ($request->hasFile('foto_pulang')) {
            $path = $request->file('foto_pulang')->store(UploadPath::dir('absensi'), 'public');
            $absensi->foto_pulang = $path;
            $absensi->save();
        }

        return $this->okResponse($absensi->fresh(), 'Absen pulang berhasil.');
    }

    public function latest(Request $request): JsonResponse
    {
        $employee = $this->getEmployeeFromRequest($request);
        if (!$employee) {
            return $this->errorResponse('Employee not found for this user.', 404);
        }

        $lastMasuk = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('jam_masuk')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        $lastPulang = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('jam_pulang')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        $pendingCheckout = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->whereBetween('tanggal', [Carbon::today()->subDays(3)->toDateString(), Carbon::today()->addDays(1)->toDateString()])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->first();

        return $this->okResponse([
            'last_masuk' => $lastMasuk,
            'last_pulang' => $lastPulang,
            'pending_checkout' => $pendingCheckout,
        ]);
    }

    public function monthlyRecap(Request $request): JsonResponse
    {
        $employee = $this->getEmployeeFromRequest($request);
        if (!$employee) {
            return $this->errorResponse('Employee not found for this user.', 404);
        }

        $now = now();
        $month = (int) $request->query('month', (int) $now->format('m'));
        $year = (int) $request->query('year', (int) $now->format('Y'));

        if ($month < 1 || $month > 12) {
            return $this->errorResponse('Month must be between 1 and 12.', 422);
        }

        if ($year < 2000 || $year > 2100) {
            return $this->errorResponse('Year is invalid.', 422);
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn (Absensi $item) => $item->tanggal?->format('Y-m-d'));

        $items = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateKey = $cursor->format('Y-m-d');
            $items[] = [
                'tanggal' => $dateKey,
                'absensi' => $rows->get($dateKey),
            ];
            $cursor->addDay();
        }

        $summaryByStatus = $rows->groupBy('status')->map->count();

        return $this->okResponse([
            'month' => $month,
            'year' => $year,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'total_days_in_month' => $start->daysInMonth,
            'total_days_with_record' => $rows->count(),
            'summary_by_status' => $summaryByStatus,
            'items' => $items,
        ]);
    }
}
