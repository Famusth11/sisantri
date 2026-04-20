<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncSisantriSheets extends Command
{
    protected $signature = 'sisantri:sync-sheets
                            {--sheet-id= : Spreadsheet ID override}
                            {--credentials= : Credentials path override}
                            {--sync-absensi : Replace absensi from Log Absensi sheet too}
                            {--dry-run : Preview counts without writing database}';

    protected $description = 'Sinkronkan data master SISANTRI dari Google Sheets sumber.';

    public function handle(): int
    {
        try {
            $sheetId = $this->option('sheet-id') ?: env('GOOGLE_SHEETS_ID');
            $credentialsPath = $this->resolveCredentialsPath(
                $this->option('credentials') ?: env('GOOGLE_SHEETS_CREDENTIALS_PATH', 'storage/app/credentials.json')
            );

            if (!$sheetId) {
                $this->error('GOOGLE_SHEETS_ID belum diatur.');
                return self::FAILURE;
            }

            if (!is_file($credentialsPath)) {
                $this->error('File credentials tidak ditemukan di: '.$credentialsPath);
                return self::FAILURE;
            }

            $service = $this->makeSheetsService($credentialsPath);
            $sheetData = $this->fetchSheetData($service, $sheetId);
            $payload = $this->buildPayload($sheetData);

            $summary = [
                'users' => count($payload['users']),
                'santri' => count($payload['santri']),
                'kitab_diniyah' => count($payload['kitab']),
                'absensi' => count($payload['absensi']),
            ];

            $this->table(
                ['Data', 'Jumlah'],
                [
                    ['User', $summary['users']],
                    ['Santri', $summary['santri']],
                    ['Kitab Diniyah', $summary['kitab_diniyah']],
                    ['Absensi (sheet)', $summary['absensi']],
                ]
            );

            if ($this->option('dry-run')) {
                $this->info('Dry run selesai. Tidak ada data yang diubah.');
                return self::SUCCESS;
            }

            $backupFile = $this->backupCurrentState();
            $this->restoreDatabase($payload, (bool) $this->option('sync-absensi'));

            $restored = [
                'users' => DB::table('users')->count(),
                'santri' => DB::table('santri')->count(),
                'kitab_diniyah' => DB::table('kitab_diniyah')->count(),
                'absensi' => DB::table('absensi')->count(),
            ];

            $this->info('Sinkronisasi selesai.');
            $this->line('Backup: '.$backupFile);
            $this->table(
                ['Tabel', 'Jumlah Setelah Sinkron'],
                [
                    ['users', $restored['users']],
                    ['santri', $restored['santri']],
                    ['kitab_diniyah', $restored['kitab_diniyah']],
                    ['absensi', $restored['absensi']],
                ]
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sinkronisasi gagal: '.$e->getMessage());
            return self::FAILURE;
        }
    }

    protected function resolveCredentialsPath(string $path): string
    {
        if (Str::startsWith($path, [DIRECTORY_SEPARATOR, 'C:\\', 'D:\\'])) {
            return $path;
        }

        $candidates = [
            base_path($path),
            storage_path($path),
            storage_path('app/'.$path),
            base_path('storage/app/'.$path),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return base_path($path);
    }

    protected function makeSheetsService(string $credentialsPath): Sheets
    {
        $client = new Client();
        $client->setApplicationName('SISANTRI Sync');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig($credentialsPath);

        return new Sheets($client);
    }

    protected function fetchSheetData(Sheets $service, string $sheetId): array
    {
        $ranges = [
            'santri' => 'Data Induk Santri!A:Z',
            'kitab' => 'KITAB_DINIYAH!A:Z',
            'absensi' => 'Log Absensi!A:Z',
            'users' => 'USER_ROLES!A:Z',
        ];

        $result = [];

        foreach ($ranges as $key => $range) {
            $result[$key] = $service->spreadsheets_values->get($sheetId, $range)->getValues() ?? [];
        }

        return $result;
    }

    protected function buildPayload(array $sheetData): array
    {
        $now = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

        $santriRows = [];
        foreach (array_slice($sheetData['santri'] ?? [], 1) as $row) {
            $id = $this->normalizeValue($row[0] ?? null);
            $nama = $this->normalizeValue($row[1] ?? null);

            if (!$id || !$nama) {
                continue;
            }

            $santriRows[$id] = [
                'id_santri' => $id,
                'nama' => $nama,
                'jenis_kelamin' => $this->normalizeValue($row[2] ?? null) ?? 'Tidak Diketahui',
                'kelas' => $this->normalizeValue($row[3] ?? null) ?? '-',
                'golongan' => $this->normalizeValue($row[4] ?? null),
                'pembina' => $this->normalizeValue($row[5] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $kitabRows = [];
        foreach (array_slice($sheetData['kitab'] ?? [], 1) as $row) {
            $idKitab = $this->normalizeValue($row[0] ?? null);
            $namaKitab = $this->normalizeValue($row[1] ?? null);

            if (!$idKitab || !$namaKitab) {
                continue;
            }

            $kelasTarget = $this->normalizeValue($row[2] ?? null) ?? 'ALL';
            $jenisKegiatan = $this->normalizeValue($row[3] ?? null);
            $pengampu = $this->normalizeValue($row[4] ?? null);
            $jadwal = $this->normalizeValue($row[5] ?? null);
            $meta = implode(' | ', array_filter([$jenisKegiatan, $pengampu, $jadwal]));

            $kitabRows[$idKitab] = [
                'id_kitab' => $idKitab,
                'kelas_kitab' => $kelasTarget,
                'pengampu_golongan' => Str::limit($meta !== '' ? $meta : $kelasTarget, 255, ''),
                'nama_kitab' => $namaKitab,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $validRoles = ['Admin', 'Pembina', 'Ustadz Pengajar'];
        $userRows = [];
        foreach (array_slice($sheetData['users'] ?? [], 1) as $row) {
            $parsed = $this->parseUserRow($row, $validRoles);

            if (!$parsed) {
                continue;
            }

            $passwordHash = $parsed['password_hash'];
            $useStoredHash = is_string($passwordHash) && preg_match('/^\$2[aby]\$/', $passwordHash);
            $namaLengkap = $parsed['nama_lengkap'];
            $kelasKitab = $parsed['kelas_kitab_hendel'];

            if ($kelasKitab === 'N/A') {
                $kelasKitab = null;
            }

            $userRows[$parsed['email']] = [
                'name' => $this->makeUsername($parsed['email'], $namaLengkap),
                'email' => $parsed['email'],
                'email_verified_at' => $now,
                'password' => $useStoredHash ? $passwordHash : Hash::make('password'),
                'role' => $parsed['role'],
                'nama_lengkap' => $namaLengkap ?? $this->makeUsername($parsed['email'], $namaLengkap),
                'kelas_kitab_hendel' => $kelasKitab,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'last_login_at' => null,
            ];
        }

        if (!isset($userRows['admin@sisantri.com'])) {
            $userRows['admin@sisantri.com'] = [
                'name' => 'admin',
                'email' => 'admin@sisantri.com',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'nama_lengkap' => 'Admin',
                'kelas_kitab_hendel' => 'ALL',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'last_login_at' => null,
            ];
        }

        $absensiRows = [];
        foreach (array_slice($sheetData['absensi'] ?? [], 1) as $row) {
            $timestamp = $this->parseAttendanceTimestamp($row[0] ?? null);
            $santriId = $this->normalizeValue($row[1] ?? null);
            $kegiatan = $this->normalizeValue($row[2] ?? null);
            $status = $this->normalizeValue($row[3] ?? null);

            if (!$timestamp || !$santriId || !$kegiatan || !$status) {
                continue;
            }

            $absensiRows[] = [
                'timestamp' => $timestamp,
                'santri_id' => $santriId,
                'kegiatan' => $kegiatan,
                'status' => strtoupper($status),
                'petugas_id' => $this->normalizeValue($row[4] ?? null),
                'nama_santri' => $this->normalizeValue($row[5] ?? null),
                'golongan' => $this->normalizeValue($row[6] ?? null),
                'kelas' => $this->normalizeValue($row[7] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [
            'users' => array_values($userRows),
            'santri' => array_values($santriRows),
            'kitab' => array_values($kitabRows),
            'absensi' => $absensiRows,
        ];
    }

    protected function backupCurrentState(): string
    {
        $backupDir = storage_path('app/private');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $backupFile = $backupDir.'/sync-backup-'.date('Ymd-His').'.json';
        $payload = [
            'generated_at' => Carbon::now('Asia/Jakarta')->toIso8601String(),
            'users_count' => DB::table('users')->count(),
            'santri_count' => DB::table('santri')->count(),
            'kitab_diniyah_count' => DB::table('kitab_diniyah')->count(),
            'absensi_count' => DB::table('absensi')->count(),
        ];

        file_put_contents($backupFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $backupFile;
    }

    protected function restoreDatabase(array $payload, bool $syncAbsensi): void
    {
        DB::transaction(function () use ($payload, $syncAbsensi) {
            DB::table('sessions')->delete();
            DB::table('cache_locks')->delete();
            DB::table('cache')->delete();
            DB::table('password_reset_tokens')->delete();

            if ($syncAbsensi) {
                DB::table('absensi')->delete();
            }

            DB::table('kitab_diniyah')->delete();
            DB::table('santri')->delete();
            DB::table('users')->delete();

            foreach (array_chunk($payload['santri'], 100) as $chunk) {
                DB::table('santri')->insert($chunk);
            }

            foreach (array_chunk($payload['kitab'], 100) as $chunk) {
                DB::table('kitab_diniyah')->insert($chunk);
            }

            foreach (array_chunk($payload['users'], 100) as $chunk) {
                DB::table('users')->insert($chunk);
            }

            if ($syncAbsensi && !empty($payload['absensi'])) {
                foreach (array_chunk($payload['absensi'], 100) as $chunk) {
                    DB::table('absensi')->insert($chunk);
                }
            }
        });
    }

    protected function normalizeValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;
        $value = preg_replace('/\x{00A0}/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function makeUsername(string $email, ?string $fallbackName = null): string
    {
        $base = strtolower(trim(Str::before($email, '@')));
        $base = preg_replace('/[^a-z0-9]+/i', '_', $base) ?: 'user';
        $base = trim($base, '_');

        if ($base !== '') {
            return $base;
        }

        $fallbackName = $this->normalizeValue($fallbackName) ?? 'user';
        $fallbackName = preg_replace('/[^a-z0-9]+/i', '_', strtolower($fallbackName)) ?: 'user';

        return trim($fallbackName, '_') ?: 'user';
    }

    protected function parseUserRow(array $row, array $validRoles): ?array
    {
        $row = array_map(fn ($value) => $this->normalizeValue($value), $row);
        $count = count($row);

        if ($count < 4) {
            return null;
        }

        $email = $row[0] ?? null;
        $passwordHash = null;
        $role = null;
        $kelasKitab = null;
        $namaLengkap = null;

        if ($count >= 5) {
            $passwordHash = $row[1] ?? null;
            $role = $row[2] ?? null;
            $kelasKitab = $row[3] ?? null;
            $namaLengkap = $row[4] ?? null;
        } elseif (in_array($row[1] ?? null, $validRoles, true)) {
            $role = $row[1] ?? null;
            $kelasKitab = $row[2] ?? null;
            $namaLengkap = $row[3] ?? null;
        }

        $email = $email ? strtolower($email) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        if (!in_array($role, $validRoles, true)) {
            return null;
        }

        return [
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'kelas_kitab_hendel' => $kelasKitab,
            'nama_lengkap' => $namaLengkap,
        ];
    }

    protected function parseAttendanceTimestamp($value): ?string
    {
        $value = $this->normalizeValue($value);

        if (!$value) {
            return null;
        }

        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value, 'Asia/Jakarta')->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta')->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
