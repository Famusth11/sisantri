<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitabDiniyah extends Model
{
    use HasFactory;

    protected $table = 'kitab_diniyah';
    
    protected $fillable = [
        'id_kitab',
        'kelas_kitab',
        'pengampu_golongan',
        'nama_kitab',
    ];

    protected $primaryKey = 'id_kitab';
    public $incrementing = false;
    protected $keyType = 'string';

    public function getMasterLabelAttribute(): string
    {
        $segments = [trim((string) ($this->nama_kitab ?? $this->id_kitab))];

        $kelas = $this->formatted_kelas_kitab;
        if ($kelas !== null) {
            $segments[] = $kelas;
        }

        $waktu = $this->waktu_ringkas;
        if ($waktu !== null) {
            $segments[] = $waktu;
        }

        return implode(' - ', array_filter($segments));
    }

    public function getFormattedKelasKitabAttribute(): ?string
    {
        $kelas = trim((string) ($this->kelas_kitab ?? ''));

        if ($kelas === '') {
            return null;
        }

        if (preg_match('/\bkelas\s*(\d+)\b/i', $kelas, $matches)) {
            return 'Kelas ' . $matches[1];
        }

        if (preg_match('/^\d+$/', $kelas)) {
            return 'Kelas ' . $kelas;
        }

        return $kelas;
    }

    public function getWaktuRingkasAttribute(): ?string
    {
        $raw = trim((string) ($this->pengampu_golongan ?? ''));
        if ($raw === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode('|', $raw))));

        foreach ($parts as $part) {
            if (preg_match('/\b(DINIYAH|TAHFIDZ|NGAJI|SORE|MALAM|PAGI|SIANG|SUBUH|ASAR|MAGHRIB|ISYA)\b/i', $part)) {
                return $part;
            }
        }

        return null;
    }
}
