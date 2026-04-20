<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
                    if (!$this->looksLikeTimeLabel((string) ($row->pengampu ?? ''))) {
                        continue;
                    }

                    $rawMetadata = trim((string) ($metadataByKitab[$row->kitab_id]->pengampu_golongan ?? ''));
                    if ($rawMetadata === '') {
                        continue;
                    }

                    $teacher = $this->extractTeacher($rawMetadata);
                    if ($teacher === null) {
                        continue;
                    }

                    DB::table('jadwal_diniyah')
                        ->where('id', $row->id)
                        ->update([
                            'pengampu' => $teacher,
                            'updated_at' => now('Asia/Jakarta'),
                        ]);
                }
            });
    }

    public function down(): void
    {
    }

    protected function extractTeacher(string $raw): ?string
    {
        $parts = array_values(array_filter(array_map('trim', explode('|', $raw))));

        foreach ($parts as $part) {
            if ($this->looksLikeTeacher($part)) {
                return $part;
            }
        }

        return null;
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
