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
        foreach ($years as $year) {
            $months = collect($disk->directories('uploads/'.$year))
                ->map(fn (string $dir) => basename($dir))
                ->filter(fn (string $month) => preg_match('/^\d{2}$/', $month))
                ->sortDesc()
                ->values();

            foreach ($months as $month) {
                $prefix = 'uploads/'.$year.'/'.$month;
                $allFiles = $disk->allFiles($prefix);
                $code = $this->confirmationCode();
                $challenges[$year.$month] = $code;
                $folders[] = [
                    'year' => $year,
                    'month' => $month,
                    'path' => $prefix,
                    'total_files' => count($allFiles),
                    'confirmation_code' => $code,
                ];
            }
        }

        session(['upload_folder_confirm' => $challenges]);

        return view('upload-folders.index', compact('folders'));
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
}
