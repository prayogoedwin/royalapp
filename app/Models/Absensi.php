<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absensi extends Model
{
    use SoftDeletes;

    protected $table = 'absensis';

    protected $fillable = [
        'employee_id',
        'pool_id',
        'tanggal',
        'jadwal_jam_masuk',
        'jadwal_jam_pulang',
        'jam_masuk',
        'jam_pulang',
        'status',
        'is_overnight',
        'telat_menit',
        'pulang_awal_menit',
        'lat_masuk',
        'lng_masuk',
        'jarak_lokasi_masuk',
        'lat_pulang',
        'lng_pulang',
        'jarak_lokasi_pulang',
        'foto_masuk',
        'foto_pulang',
        'device_info',
        'keterangan',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jadwal_jam_masuk' => 'datetime:H:i',
        'jadwal_jam_pulang' => 'datetime:H:i',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'lat_masuk' => 'decimal:8',
        'lng_masuk' => 'decimal:8',
        'lat_pulang' => 'decimal:8',
        'lng_pulang' => 'decimal:8',
        'jarak_lokasi_masuk' => 'decimal:2',
        'jarak_lokasi_pulang' => 'decimal:2',
        'device_info' => 'array',
        'approved_at' => 'datetime',
        'is_overnight' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_HADIR = 'hadir';
    public const STATUS_ALPHA = 'alpha';
    public const STATUS_IZIN = 'izin';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_CUTI = 'cuti';
    public const STATUS_TERLAMBAT = 'terlambat';
    public const STATUS_LIBUR = 'libur';
    public const STATUS_TIDAK_ABSEN_MASUK = 'tidak_absen_masuk';
    public const STATUS_TIDAK_ABSEN_PULANG = 'tidak_absen_pulang';
    public const STATUS_PULANG_CEPAT = 'pulang_cepat';
    public const STATUS_LEMBUR = 'lembur';

    public const STATUS_OPTIONS = [
        self::STATUS_HADIR => 'Hadir',
        self::STATUS_ALPHA => 'Alpha',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_SAKIT => 'Sakit',
        self::STATUS_CUTI => 'Cuti',
        self::STATUS_TERLAMBAT => 'Terlambat',
        self::STATUS_LIBUR => 'Libur',
        self::STATUS_TIDAK_ABSEN_MASUK => 'Tidak Absen Masuk',
        self::STATUS_TIDAK_ABSEN_PULANG => 'Tidak Absen Pulang',
        self::STATUS_PULANG_CEPAT => 'Pulang Cepat',
        self::STATUS_LEMBUR => 'Lembur',
    ];

    public const STATUS_COLORS = [
        self::STATUS_HADIR => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        self::STATUS_ALPHA => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        self::STATUS_IZIN => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        self::STATUS_SAKIT => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        self::STATUS_CUTI => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        self::STATUS_TERLAMBAT => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        self::STATUS_LIBUR => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        self::STATUS_TIDAK_ABSEN_MASUK => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        self::STATUS_TIDAK_ABSEN_PULANG => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        self::STATUS_PULANG_CEPAT => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        self::STATUS_LEMBUR => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

