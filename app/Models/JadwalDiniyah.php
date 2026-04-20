<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalDiniyah extends Model
{
    use HasFactory;

    protected $table = 'jadwal_diniyah';

    protected $fillable = [
        'kitab_id',
        'nama_kegiatan',
        'tahun_ajaran',
        'semester',
        'kelas',
        'golongan',
        'pengampu',
        'keterangan_waktu',
        'jam_mulai',
        'jam_selesai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'jam_mulai' => 'datetime:H:i:s',
        'jam_selesai' => 'datetime:H:i:s',
    ];

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(KitabDiniyah::class, 'kitab_id', 'id_kitab');
    }

    public function scopeForPeriod($query, string $tahunAjaran, string $semester)
    {
        return $query
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayLabelAttribute(): string
    {
        $parts = [trim((string) $this->nama_kegiatan)];

        $meta = array_filter([
            $this->kelas ? 'Kelas ' . $this->kelas : null,
            $this->golongan,
            $this->pengampu ? 'Pengampu: ' . $this->pengampu : null,
            $this->formatted_jam,
        ]);

        if (!empty($meta)) {
            $parts[] = '(' . implode(' | ', $meta) . ')';
        }

        return implode(' ', $parts);
    }

    public function getFormattedJamAttribute(): ?string
    {
        $mulai = $this->jam_mulai instanceof Carbon ? $this->jam_mulai->format('H:i') : null;
        $selesai = $this->jam_selesai instanceof Carbon ? $this->jam_selesai->format('H:i') : null;
        $keteranganWaktu = trim((string) ($this->keterangan_waktu ?? ''));

        if ($mulai && $selesai && $keteranganWaktu !== '') {
            return $mulai . ' - ' . $selesai . ' · ' . $keteranganWaktu;
        }

        if ($mulai && $selesai) {
            return $mulai . ' - ' . $selesai;
        }

        if ($mulai || $selesai) {
            return $mulai ?: $selesai;
        }

        return $keteranganWaktu !== '' ? $keteranganWaktu : null;
    }

    public static function currentAcademicYear(?Carbon $date = null): string
    {
        $date ??= Carbon::now('Asia/Jakarta');
        $startYear = $date->month >= 7 ? $date->year : $date->year - 1;

        return $startYear . '/' . ($startYear + 1);
    }

    public static function currentSemester(?Carbon $date = null): string
    {
        $date ??= Carbon::now('Asia/Jakarta');

        return $date->month >= 7 ? 'Ganjil' : 'Genap';
    }

    public static function previousAcademicYear(string $tahunAjaran): ?string
    {
        if (!preg_match('/^(\d{4})\/(\d{4})$/', trim($tahunAjaran), $matches)) {
            return null;
        }

        $startYear = (int) $matches[1] - 1;
        $endYear = (int) $matches[2] - 1;

        return $startYear . '/' . $endYear;
    }
}
