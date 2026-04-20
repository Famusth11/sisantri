<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory;

    protected static ?array $kitabNameMap = null;

    protected $table = 'absensi';
    
    protected $fillable = [
        'timestamp',
        'santri_id',
        'kegiatan',
        'status',
        'petugas_id',
        'nama_santri',
        'kelas',
        'golongan',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public static function resolveKitabName(?string $kitabIdOrName): string
    {
        $value = trim((string) $kitabIdOrName);

        if ($value === '') {
            return '';
        }

        return static::getKitabNameMap()[strtoupper($value)] ?? $value;
    }

    public static function flushKitabNameMap(): void
    {
        static::$kitabNameMap = null;
    }

    public static function formatKegiatanLabel(?string $kegiatan): string
    {
        $kegiatan = trim((string) $kegiatan);

        if ($kegiatan === '') {
            return 'Tidak diketahui';
        }

        $kitabNameMap = static::getKitabNameMap();
        $normalizedKegiatan = strtoupper($kegiatan);

        if (isset($kitabNameMap[$normalizedKegiatan])) {
            return $kitabNameMap[$normalizedKegiatan];
        }

        if (preg_match('/^(Ngaji)\s+(.+)$/iu', $kegiatan, $matches)) {
            $kitabKey = strtoupper(trim($matches[2]));
            $kitabName = $kitabNameMap[$kitabKey] ?? null;

            if ($kitabName !== null) {
                return trim($matches[1]) . ' ' . $kitabName;
            }
        }

        return $kegiatan;
    }

    public static function resolveAttendanceTime($timestamp, $createdAt = null): string
    {
        if (!$timestamp instanceof Carbon) {
            return '--:--';
        }

        if (
            $timestamp->format('H:i:s') === '00:00:00'
            && $createdAt instanceof Carbon
            && $createdAt->format('H:i:s') !== '00:00:00'
        ) {
            return $createdAt->format('H:i');
        }

        if ($timestamp->format('H:i:s') === '00:00:00') {
            return '--:--';
        }

        return $timestamp->format('H:i');
    }

    protected static function getKitabNameMap(): array
    {
        if (static::$kitabNameMap !== null) {
            return static::$kitabNameMap;
        }

        static::$kitabNameMap = KitabDiniyah::query()
            ->get(['id_kitab', 'nama_kitab'])
            ->mapWithKeys(function ($kitab) {
                $idKitab = strtoupper(trim((string) ($kitab->id_kitab ?? '')));
                $namaKitab = trim((string) ($kitab->nama_kitab ?? ''));

                if ($idKitab === '' || $namaKitab === '') {
                    return [];
                }

                return [$idKitab => $namaKitab];
            })
            ->all();

        return static::$kitabNameMap;
    }

    
    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id', 'id_santri');
    }

    


    public function scopeForMonth($query, $month)
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        return $query->whereBetween('timestamp', [$monthStart, $monthEnd]);
    }

    


    public function scopeKegiatan($query, $kegiatan)
    {
        return $query->where('kegiatan', 'like', '%' . $kegiatan . '%');
    }

    


    public function scopeStatus($query, $status)
    {
        return $query->where('status', strtoupper($status));
    }
}
