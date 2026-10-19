<?php

namespace App\Http\Controllers;

use App\Exports\AbsensiTodayAllExport;
use App\Models\Absensi;
use App\Models\Employee;
use App\Support\UploadPath;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbsensiController extends Controller
{
    public function showMyPresensi(Request $request): View
    {
        $employee = $request->user()?->employee;
        if (! $employee) {
            abort(403, 'Employee not found for this user.');
        }

        $now = Carbon::now();
        $month = $request->query('month', $now->format('Y-m')); // YYYY-MM

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = $now->startOfMonth();
            $month = $monthDate->format('Y-m');
        }

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $absensis = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn (Absensi $a) => $a->tanggal->format('Y-m-d'));

        $daysInMonth = $monthDate->daysInMonth;
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $monthDate->copy()->day($d);
            $days[] = [
                'date' => $date,
                'absensi' => $absensis->get($date->format('Y-m-d')),
            ];
        }

        $isAdmin = $request->user()?->hasPermission('edit-absensi-status') ?? false;

        return view('absensi.show', [
            'employee' => $employee,
            'days' => $days,
            'month' => $month,
            'isAdmin' => $isAdmin,
            'showPdf' => true,
            'monthFormAction' => route('presensi.my'),
        ]);
    }

    public function showEmployeePresensi(Employee $employee, Request $request): View
    {
        $now = Carbon::now();
        $month = $request->query('month', $now->format('Y-m')); // YYYY-MM

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = $now->startOfMonth();
            $month = $monthDate->format('Y-m');
        }

        // Authorization: employee can only view self; admin can view any
        $canViewAny = $request->user()?->hasPermission('edit-absensi-status') ?? false;
        $canViewOwn = $request->user()?->employee && $request->user()->employee->id === $employee->id;

        if (! ($canViewAny || $canViewOwn)) {
            abort(403, 'Unauthorized action.');
        }

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $absensis = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn (Absensi $a) => $a->tanggal->format('Y-m-d'));

        $daysInMonth = $monthDate->daysInMonth;
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $monthDate->copy()->day($d);
            $days[] = [
                'date' => $date,
                'absensi' => $absensis->get($date->format('Y-m-d')),
            ];
        }

        return view('absensi.show', [
            'employee' => $employee,
            'days' => $days,
            'month' => $month,
            'isAdmin' => $canViewAny,
            'showPdf' => false,
            'monthFormAction' => route('employees.presensi', $employee),
        ]);
    }

    public function exportMyPresensiPdf(Request $request)
    {
        $employee = $request->user()?->employee;
        if (! $employee) {
            abort(403, 'Employee not found for this user.');
        }

        $now = Carbon::now();
        $month = $request->query('month', $now->format('Y-m'));

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = $now->startOfMonth();
            $month = $monthDate->format('Y-m');
        }

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $absensis = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn (Absensi $a) => $a->tanggal->format('Y-m-d'));

        $daysInMonth = $monthDate->daysInMonth;
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $monthDate->copy()->day($d);
            $days[] = [
                'date' => $date,
                'absensi' => $absensis->get($date->format('Y-m-d')),
            ];
        }

        $statusOptions = Absensi::STATUS_OPTIONS;

        $html = view('absensi.pdf.monthly', [
            'employee' => $employee,
            'month' => $month,
            'days' => $days,
            'statusOptions' => $statusOptions,
        ])->render();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $fileName = 'presensi-'.$employee->full_name.'-'.$month.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function exportEmployeePresensiPdf(Employee $employee, Request $request)
    {
        $canViewAny = $request->user()?->hasPermission('edit-absensi-status') ?? false;
        $canViewOwn = $request->user()?->employee && $request->user()->employee->id === $employee->id;

        if (! ($canViewAny || $canViewOwn)) {
            abort(403, 'Unauthorized action.');
        }

        $now = Carbon::now();
        $month = $request->query('month', $now->format('Y-m'));

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $monthDate = $now->startOfMonth();
            $month = $monthDate->format('Y-m');
        }

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $absensis = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn (Absensi $a) => $a->tanggal->format('Y-m-d'));

        $daysInMonth = $monthDate->daysInMonth;
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = $monthDate->copy()->day($d);
            $days[] = [
                'date' => $date,
                'absensi' => $absensis->get($date->format('Y-m-d')),
            ];
        }

        $statusOptions = Absensi::STATUS_OPTIONS;

        $html = view('absensi.pdf.monthly', [
            'employee' => $employee,
            'month' => $month,
            'days' => $days,
            'statusOptions' => $statusOptions,
        ])->render();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $fileName = 'presensi-'.$employee->full_name.'-'.$month.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function showAllEmployeesToday(Request $request): View
    {
        // Admin screen to mark attendance for all employees (filterable by date).
        // Permission gating is done via route middleware.

        $tanggal = $this->resolveAbsensiAllTanggal($request);
        $payload = $this->absensiAllEmployeesPayload($tanggal);

        return view('absensi.today_all', [
            ...$payload,
            'isAdmin' => $request->user()?->hasPermission('edit-absensi-status') ?? false,
            'canMasuk' => $request->user()?->hasPermission('create-absensi-masuk') ?? false,
            'canPulang' => $request->user()?->hasPermission('create-absensi-pulang') ?? false,
            'editMode' => $request->boolean('edit'),
        ]);
    }

    public function exportAllEmployeesTodayPdf(Request $request): Response
    {
        $tanggal = $this->resolveAbsensiAllTanggal($request);
        $payload = $this->absensiAllEmployeesPayload($tanggal);
        $rows = $this->absensiAllEmployeeRows($payload);

        $html = view('absensi.pdf.today_all', [
            'tanggal' => $tanggal,
            'rows' => $rows,
            'statusOptions' => $payload['statusOptions'],
        ])->render();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        $fileName = 'absensi-semua-pegawai-'.$tanggal.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportAllEmployeesTodayExcel(Request $request): BinaryFileResponse
    {
        $tanggal = $this->resolveAbsensiAllTanggal($request);
        $payload = $this->absensiAllEmployeesPayload($tanggal);
        $rows = $this->absensiAllEmployeeRows($payload);

        return Excel::download(
            new AbsensiTodayAllExport($tanggal, $rows, $payload['statusOptions']),
            'absensi-semua-pegawai-'.$tanggal.'.xlsx'
        );
    }

    private function resolveAbsensiAllTanggal(Request $request): string
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
        ]);

        return isset($validated['tanggal'])
            ? Carbon::parse($validated['tanggal'])->toDateString()
            : Carbon::today()->toDateString();
    }

    /**
     * @return array{
     *     tanggal: string,
     *     employees: Collection<int, Employee>,
     *     pendingByEmployee: array<int, Absensi>,
     *     todayAbsensis: Collection<int, Absensi>,
     *     statusOptions: array<string, string>,
     *     statusColors: array<string, string>
     * }
     */
    private function absensiAllEmployeesPayload(string $tanggal): array
    {
        $employees = Employee::query()
            ->with(['pool'])
            ->orderBy('full_name')
            ->get();

        $employeeIds = $employees->pluck('id');

        $todayAbsensis = Absensi::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('employee_id');

        // Pending pulang: tampilan kolom hanya pakai record tanggal filter.
        // Lookback 3 hari khusus form edit hari ini (shift lintas hari).
        $isViewingToday = $tanggal === Carbon::today()->toDateString();
        $rangeStart = $isViewingToday
            ? Carbon::parse($tanggal)->subDays(3)->toDateString()
            : $tanggal;

        $pendingAbsensis = Absensi::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('tanggal', [$rangeStart, $tanggal])
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $pendingByEmployee = [];
        foreach ($pendingAbsensis as $absensi) {
            if (! isset($pendingByEmployee[$absensi->employee_id])) {
                $pendingByEmployee[$absensi->employee_id] = $absensi;
            }
        }

        return [
            'tanggal' => $tanggal,
            'employees' => $employees,
            'pendingByEmployee' => $pendingByEmployee,
            'todayAbsensis' => $todayAbsensis,
            'statusOptions' => Absensi::STATUS_OPTIONS,
            'statusColors' => Absensi::STATUS_COLORS,
        ];
    }

    /**
     * @param  array{
     *     employees: Collection<int, Employee>,
     *     pendingByEmployee: array<int, Absensi>,
     *     todayAbsensis: Collection<int, Absensi>
     * }  $payload
     * @return list<array{pegawai: string, pool: string, jam_masuk: string, jam_pulang: string, status: string|null}>
     */
    private function absensiAllEmployeeRows(array $payload): array
    {
        $rows = [];

        foreach ($payload['employees'] as $employee) {
            $todayAbsensi = $payload['todayAbsensis']->get($employee->id);

            $rows[] = [
                'pegawai' => $employee->full_name,
                'pool' => $employee->pool?->pool_name ?? '-',
                'jam_masuk' => $todayAbsensi?->jam_masuk?->format('H:i') ?? '-',
                'jam_pulang' => $todayAbsensi?->jam_pulang?->format('H:i') ?? '-',
                'status' => $todayAbsensi?->status,
            ];
        }

        return $rows;
    }

    public function storeMasuk(Request $request): RedirectResponse
    {
        $targetEmployeeId = $request->input('employee_id');

        if ($targetEmployeeId) {
            $employee = Employee::findOrFail($targetEmployeeId);

            $myEmployee = $request->user()?->employee;
            $isSelf = $myEmployee && (int) $myEmployee->id === (int) $employee->id;
            $canActAsAdmin = $request->user()?->hasPermission('edit-absensi-status') ?? false;

            if (! $isSelf && ! $canActAsAdmin) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            $employee = $request->user()?->employee;
            if (! $employee) {
                abort(403, 'Employee not found for this user.');
            }
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'keterangan' => ['nullable', 'string'],
            'foto_masuk' => ['nullable', 'image', 'max:2048'],
            'device_info' => ['nullable', 'array'],
        ]);

        $jamMasukInput = $validated['jam_masuk'] ?? null;
        $jamMasuk = $jamMasukInput
            ? Carbon::createFromFormat('H:i', $jamMasukInput)->format('H:i:s')
            : Carbon::now()->format('H:i:s');

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
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();

        if (! $absensi) {
            $absensi = Absensi::create([
                'employee_id' => $employee->id,
                'pool_id' => $employee->pool_id,
                'tanggal' => $validated['tanggal'],
                'jam_masuk' => $jamMasuk,
                'foto_masuk' => null,
                'status' => Absensi::STATUS_HADIR,
                'is_overnight' => false,
                'device_info' => $validated['device_info'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        } else {
            // For non-admin: prevent masuk input when status already marked non-Hadir.
            // For admin (edit-absensi-status): allow jam input regardless of current status.
            if (! $canActAsAdmin && $absensi->status && in_array($absensi->status, $lockedStatuses, true)) {
                abort(422, 'Jam masuk tidak bisa diinput karena status sudah terkunci.');
            }

            if ($absensi->jam_masuk !== null && ! $canActAsAdmin) {
                // Non-admin tidak boleh overwrite jam_masuk.
                abort(422, 'Jam masuk sudah diisi.');
            }

            $absensi->jam_masuk = $jamMasuk;
            if (! $canActAsAdmin) {
                $absensi->status = Absensi::STATUS_HADIR;
            } else {
                // Admin: jangan paksa status berubah kalau sudah di-set lewat status CRUD.
                $absensi->status = $absensi->status ?? Absensi::STATUS_HADIR;
            }
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

        if ($request->input('return_to') === 'absensi.today.all') {
            return redirect()
                ->route('absensi.today.all', [
                    'tanggal' => $validated['tanggal'],
                    'edit' => 1,
                ])
                ->with('status', 'Absen masuk berhasil.');
        }

        return redirect()
            ->route('employees.presensi', $employee)
            ->with('status', 'Absen masuk berhasil.');
    }

    public function storePulang(Request $request): RedirectResponse
    {
        $targetEmployeeId = $request->input('employee_id');

        $canActAsAdmin = $request->user()?->hasPermission('edit-absensi-status') ?? false;

        if ($targetEmployeeId) {
            $employee = Employee::findOrFail($targetEmployeeId);

            $myEmployee = $request->user()?->employee;
            $isSelf = $myEmployee && (int) $myEmployee->id === (int) $employee->id;

            if (! $isSelf && ! $canActAsAdmin) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            $employee = $request->user()?->employee;
            if (! $employee) {
                abort(403, 'Employee not found for this user.');
            }
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'keterangan' => ['nullable', 'string'],
            'foto_pulang' => ['nullable', 'image', 'max:2048'],
            'device_info' => ['nullable', 'array'],
        ]);

        $jamPulangInput = $validated['jam_pulang'] ?? null;
        $jamPulang = $jamPulangInput
            ? Carbon::createFromFormat('H:i', $jamPulangInput)->format('H:i:s')
            : Carbon::now()->format('H:i:s');

        $lockedStatuses = [
            Absensi::STATUS_ALPHA,
            Absensi::STATUS_IZIN,
            Absensi::STATUS_SAKIT,
            Absensi::STATUS_CUTI,
            Absensi::STATUS_LIBUR,
            Absensi::STATUS_TIDAK_ABSEN_PULANG,
        ];

        // Pulang harus selalu meng-update record yang statusnya masih pending checkout:
        // jam_masuk terisi, jam_pulang masih null.
        $tanggalInput = $validated['tanggal'];

        $absensi = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->whereDate('tanggal', $tanggalInput)
            ->first();

        if (! $absensi) {
            // fallback untuk lintas hari/shift (mulai kemarin, pulang hari ini)
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

        if (! $absensi) {
            abort(422, 'Absensi pulang tidak ditemukan atau sudah diisi.');
        }

        if ($absensi->status && in_array($absensi->status, $lockedStatuses, true)) {
            if (! $canActAsAdmin) {
                abort(422, 'Jam pulang tidak bisa diinput karena status sudah terkunci.');
            }
        }

        $jamMasuk = $absensi->jam_masuk;
        // Heuristic for overnight (jam pulang <= jam masuk means likely next-day)
        $isOvernight = $jamMasuk && $jamPulang ? $jamPulang <= $jamMasuk : false;

        $absensi->jam_pulang = $jamPulang;
        $absensi->is_overnight = $absensi->is_overnight ?? $isOvernight;
        if (! $canActAsAdmin) {
            $absensi->status = Absensi::STATUS_HADIR;
        } else {
            // Admin: jangan paksa status berubah kalau sudah di-set lewat status CRUD.
            $absensi->status = $absensi->status ?? Absensi::STATUS_HADIR;
        }
        $absensi->pool_id = $employee->pool_id;
        $absensi->device_info = $validated['device_info'] ?? $absensi->device_info;
        $absensi->keterangan = $validated['keterangan'] ?? $absensi->keterangan;
        $absensi->save();

        after_absensi_save:
        if ($request->hasFile('foto_pulang')) {
            $path = $request->file('foto_pulang')->store(UploadPath::dir('absensi'), 'public');
            $absensi->foto_pulang = $path;
            $absensi->save();
        }

        if ($request->input('return_to') === 'absensi.today.all') {
            return redirect()
                ->route('absensi.today.all', [
                    'tanggal' => $validated['tanggal'],
                    'edit' => 1,
                ])
                ->with('status', 'Absen pulang berhasil.');
        }

        return redirect()
            ->route('employees.presensi', $employee)
            ->with('status', 'Absen pulang berhasil.');
    }

    public function adminUpdateStatus(Request $request, Absensi $absensi): RedirectResponse
    {
        if (! $request->user()->hasPermission('edit-absensi-status')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $status = $validated['status'] ?? null;
        if ($status === '') {
            $status = null;
        }

        $absensi->status = $status;
        if ($status && array_key_exists($status, Absensi::STATUS_OPTIONS)) {
            // ok
        }
        $absensi->keterangan = $validated['keterangan'] ?? $absensi->keterangan;
        $absensi->approved_by = $request->user()->id;
        $absensi->approved_at = Carbon::now();
        $absensi->save();

        return redirect()
            ->route('employees.presensi', $absensi->employee_id)
            ->with('status', 'Absensi berhasil diperbarui.');
    }

    public function adminUpsertStatusForDate(Request $request, Employee $employee): RedirectResponse
    {
        if (! $request->user()->hasPermission('edit-absensi-status')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $status = $validated['status'] ?? null;
        if ($status === '') {
            $status = null;
        }

        // Create if does not exist yet (used for "Tidak Absen Masuk/Pulang")
        $absensi = Absensi::query()
            ->where('employee_id', $employee->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();

        if (! $absensi) {
            $absensi = Absensi::create([
                'employee_id' => $employee->id,
                'pool_id' => $employee->pool_id,
                'tanggal' => $validated['tanggal'],
                'status' => $status,
                'keterangan' => $validated['keterangan'] ?? null,
                'approved_by' => $request->user()->id,
                'approved_at' => Carbon::now(),
                'is_overnight' => false,
            ]);
        } else {
            $absensi->status = $status;
            $absensi->keterangan = $validated['keterangan'] ?? $absensi->keterangan;
            $absensi->approved_by = $request->user()->id;
            $absensi->approved_at = Carbon::now();
            $absensi->save();
        }

        return redirect()
            ->route('employees.presensi', $employee)
            ->with('status', 'Status absensi berhasil diperbarui.');
    }
}
