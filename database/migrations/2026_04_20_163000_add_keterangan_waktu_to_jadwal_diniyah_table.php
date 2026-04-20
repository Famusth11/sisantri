<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_diniyah', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_diniyah', 'keterangan_waktu')) {
                $table->string('keterangan_waktu')->nullable()->after('pengampu');
            }
        });

        $this->repairExistingSchedules();
    }

    public function down(): void
    {
        Schema::table('jadwal_diniyah', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_diniyah', 'keterangan_waktu')) {
                $table->dropColumn('keterangan_waktu');
            }
        });
    }

    protected function repairExistingSchedules(): void
    {
        if (!Schema::hasTable('jadwal_diniyah') || !Schema::hasTable('kitab_diniyah')) {
            return;
        }

        $metadataByKitab = DB::table('kitab_diniyah')
            ->select('id_kitab', 'pengampu_golongan')
            ->get()
            ->keyBy('id_kitab');

        DB::table('jadwal_diniyah')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($metadataByKitab) {
                foreach ($rows as $row) {
                    $rawMetadata = trim((string) ($metadataByKitab[$row->kitab_id]->pengampu_golongan ?? ''));
                    if ($rawMetadata === '') {
                        continue;
                    }

                    $parsed = $this->parseLegacyMetadata($rawMetadata);
                    $updates = [];

                    if (($row->pengampu === null || $this->looksLikeTimeLabel((string) $row->pengampu)) && !empty($parsed['pengampu'])) {
                        $updates['pengampu'] = $parsed['pengampu'];
                    }

                    if (($row->keterangan_waktu === null || trim((string) $row->keterangan_waktu) === '') && !empty($parsed['keterangan_waktu'])) {
                        $updates['keterangan_waktu'] = $parsed['keterangan_waktu'];
                    }

                    if (($row->golongan === null || trim((string) $row->golongan) === '') && !empty($parsed['golongan'])) {
                        $updates['golongan'] = $parsed['golongan'];
                    }

                    if (!empty($updates)) {
                        $updates['updated_at'] = now('Asia/Jakarta');
                        DB::table('jadwal_diniyah')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    protected function parseLegacyMetadata(string $raw): array
    {
        $result = [
            'golongan' => null,
            'pengampu' => null,
            'keterangan_waktu' => null,
        ];

        $parts = array_values(array_filter(array_map('trim', explode('|', $raw))));
        $scheduleDetail = null;

        foreach ($parts as $part) {
            $upperPart = strtoupper($part);

            if (in_array($upperPart, ['BILINGUAL', 'TAHFIDZ', 'PUTRA', 'PUTRI', 'ALL'], true)) {
                $result['golongan'] = $upperPart;
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
