<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\OrderEtollTransaction;
use App\Models\OrderExpense;
use App\Models\OrderPhoto;
use App\Models\OrderVehicleIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

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
        foreach ($years as $year) {
            $months = collect($disk->directories('uploads/' . $year))
                ->map(fn (string $dir) => basename($dir))
                ->filter(fn (string $month) => preg_match('/^\d{2}$/', $month))
                ->sortDesc()
                ->values();

            foreach ($months as $month) {
                $prefix = 'uploads/' . $year . '/' . $month;
                $allFiles = $disk->allFiles($prefix);
                $folders[] = [
                    'year' => $year,
                    'month' => $month,
                    'path' => $prefix,
                    'total_files' => count($allFiles),
                ];
            }
        }

        return view('upload-folders.index', compact('folders'));
    }

    public function destroy(string $year, string $month): RedirectResponse
    {
        if (!preg_match('/^\d{4}$/', $year) || !preg_match('/^\d{2}$/', $month)) {
            return back()->withErrors(['error' => 'Format tahun/bulan tidak valid.']);
        }

        $prefix = 'uploads/' . $year . '/' . $month . '/';
        $disk = Storage::disk('public');
        $dir = 'uploads/' . $year . '/' . $month;

        DB::beginTransaction();
        try {
            // Keep all rows, only clear file columns that point to this folder.
            OrderPhoto::where('path', 'like', $prefix . '%')->update(['path' => null]);
            OrderExpense::where('receipt_photo', 'like', $prefix . '%')->update(['receipt_photo' => null]);
            OrderEtollTransaction::where('receipt_photo', 'like', $prefix . '%')->update(['receipt_photo' => null]);
            OrderVehicleIssue::where('issue_photo', 'like', $prefix . '%')->update(['issue_photo' => null]);
            OrderVehicleIssue::where('repair_photo', 'like', $prefix . '%')->update(['repair_photo' => null]);
            Absensi::where('foto_masuk', 'like', $prefix . '%')->update(['foto_masuk' => null]);
            Absensi::where('foto_pulang', 'like', $prefix . '%')->update(['foto_pulang' => null]);

            // Remove the physical folder
            $disk->deleteDirectory($dir);

            DB::commit();
            return redirect()->route('upload-folders.index')->with('status', 'Folder upload ' . $year . '/' . $month . ' berhasil dihapus. Data DB tetap disimpan, hanya kolom file yang dikosongkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus folder: ' . $e->getMessage()]);
        }
    }
}

