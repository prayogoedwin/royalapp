<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OrderEtollTransaction;
use App\Models\OrderExpense;
use App\Models\OrderPhoto;
use App\Models\OrderVehicleIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class UploadFolderController extends Controller
{
    public const MAX_BYTES = 5 * 1024 * 1024 * 1024;

    public const MAX_FILES = 5000;

    public function index(): View
    {
        $disk = Storage::disk('public');
        $years = collect($disk->directories('uploads'))
            ->map(fn (string $dir) => basename($dir))
            ->filter(fn (string $year) => preg_match('/^\d{4}$/', $year))
            ->sortDesc()
            ->values();

        $folders = [];
        $challenges = [];
        $totalFiles = 0;
        $totalBytes = 0;

        foreach ($years as $year) {
            $months = collect($disk->directories('uploads/'.$year))
                ->map(fn (string $dir) => basename($dir))
                ->filter(fn (string $month) => preg_match('/^\d{2}$/', $month))
                ->sortDesc()
                ->values();

            foreach ($months as $month) {
                $prefix = 'uploads/'.$year.'/'.$month;
                $allFiles = $disk->allFiles($prefix);
                $folderBytes = 0;

                foreach ($allFiles as $file) {
                    $folderBytes += (int) $disk->size($file);
                }

                $fileCount = count($allFiles);
                $totalFiles += $fileCount;
                $totalBytes += $folderBytes;

                $code = $this->confirmationCode();
                $challenges[$year.$month] = $code;
                $folders[] = [
                    'year' => $year,
                    'month' => $month,
                    'path' => $prefix,
                    'total_files' => $fileCount,
                    'total_bytes' => $folderBytes,
                    'total_size_label' => $this->formatBytes($folderBytes),
                    'confirmation_code' => $code,
                ];
            }
        }

        session(['upload_folder_confirm' => $challenges]);

        return view('upload-folders.index', [
            'folders' => $folders,
            'totalFiles' => $totalFiles,
            'totalBytes' => $totalBytes,
            'totalSizeLabel' => $this->formatBytes($totalBytes),
            'maxFiles' => self::MAX_FILES,
            'maxBytes' => self::MAX_BYTES,
            'maxSizeLabel' => $this->formatBytes(self::MAX_BYTES),
            'filesPercent' => $this->usagePercent($totalFiles, self::MAX_FILES),
            'bytesPercent' => $this->usagePercent($totalBytes, self::MAX_BYTES),
        ]);
    }

    public function download(string $year, string $month): BinaryFileResponse|RedirectResponse
    {
        if (! $this->validPeriod($year, $month)) {
            return back()->withErrors(['error' => 'Format tahun/bulan tidak valid.']);
        }

        $disk = Storage::disk('public');
        $dir = 'uploads/'.$year.'/'.$month;

        if (! $disk->directoryExists($dir)) {
            return back()->withErrors(['error' => 'Folder tidak ditemukan.']);
        }

        $files = $disk->allFiles($dir);

        if ($files === []) {
            return back()->withErrors(['error' => 'Folder kosong, tidak ada file untuk diunduh.']);
        }

        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipPath = $tmpDir.'/uploads-'.$year.'-'.$month.'-'.uniqid().'.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['error' => 'Gagal membuat file unduhan.']);
        }

        foreach ($files as $file) {
            $relative = ltrim(substr($file, strlen($dir)), '/');
            $zip->addFile($disk->path($file), $relative);
        }

        $zip->close();

        return response()
            ->download($zipPath, 'uploads-'.$year.'-'.$month.'.zip')
            ->deleteFileAfterSend(true);
    }

    public function destroy(Request $request, string $year, string $month): RedirectResponse
    {
        if (! $this->validPeriod($year, $month)) {
            return back()->withErrors(['error' => 'Format tahun/bulan tidak valid.']);
        }

        $given = strtoupper(trim((string) $request->input('confirmation_code')));
        $expected = session('upload_folder_confirm.'.$year.$month);

        if (! is_string($expected) || strlen($given) !== 4 || ! hash_equals($expected, $given)) {
            return back()
                ->with('confirm_folder', $year.'/'.$month)
                ->withErrors(['confirmation_code' => 'Kode konfirmasi tidak sesuai. Ketik 4 huruf yang ditampilkan.']);
        }

        session()->forget('upload_folder_confirm.'.$year.$month);

        $prefix = 'uploads/'.$year.'/'.$month.'/';
        $disk = Storage::disk('public');
        $dir = 'uploads/'.$year.'/'.$month;

        DB::beginTransaction();
        try {
            // Keep all rows, only clear file columns that point to this folder.
            OrderPhoto::where('path', 'like', $prefix.'%')->update(['path' => null]);
            OrderExpense::where('receipt_photo', 'like', $prefix.'%')->update(['receipt_photo' => null]);
            OrderEtollTransaction::where('receipt_photo', 'like', $prefix.'%')->update(['receipt_photo' => null]);
            OrderVehicleIssue::where('issue_photo', 'like', $prefix.'%')->update(['issue_photo' => null]);
            OrderVehicleIssue::where('repair_photo', 'like', $prefix.'%')->update(['repair_photo' => null]);
            Absensi::where('foto_masuk', 'like', $prefix.'%')->update(['foto_masuk' => null]);
            Absensi::where('foto_pulang', 'like', $prefix.'%')->update(['foto_pulang' => null]);

            // Remove the physical folder
            $disk->deleteDirectory($dir);

            DB::commit();

            return redirect()->route('upload-folders.index')->with('status', 'Folder upload '.$year.'/'.$month.' berhasil dihapus. Data DB tetap disimpan, hanya kolom file yang dikosongkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menghapus folder: '.$e->getMessage()]);
        }
    }

    private function validPeriod(string $year, string $month): bool
    {
        return preg_match('/^\d{4}$/', $year) === 1 && preg_match('/^\d{2}$/', $month) === 1;
    }

    private function confirmationCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < 4; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    private function usagePercent(int $used, int $max): float
    {
        if ($max <= 0) {
            return 0.0;
        }

        return min(100, round(($used / $max) * 100, 1));
    }

    private function formatBytes(int $bytes): string
    {
        $units = [
            ['GB', 1024 ** 3, 2],
            ['MB', 1024 ** 2, 1],
            ['KB', 1024, 1],
            ['B', 1, 0],
        ];

        foreach ($units as [$unit, $size, $precision]) {
            if ($bytes >= $size || $unit === 'B') {
                $value = $precision === 0
                    ? (string) $bytes
                    : number_format($bytes / $size, $precision, ',', '.');

                return $value.' '.$unit;
            }
        }

        return '0 B';
    }
}
