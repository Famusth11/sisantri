<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_diniyah', function (Blueprint $table) {
            $table->id();
            $table->string('kitab_id')->nullable();
            $table->string('nama_kegiatan');
            $table->string('tahun_ajaran', 20);
            $table->string('semester', 20);
            $table->string('kelas')->nullable();
            $table->string('golongan')->nullable();
            $table->string('pengampu')->nullable();
            $table->string('keterangan_waktu')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['tahun_ajaran', 'semester'], 'jadwal_diniyah_tahun_semester_index');
            $table->index(['is_active', 'tahun_ajaran', 'semester'], 'jadwal_diniyah_active_period_index');
            $table->index('kitab_id', 'jadwal_diniyah_kitab_index');
        });

        $this->backfillLegacySchedules();
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_diniyah');
    }

    protected function backfillLegacySchedules(): void
    {
        if (!Schema::hasTable('kitab_diniyah')) {
            return;
        }

        if (DB::table('jadwal_diniyah')->exists()) {
            return;
        }

        $legacyKitab = DB::table('kitab_diniyah')
            ->select('id_kitab', 'nama_kitab', 'kelas_kitab', 'pengampu_golongan')
            ->orderBy('nama_kitab')
            ->get();

        if ($legacyKitab->isEmpty()) {
            return;
        }

        $now = Carbon::now('Asia/Jakarta');
        $tahunAjaran = $this->guessAcademicYear($now);
        $semester = $now->month >= 7 ? 'Ganjil' : 'Genap';

        $rows = $legacyKitab->map(function ($kitab) use ($tahunAjaran, $semester, $now) {
            $metadata = $this->parseLegacyMetadata((string) ($kitab->pengampu_golongan ?? ''));

            return [
                'kitab_id' => $kitab->id_kitab,
                'nama_kegiatan' => trim((string) ($kitab->nama_kitab ?? $kitab->id_kitab)),
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'kelas' => $this->normalizeLegacyValue($kitab->kelas_kitab),
                'golongan' => $metadata['golongan'],
                'pengampu' => $metadata['pengampu'],
                'keterangan_waktu' => $metadata['keterangan_waktu'],
                'jam_mulai' => $metadata['jam_mulai'],
                'jam_selesai' => $metadata['jam_selesai'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('jadwal_diniyah')->insert($chunk);
        }
    }

    protected function guessAcademicYear(Carbon $now): string
    {
        $startYear = $now->month >= 7 ? $now->year : $now->year - 1;

        return $startYear . '/' . ($startYear + 1);
    }

    protected function normalizeLegacyValue($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function parseLegacyMetadata(string $raw): array
    {
        $raw = trim($raw);

        $result = [
            'golongan' => null,
            'pengampu' => null,
            'keterangan_waktu' => null,
            'jam_mulai' => null,
            'jam_selesai' => null,
        ];

        if ($raw === '') {
            return $result;
        }

        $parts = array_values(array_filter(array_map('trim', explode('|', $raw))));
        $scheduleDetail = null;

        foreach ($parts as $part) {
            $upperPart = strtoupper($part);

            if (in_array($upperPart, ['BILINGUAL', 'TAHFIDZ', 'PUTRA', 'PUTRI', 'ALL'], true)) {
                $result['golongan'] = $upperPart;
                continue;
            }

            if (preg_match('/(\d{1,2}:\d{2})(?:\s*[-s.d\/]+\s*(\d{1,2}:\d{2}))?/u', $part, $matches)) {
                $result['jam_mulai'] = $matches[1] . ':00';
                if (!empty($matches[2])) {
                    $result['jam_selesai'] = $matches[2] . ':00';
                }
                continue;
            }

            if ($this->looksLikeTeacher($part)) {
                $result['pengampu'] = $part;
                continue;
            }

            if ($this->looksLikeTimeLabel($part) && $result['keterangan_waktu'] === null) {
                $result['keterangan_waktu'] = $part;
                continue;
            }

            if ($scheduleDetail === null) {
                $scheduleDetail = $part;
            }
        }

        if ($result['keterangan_waktu'] === null && $scheduleDetail !== null) {
            $result['keterangan_waktu'] = $scheduleDetail;
        }

        if ($result['pengampu'] === null) {
            foreach ($parts as $part) {
                if (!$this->looksLikeTimeLabel($part)) {
                    $result['pengampu'] = $part;
                    break;
                }
            }
        }

        return $result;
    }

    protected function looksLikeTeacher(string $part): bool
    {
        return preg_match('/(^|\s)(K\.|KH\.?|UST|USTH|USTZ|USTADZ|PEMBINA|NYAI|BUNDA|ABI|UMMI|MUSYRIF)(\s|$|\.|,)/i', $part) === 1;
    }

    protected function looksLikeTimeLabel(string $part): bool
    {
        return preg_match('/\b(DINIYAH|TAHFIDZ|NGAJI|SORE|MALAM|PAGI|SIANG|SUBUH|ASAR|MAGHRIB|ISYA|SENIN|SELASA|RABU|KAMIS|JUMAT|SABTU|MINGGU)\b/i', $part) === 1;
    }
};
