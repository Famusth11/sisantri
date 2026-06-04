<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use App\Models\Absensi;
use App\Models\JadwalDiniyah;
use App\Models\KitabDiniyah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    protected const ATTENDANCE_TIMEZONE = 'Asia/Jakarta';
    protected const DISPLAY_CACHE_VERSION = 'kegiatan_nama_jam_perf_v8';

    protected function shouldLogDebug(): bool
    {
        return app()->isLocal() && config('app.debug');
    }

    protected function getCachedVisibleSantri($user)
    {
        $cacheKey = Santri::visibleListCacheKey($user, 'full');

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            return Santri::getAllForUser($user);
        });
    }

    protected function getCachedDiniyahSantri($user)
    {
        if ($user && in_array($user->role, ['Admin', 'Ustadz Pengajar', 'Pembina'], true)) {
            return Cache::remember('diniyah_santri_all_for_attendance_v1', 1800, function () {
                return Santri::query()
                    ->select(Santri::listColumns())
                    ->orderBy('nama')
                    ->get();
            });
        }

        return $this->getCachedVisibleSantri($user);
    }

    protected function getSantriBySelectedClass(?string $kelasFilter, $fallbackCollection = null)
    {
        if (!in_array($kelasFilter, ['10', '11', '12'], true)) {
            return $fallbackCollection ?? collect([]);
        }

        if ($fallbackCollection !== null) {
            return $fallbackCollection->filter(function ($santri) use ($kelasFilter) {
                return (string) ($santri->kelas ?? '') === $kelasFilter;
            })->values();
        }

        return Santri::select('id_santri', 'nama', 'jenis_kelamin', 'kelas', 'golongan', 'pembina')
            ->where('kelas', $kelasFilter)
            ->orderBy('nama')
            ->get();
    }

    protected function buildSantriLookup($santriCollection): array
    {
        $lookup = [];

        foreach ($santriCollection as $santri) {
            $lookup[$santri->id_santri] = [
                'nama' => $santri->nama,
                'kelas' => (string) $santri->kelas,
                'golongan' => (string) $santri->golongan,
                'golongan_normalized' => strtolower(trim((string) ($santri->golongan ?? ''))),
            ];
        }

        return $lookup;
    }

    protected function buildVisibleSantriIdSet($santriCollection): array
    {
        $visibleSantriIds = [];

        foreach ($santriCollection as $santri) {
            $visibleSantriIds[(string) $santri->id_santri] = true;
        }

        return $visibleSantriIds;
    }

    protected function getDisplayCacheKey(string $baseKey): string
    {
        return $baseKey . '_' . static::DISPLAY_CACHE_VERSION;
    }

    protected function filterSantriCollection($santriCollection, ?string $kelasFilter = null, ?string $golonganFilter = null)
    {
        $kelasFilter = trim((string) $kelasFilter);
        $golonganFilter = strtolower(trim((string) $golonganFilter));

        return $santriCollection->filter(function ($santri) use ($kelasFilter, $golonganFilter) {
            if ($kelasFilter !== '' && (string) ($santri->kelas ?? '') !== $kelasFilter) {
                return false;
            }

            if ($golonganFilter !== '' && strtolower(trim((string) ($santri->golongan ?? ''))) !== $golonganFilter) {
                return false;
            }

            return true;
        })->values();
    }

    protected function buildAttendanceTimestamp(?string $chosenDate): Carbon
    {
        $now = Carbon::now(static::ATTENDANCE_TIMEZONE);

        if (empty($chosenDate)) {
            return $now;
        }

        $selectedTimestamp = Carbon::parse($chosenDate, static::ATTENDANCE_TIMEZONE)
            ->setTime($now->hour, $now->minute, $now->second);

        return $selectedTimestamp->isFuture() ? $now : $selectedTimestamp;
    }

    protected function buildDayRange(?string $chosenDate): ?array
    {
        $chosenDate = trim((string) $chosenDate);

        if ($chosenDate === '') {
            return null;
        }

        $start = Carbon::parse($chosenDate, static::ATTENDANCE_TIMEZONE)->startOfDay();

        return [$start, $start->copy()->endOfDay()];
    }

    protected function createAttendanceSummaryRow(array $santriInfo): array
    {
        $summary = [
            'nama' => $santriInfo['nama'] ?? '',
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'detail_per_hari' => [],
        ];

        if (array_key_exists('golongan', $santriInfo)) {
            $summary['golongan'] = $santriInfo['golongan'];
        }

        return $summary;
    }

    protected function createAttendanceDetailRow(): array
    {
        return [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'kegiatan' => [],
            'jam' => [],
            'ustadz' => [],
        ];
    }

    protected function addUniqueSummaryValue(array &$target, string $value): void
    {
        if ($value === '') {
            return;
        }

        $target[$value] = $value;
    }

    protected function incrementAttendanceCounters(array &$summaryRow, array &$dailyRow, string $status): void
    {
        $summaryRow['total']++;

        switch ($status) {
            case 'HADIR':
                $summaryRow['hadir']++;
                $dailyRow['hadir']++;
                break;
            case 'IZIN':
                $summaryRow['izin']++;
                $dailyRow['izin']++;
                break;
            case 'SAKIT':
                $summaryRow['sakit']++;
                $dailyRow['sakit']++;
                break;
            case 'ALPHA':
            case 'TIDAK HADIR':
                $summaryRow['alpha']++;
                $dailyRow['alpha']++;
                break;
        }
    }

    protected function finalizeAttendanceSummaryDetails(array &$summaryPerSantri): void
    {
        foreach ($summaryPerSantri as &$santriData) {
            foreach ($santriData as &$summaryRow) {
                foreach ($summaryRow['detail_per_hari'] as &$dailyRow) {
                    foreach ($dailyRow['kegiatan'] as &$kegiatanByStatus) {
                        $kegiatanByStatus = array_values($kegiatanByStatus);
                    }

                    $dailyRow['jam'] = array_values($dailyRow['jam']);
                    sort($dailyRow['jam']);

                    if (isset($dailyRow['ustadz'])) {
                        $dailyRow['ustadz'] = array_values($dailyRow['ustadz']);
                        sort($dailyRow['ustadz']);
                    }
                }
                unset($dailyRow);
            }
            unset($summaryRow);
        }
        unset($santriData);
    }

    protected function buildGolonganOptions($santriCollection): array
    {
        $optionsByLookup = [];

        foreach ($santriCollection as $santri) {
            $value = trim((string) ($santri->golongan ?? ''));

            if ($value === '') {
                continue;
            }

            $lookupKey = strtoupper($value);

            if (
                !isset($optionsByLookup[$lookupKey])
                || $this->preferGolonganDisplayValue($value, $optionsByLookup[$lookupKey])
            ) {
                $optionsByLookup[$lookupKey] = $value;
            }
        }

        $options = array_values($optionsByLookup);
        usort($options, fn (string $left, string $right) => strnatcasecmp($left, $right));

        return $options;
    }

    protected function preferGolonganDisplayValue(string $candidate, string $current): bool
    {
        return $current === strtoupper($current) && $candidate !== strtoupper($candidate);
    }

    protected function forgetMonthlyRecapCaches(string $month, $targetUser = null): void
    {
        $users = $targetUser ? collect([$targetUser]) : User::select('id', 'role')->get();
        $kelasOptions = ['all', '10', '11', '12'];
        $golonganOptions = ['all', 'putra', 'putri', 'bilingual', 'tahfidz'];

        foreach ($users as $user) {
            Cache::forget('rekap_sholat_raw_' . $month . '_' . $user->id);
            Cache::forget('rekap_bulanan_raw_' . $month . '_' . $user->id);
            Cache::forget('rekap_bulanan_raw_with_ustadz_' . $month . '_' . $user->id);
            Cache::forget('rekap_bulanan_diniyah_raw_with_ustadz_' . $month . '_' . $user->id);

            foreach ($kelasOptions as $kelas) {
                Cache::forget($this->getDisplayCacheKey('rekap_sholat_processed_' . $month . '_' . $user->id . '_' . $kelas));

                foreach ($golonganOptions as $golongan) {
                    Cache::forget($this->getDisplayCacheKey('rekap_bulanan_processed_' . $month . '_' . $user->id . '_' . $kelas . '_' . $golongan));
                }
            }
        }
    }

    protected function clearAbsensiCache()
    {
        try {
            $month = Carbon::now(static::ATTENDANCE_TIMEZONE)->format('Y-m');
            $users = User::select('id', 'role')->get();

            foreach ($users as $user) {
                Cache::forget('dashboard_stats_' . $user->id . '_' . $month);
                \App\Models\Santri::clearCache($user);
            }

            $this->forgetMonthlyRecapCaches($month);
            Cache::forget('diniyah_data_all_santri');
            Cache::forget('diniyah_santri_all_for_attendance_v1');
            Cache::forget('kitab_diniyah_list');
            Cache::forget('jadwal_diniyah_active_list');
            Cache::forget('jadwal_diniyah_active_list_grouped_v2');
            Cache::forget('jadwal_diniyah_active_period');

            if ($this->shouldLogDebug()) {
                Log::debug('Cache cleared for absensi and dashboard', ['month' => $month]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache: ' . $e->getMessage());
        }
    }

    protected function getActiveJadwalDiniyah()
    {
        return Cache::remember('jadwal_diniyah_active_list_grouped_v2', 1800, function () {
            return JadwalDiniyah::query()
                ->with('kitab:id_kitab,nama_kitab')
                ->active()
                ->orderBy('kelas')
                ->orderBy('golongan')
                ->orderBy('jam_mulai')
                ->orderBy('nama_kegiatan')
                ->get()
                ->unique(function (JadwalDiniyah $jadwal): string {
                    return $this->jadwalDiniyahDisplayKey($jadwal);
                })
                ->values();
        });
    }

    protected function filterSantriByJadwal($santriCollection, JadwalDiniyah $jadwal, ?string $kelasFilter = null, bool $useJadwalClass = true): array
    {
        $kelasFilter = trim((string) $kelasFilter);
        $targetClass = $useJadwalClass ? trim((string) ($jadwal->kelas ?? '')) : '';
        $targetGolongan = strtoupper(trim((string) ($jadwal->golongan ?? '')));

        return $santriCollection->filter(function ($santri) use ($kelasFilter, $targetClass, $targetGolongan) {
            if ($kelasFilter !== '' && (string) ($santri->kelas ?? '') !== $kelasFilter) {
                return false;
            }

            if ($targetClass !== '' && strtoupper($targetClass) !== 'ALL') {
                $santriKelas = preg_replace('/[^0-9]/', '', trim((string) ($santri->kelas ?? '')));
                $jadwalKelas = preg_replace('/[^0-9]/', '', $targetClass);

                if ($jadwalKelas !== '') {
                    if ($santriKelas !== $jadwalKelas) {
                        return false;
                    }
                } elseif (strtoupper(trim((string) ($santri->kelas ?? ''))) !== strtoupper($targetClass)) {
                    return false;
                }
            }

            if ($targetGolongan === '' || $targetGolongan === 'ALL') {
                return true;
            }

            return strtoupper(trim((string) ($santri->golongan ?? ''))) === $targetGolongan;
        })->values()->all();
    }

    protected function jadwalDiniyahDisplayKey(JadwalDiniyah $jadwal): string
    {
        $namaJadwal = trim((string) ($jadwal->nama_kegiatan ?: $jadwal->kitab?->nama_kitab ?: $jadwal->kitab_id));

        return strtoupper($namaJadwal);
    }

    public function sholat(Request $request)
    {
        $user = Auth::user();
        $kelasFilter = trim((string) $request->query('kelas', ''));
        $visibleSantri = $this->getCachedVisibleSantri($user);
        $santriList = $this->filterSantriCollection($visibleSantri, $kelasFilter);
        $jenisSholatOptions = ['Subuh', 'Asar', 'Maghrib', 'Isya'];
        return view('absensi.sholat', compact('santriList', 'jenisSholatOptions', 'kelasFilter'));
    }

    public function storeSholat(Request $request)
    {
        try {
            $request->validate([
                'id_santri' => 'required|string',
                'jenis_sholat' => 'required|in:Subuh,Asar,Maghrib,Isya',
                'tanggal' => 'nullable|date',
            ]);

            $user = Auth::user();
            
            $santri = Santri::findById($request->id_santri);
            
            if (!$santri) {
                return Response::json(['error' => 'Santri tidak ditemukan.'], 404);
            }
            
            $visibleSantri = $this->getCachedVisibleSantri($user);
            $visibleSantriIds = $this->buildVisibleSantriIdSet($visibleSantri);

            if (!isset($visibleSantriIds[(string) $request->id_santri])) {
                return Response::json(['error' => 'Akses ditolak untuk santri ini.'], 403);
            }

            $timestamp = $this->buildAttendanceTimestamp($request->input('tanggal'));
            $kegiatan = 'Sholat ' . $request->jenis_sholat;
            $status = 'HADIR';
            $petugasId = $user->email;

            Absensi::create([
                'timestamp' => $timestamp,
                'santri_id' => $request->id_santri,
                'kegiatan' => $kegiatan,
                'status' => $status,
                'petugas_id' => $petugasId,
                'nama_santri' => $santri->nama,
                'kelas' => $santri->kelas,
                'golongan' => $santri->golongan,
            ]);

            $this->clearAbsensiCache();

            return Response::json(['success' => true, 'message' => 'Absensi Sholat berhasil dicatat untuk ' . $santri->nama]);
        } catch (\Exception $e) {
            Log::error('Store Sholat Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal simpan absensi: ' . $e->getMessage()], 500);
        }
    }

    public function diniyah(Request $request)
    {
        $user = Auth::user();
        $kelasFilter = trim((string) $request->query('kelas', ''));
        $jadwalData = $this->getActiveJadwalDiniyah();
        $allSantriData = $this->getCachedDiniyahSantri($user);

        $santriList = [];
        $selectedJadwal = trim((string) $request->query('jadwal_id', ''));
        $selectedJadwalData = null;
        $activeScheduleInfo = null;

        if ($jadwalData->isNotEmpty()) {
            $firstActiveSchedule = $jadwalData->first();
            $activeScheduleInfo = [
                'tahun_ajaran' => $firstActiveSchedule->tahun_ajaran,
                'semester' => $firstActiveSchedule->semester,
            ];
        }

        if ($selectedJadwal !== '') {
            $selectedJadwalData = $jadwalData->firstWhere('id', (int) $selectedJadwal);

            if ($selectedJadwalData) {
                $santriList = $this->filterSantriByJadwal($allSantriData, $selectedJadwalData, $kelasFilter, false);
            }
        }

        return view('absensi.diniyah', compact('santriList', 'jadwalData', 'selectedJadwal', 'selectedJadwalData', 'kelasFilter', 'activeScheduleInfo'));
    }

    
    public function storeDiniyah(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|integer',
            'absensi' => 'required|array',  
            'tanggal' => 'nullable|date',
            'kelas_mengajar' => 'required|in:10,11,12',
        ]);

        $user = Auth::user();
        $timestamp = $this->buildAttendanceTimestamp($request->input('tanggal'));
        $kelasMengajar = trim((string) $request->input('kelas_mengajar'));
        $namaUstadz = $this->resolveLoggedInPengampuName($user);
        $jadwal = JadwalDiniyah::query()
            ->active()
            ->with('kitab:id_kitab,nama_kitab')
            ->find($request->integer('jadwal_id'));

        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal diniyah tidak ditemukan atau belum aktif.');
        }

        $namaKitab = trim((string) ($jadwal->nama_kegiatan ?: $jadwal->kitab?->nama_kitab ?: $jadwal->kitab_id));
        $kegiatan = 'Ngaji ' . $namaKitab;
        $petugasId = $user->email;
        $recordedAt = Carbon::now(static::ATTENDANCE_TIMEZONE);

        
        $allSantriData = $this->getCachedDiniyahSantri($user);
        $allowedSantriData = collect($this->filterSantriByJadwal($allSantriData, $jadwal, $kelasMengajar, false));
        $visibleSantriIds = $this->buildVisibleSantriIdSet($allowedSantriData);

        $santriLookup = $allowedSantriData->keyBy('id_santri');

        $absensiRecords = [];
        foreach ($request->absensi as $idSantri => $status) {
            if (empty($status)) continue;  

            
            $idSantriStr = (string) $idSantri;
            
            $santri = $santriLookup[$idSantriStr] ?? null;
            if (!$santri || !isset($visibleSantriIds[$idSantriStr])) {
                continue;  
            }

            
            $absensiRecords[] = [
                'timestamp' => $timestamp->copy(),
                'santri_id' => $idSantriStr,
                'kegiatan' => $kegiatan,
                'status' => strtoupper($status),
                'petugas_id' => $petugasId,
                'nama_ustadz' => $namaUstadz,
                'kelas_mengajar' => $kelasMengajar,
                'nama_santri' => $santri->nama,
                'kelas' => $santri->kelas,
                'golongan' => $santri->golongan,
                'created_at' => $recordedAt->copy(),
                'updated_at' => $recordedAt->copy(),
            ];
        }

        if (empty($absensiRecords)) {
            return redirect()->back()->with('error', 'Tidak ada absensi yang valid.');
        }

        
        try {
            
            foreach (array_chunk($absensiRecords, 100) as $chunk) {
                Absensi::insert($chunk);
            }
            
            
            $this->clearAbsensiCache();
            
            return redirect()->back()->with('success', count($absensiRecords) . ' absensi Diniyah berhasil dicatat untuk jadwal: ' . $namaKitab . ' kelas ' . $kelasMengajar . ' oleh ' . $namaUstadz);
        } catch (\Exception $e) {
            Log::error('Store Diniyah Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal simpan absensi: ' . $e->getMessage());
        }
    }
    
    public function refreshData(Request $request)
    {
        try {
            $user = Auth::user();
            
            
            $cacheKey = $this->getDisplayCacheKey('refresh_data_' . $user->id . '_' . ($request->tanggal ?? 'all'));
            
            
            $cachedResult = Cache::get($cacheKey);
            if ($cachedResult !== null && !$request->has('force_refresh')) {
                return Response::json($cachedResult);
            }
            
            
            $query = Absensi::query();
            
            
            $allSantriData = $this->getCachedVisibleSantri($user);
            $santriLookup = $this->buildSantriLookup($allSantriData);

            
            $visibleSantriIds = array_keys($santriLookup);
            if (empty($visibleSantriIds)) {
                return Response::json([
                    'success' => true,
                    'data' => [],
                    'total_records' => 0,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            $query->select('timestamp', 'santri_id', 'nama_santri', 'kegiatan', 'status', 'petugas_id');
            $query->whereIn('santri_id', $visibleSantriIds);

            
            $dayRange = $this->buildDayRange($request->input('tanggal'));
            if ($dayRange !== null) {
                $query->whereBetween('timestamp', $dayRange);
            }
            
            $filteredLog = $query->orderBy('timestamp', 'desc')->get();

            
            $tableRows = [];
            foreach ($filteredLog as $row) {
                
                $santri = $santriLookup[$row->santri_id] ?? null;
                $namaSantri = $santri['nama'] ?? ($row->nama_santri ?? 'Tidak Diketahui');
                $statusBadge = $row->status === 'HADIR' ? 'bg-success' : ($row->status === 'SAKIT' ? 'bg-warning' : ($row->status === 'IZIN' ? 'bg-info' : 'bg-danger'));
                
                
                $kegiatan = Absensi::formatKegiatanLabel($row->kegiatan);
                $badgeClass = 'bg-secondary';
                $badgeIcon = 'fas fa-book';
                
                if (str_contains($kegiatan, 'Sholat')) {
                    $badgeClass = 'bg-primary';
                    $badgeIcon = 'fas fa-mosque';
                } elseif (str_contains($kegiatan, 'Ngaji')) {
                    $badgeClass = 'bg-success';
                    $badgeIcon = 'fas fa-book-open';
                } elseif (str_contains($kegiatan, 'Tahfidz')) {
                    $badgeClass = 'bg-warning text-dark';
                    $badgeIcon = 'fas fa-quran';
                } elseif (str_contains($kegiatan, 'Diniyah')) {
                    $badgeClass = 'bg-info';
                    $badgeIcon = 'fas fa-graduation-cap';
                }

                $tableRows[] = [
                    'timestamp' => $row->timestamp->format('Y-m-d H:i:s'),
                    'id_santri' => $row->santri_id,
                    'nama_santri' => $namaSantri,
                    'kegiatan' => $kegiatan,
                    'kegiatan_badge_class' => $badgeClass,
                    'kegiatan_badge_icon' => $badgeIcon,
                    'status' => $row->status,
                    'status_badge_class' => $statusBadge,
                    'petugas' => $row->petugas_id
                ];
            }

            $response = [
                'success' => true,
                'data' => $tableRows,
                'total_records' => $filteredLog->count(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];
            
            
            Cache::put($cacheKey, $response, now()->addMinutes(1));
            
            return Response::json($response);

        } catch (\Exception $e) {
            Log::error('Refresh Data Error: ' . $e->getMessage());
            return Response::json([
                'success' => false,
                'error' => 'Gagal me-refresh data: ' . $e->getMessage()
            ], 500);
        }
    }

    
    


    protected function getMonthlyRecapData(Request $request)
    {
        
        set_time_limit(300); 
        ini_set('max_execution_time', 300);
        
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelasFilter = $request->get('kelas');
        $golonganFilter = $request->get('golongan');
        $jadwalFilter = $this->normalizeRecapJadwalFilter($request->get('jadwal'));

        
        $cacheKey = $this->getDisplayCacheKey('rekap_bulanan_processed_' . $month . '_' . $user->id . '_' . ($kelasFilter ?? 'all') . '_' . ($golonganFilter ?? 'all') . '_' . ($jadwalFilter ?: 'all_jadwal'));
        
        
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }

        $allSantri = $this->getCachedVisibleSantri($user);
        $golonganFilterNormalized = $golonganFilter ? strtolower(trim($golonganFilter)) : '';
        $filteredSantri = $this->filterSantriCollection($allSantri, $kelasFilter, $golonganFilterNormalized);
        $santriLookup = $this->buildSantriLookup($filteredSantri);
        $allVisibleSantriIds = $allSantri->pluck('id_santri')->all();

        $visibleSantriIds = array_keys($santriLookup);
        if (empty($visibleSantriIds)) {
            return [
                'month' => $month,
                'kelasFilter' => $kelasFilter,
                'golonganFilter' => $golonganFilter,
                'jadwalFilter' => $jadwalFilter,
                'jadwalList' => $this->getRecapJadwalOptions(),
                'kelasList' => ['10', '11', '12'],
                'golonganList' => [],
                'summaryPerSantri' => [],
            ];
        }

        $rawCacheKey = 'rekap_bulanan_diniyah_raw_with_ustadz_' . $month . '_' . $user->id;
        $attendanceData = Cache::remember($rawCacheKey, 1800, function () use ($month, $allVisibleSantriIds) {
            return Absensi::forMonth($month)
                ->select('id', 'timestamp', 'created_at', 'santri_id', 'kegiatan', 'status', 'nama_ustadz', 'petugas_id')
                ->whereIn('santri_id', $allVisibleSantriIds)
                ->where(function ($query) {
                    $query->where('kegiatan', 'like', 'Ngaji%')
                        ->orWhere('kegiatan', 'like', 'Diniyah%')
                        ->orWhere('kegiatan', 'like', 'Tahfidz%');
                })
                ->orderBy('timestamp')
                ->get();
        });

        $jadwalList = $this->getRecapJadwalOptions($attendanceData);

        
        $summaryPerSantri = [];
        foreach ($attendanceData as $row) {
            $kegiatan = Absensi::formatKegiatanLabel($row->kegiatan);
            if ($jadwalFilter !== '' && $this->normalizeRecapJadwalFilter($kegiatan) !== $jadwalFilter) {
                continue;
            }

            $santriId = $row->santri_id;
            $status = strtoupper(trim($row->status));
            
            
            $santriInfo = $santriLookup[$santriId] ?? null;
            if (!$santriInfo) continue;
            
            $kelas = $santriInfo['kelas'];
            $golongan = $santriInfo['golongan'];

            if (!isset($summaryPerSantri[$kelas])) {
                $summaryPerSantri[$kelas] = [];
            }
            if (!isset($summaryPerSantri[$kelas][$santriId])) {
                $summaryPerSantri[$kelas][$santriId] = $this->createAttendanceSummaryRow([
                    'nama' => $santriInfo['nama'] ?? $santriId,
                    'golongan' => $golongan,
                ]);
            }

            $tanggalAbsen = $row->timestamp->format('Y-m-d');

            
            if (!isset($summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen])) {
                $summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen] = $this->createAttendanceDetailRow();
            }

            
            $jamAbsen = Absensi::resolveAttendanceTime($row->timestamp, $row->created_at);
            $namaPengabsen = trim((string) ($row->nama_ustadz ?: $row->petugas_id));
            if (!isset($summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen]['kegiatan'][$status])) {
                $summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen]['kegiatan'][$status] = [];
            }

            $dailySummary = &$summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen];
            $summaryRow = &$summaryPerSantri[$kelas][$santriId];

            $this->addUniqueSummaryValue($dailySummary['kegiatan'][$status], $kegiatan);
            $this->addUniqueSummaryValue($dailySummary['jam'], $jamAbsen);
            $this->addUniqueSummaryValue($dailySummary['ustadz'], $namaPengabsen);
            $this->incrementAttendanceCounters($summaryRow, $dailySummary, $status);

            unset($dailySummary, $summaryRow);
        }

        $this->finalizeAttendanceSummaryDetails($summaryPerSantri);

        
        ksort($summaryPerSantri);
        foreach ($summaryPerSantri as $kelas => &$santriData) {
            uasort($santriData, function ($a, $b) {
                return strcmp($a['nama'], $b['nama']);
            });
        }

        
        $allKelasList = ['10', '11', '12'];
        
        
        $existingKelas = array_keys($summaryPerSantri);
        $kelasList = array_unique(array_merge($allKelasList, $existingKelas));
        sort($kelasList);

        
        $golonganList = $this->buildGolonganOptions($allSantri);
        if (empty($golonganList)) {
            
            $golonganList = ['Bilingual', 'Takhfidz'];
        }

        if ($this->shouldLogDebug()) {
            \Log::debug('Rekap Bulanan built', [
                'month' => $month,
                'kelasFilter' => $kelasFilter,
                'golonganFilter' => $golonganFilter,
                'jadwalFilter' => $jadwalFilter,
                'kelas_count' => count($kelasList),
                'santri_count' => array_sum(array_map('count', $summaryPerSantri)),
            ]);
        }

        $result = [
            'month' => $month,
            'kelasFilter' => $kelasFilter,
            'golonganFilter' => $golonganFilter,
            'jadwalFilter' => $jadwalFilter,
            'jadwalList' => $jadwalList,
            'kelasList' => $kelasList,
            'golonganList' => $golonganList,
            'summaryPerSantri' => $summaryPerSantri,
        ];
        
        
        Cache::put($cacheKey, $result, now()->addMinutes(10));
        
        return $result;
    }

    protected function normalizeRecapJadwalFilter(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^(Ngaji|Diniyah|Tahfidz|Sholat|Solat)\s+/iu', '', $value) ?? $value;

        return strtoupper(trim($value));
    }

    protected function getRecapJadwalOptions($attendanceData = null): array
    {
        $activeJadwalList = $this->getActiveJadwalDiniyah()
            ->map(fn (JadwalDiniyah $jadwal) => $this->formatJadwalDiniyahName($jadwal));

        $allScheduleNames = JadwalDiniyah::query()
            ->whereNotNull('nama_kegiatan')
            ->distinct()
            ->pluck('nama_kegiatan');

        $masterKitabNames = KitabDiniyah::query()
            ->whereNotNull('nama_kitab')
            ->distinct()
            ->pluck('nama_kitab');

        $recordedJadwalList = collect($attendanceData ?? [])
            ->map(fn ($row) => Absensi::formatKegiatanLabel($row->kegiatan));

        return $activeJadwalList
            ->merge($allScheduleNames)
            ->merge($masterKitabNames)
            ->merge($recordedJadwalList)
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->unique(fn ($value) => $this->normalizeRecapJadwalFilter($value))
            ->sortBy(fn ($value) => $value, SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    protected function formatJadwalDiniyahName(JadwalDiniyah $jadwal): string
    {
        return trim((string) ($jadwal->nama_kegiatan ?: $jadwal->kitab?->nama_kitab ?: $jadwal->kitab_id));
    }

    protected function resolveLoggedInPengampuName($user): string
    {
        $name = trim((string) ($user->nama_lengkap ?: $user->name ?: $user->email));

        return $name !== '' ? $name : 'Pengampu';
    }

    public function monthlyRecap(Request $request)
    {
        $data = $this->getMonthlyRecapData($request);
        return view('absensi.rekap_bulanan', $data);
    }

    public function monthlyRecapMatrixDiniyah(Request $request)
    {
        return view('absensi.rekap_matrix', $this->getMonthlyMatrixRecapData($request, 'diniyah'));
    }

    public function monthlyRecapMatrixSholat(Request $request)
    {
        return view('absensi.rekap_matrix', $this->getMonthlyMatrixRecapData($request, 'sholat'));
    }

    public function exportMonthlyRecapMatrixDiniyahExcel(Request $request)
    {
        return $this->exportMonthlyRecapMatrixExcel($request, 'diniyah');
    }

    public function exportMonthlyRecapMatrixSholatExcel(Request $request)
    {
        return $this->exportMonthlyRecapMatrixExcel($request, 'sholat');
    }

    public function exportMonthlyRecapMatrixDiniyahPDF(Request $request)
    {
        return $this->exportMonthlyRecapMatrixPDF($request, 'diniyah');
    }

    public function exportMonthlyRecapMatrixSholatPDF(Request $request)
    {
        return $this->exportMonthlyRecapMatrixPDF($request, 'sholat');
    }

    protected function exportMonthlyRecapMatrixExcel(Request $request, string $type)
    {
        $data = $this->getMonthlyMatrixRecapData($request, $type);
        $filename = $this->matrixExportFilename($data, 'xlsx');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RekapMatrixExport($data), $filename);
    }

    protected function exportMonthlyRecapMatrixPDF(Request $request, string $type)
    {
        $data = $this->getMonthlyMatrixRecapData($request, $type);
        $filename = $this->matrixExportFilename($data, 'pdf');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('absensi.rekap_matrix_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    protected function matrixExportFilename(array $data, string $extension): string
    {
        $parts = [
            'rekap_matriks',
            $data['type'],
            strtolower($data['jadwalFilter'] ?: 'semua'),
            $data['kelasFilter'] ? 'kelas_' . $data['kelasFilter'] : 'semua_kelas',
            $data['jenisKelaminFilter'] ? strtolower($data['jenisKelaminFilter']) : 'semua',
            $data['month'],
        ];

        return preg_replace('/[^a-z0-9_\-\.]+/i', '_', implode('_', $parts)) . '.' . $extension;
    }

    protected function getMonthlyMatrixRecapData(Request $request, string $type): array
    {
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelasFilter = trim((string) $request->get('kelas', ''));
        $jenisKelaminFilter = ucfirst(strtolower(trim((string) $request->get('jenis_kelamin', ''))));
        if (!in_array($jenisKelaminFilter, ['Putra', 'Putri'], true)) {
            $jenisKelaminFilter = '';
        }
        $jadwalFilter = $this->normalizeRecapJadwalFilter($request->get('jadwal'));
        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $daysInMonth = $monthDate->daysInMonth;

        $allSantri = $this->getCachedDiniyahSantri($user);
        $filteredSantri = $this->filterSantriCollection($allSantri, $kelasFilter);
        if ($jenisKelaminFilter !== '') {
            $filteredSantri = $filteredSantri
                ->filter(fn ($santri) => strtolower(trim((string) ($santri->jenis_kelamin ?? ''))) === strtolower($jenisKelaminFilter))
                ->values();
        }
        $santriLookup = $this->buildSantriLookup($filteredSantri);
        $visibleSantriIds = array_keys($santriLookup);

        $query = Absensi::forMonth($month)
            ->select('id', 'timestamp', 'santri_id', 'kegiatan', 'status', 'nama_ustadz', 'petugas_id')
            ->whereIn('santri_id', $visibleSantriIds);

        if ($type === 'diniyah') {
            $query->where(function ($builder) {
                $builder->where('kegiatan', 'like', 'Ngaji%')
                    ->orWhere('kegiatan', 'like', 'Diniyah%')
                    ->orWhere('kegiatan', 'like', 'Tahfidz%');
            });
            $jadwalOptions = $this->getRecapJadwalOptions();
        } else {
            $query->where(function ($builder) {
                $builder->where('kegiatan', 'like', 'Sholat%')
                    ->orWhere('kegiatan', 'like', 'Solat%');
            });
            $jadwalOptions = $this->getSholatRecapOptions();
        }

        $attendanceData = $query->orderBy('timestamp')->get();

        $matrixRows = [];
        foreach ($filteredSantri as $santri) {
            $matrixRows[(string) $santri->id_santri] = [
                'nama' => $santri->nama,
                'kelas' => (string) $santri->kelas,
                'days' => array_fill(1, $daysInMonth, ''),
                'pengampu' => [],
            ];
        }

        foreach ($attendanceData as $row) {
            $kegiatan = Absensi::formatKegiatanLabel($row->kegiatan);
            if ($jadwalFilter !== '' && $this->normalizeRecapJadwalFilter($kegiatan) !== $jadwalFilter) {
                continue;
            }

            $santriId = (string) $row->santri_id;
            if (!isset($matrixRows[$santriId])) {
                continue;
            }

            $day = (int) $row->timestamp->format('j');
            $matrixRows[$santriId]['days'][$day] = $this->statusMatrixLabel($row->status);

            $pengampu = trim((string) ($row->nama_ustadz ?: $row->petugas_id));
            if ($pengampu !== '') {
                $matrixRows[$santriId]['pengampu'][$pengampu] = $pengampu;
            }
        }

        foreach ($matrixRows as &$row) {
            $row['pengampu'] = array_values($row['pengampu']);
        }
        unset($row);

        $pengampuList = collect($matrixRows)
            ->flatMap(fn ($row) => $row['pengampu'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'type' => $type,
            'title' => $type === 'diniyah' ? 'Rekap Matriks Diniyah' : 'Rekap Matriks Sholat',
            'headerTitle' => $jadwalFilter
                ? 'ABSENSI ' . ucwords(strtolower($jadwalFilter))
                : ($type === 'diniyah' ? 'ABSENSI KEGIATAN DINIYAH' : 'ABSENSI SHOLAT'),
            'month' => $month,
            'monthLabel' => $monthDate->locale('id')->translatedFormat('F Y'),
            'daysInMonth' => $daysInMonth,
            'kelasFilter' => $kelasFilter,
            'kelasList' => ['10', '11', '12'],
            'jenisKelaminFilter' => $jenisKelaminFilter,
            'jenisKelaminList' => ['Putra', 'Putri'],
            'jadwalFilter' => $jadwalFilter,
            'jadwalList' => $jadwalOptions,
            'pengampuList' => $pengampuList,
            'rows' => array_values($matrixRows),
        ];
    }

    protected function statusMatrixLabel(?string $status): string
    {
        return match (strtoupper(trim((string) $status))) {
            'HADIR' => 'H',
            'IZIN' => 'I',
            'SAKIT' => 'S',
            'ALPA', 'ALPHA' => 'A',
            default => '',
        };
    }

    protected function getSholatRecapOptions(): array
    {
        return ['Subuh', 'Asar', 'Maghrib', 'Isya'];
    }

    
    public function refreshMonthlyRecap(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $this->forgetMonthlyRecapCaches($month);
        
        
        return redirect()->route('absensi.rekapBulanan', [
            'month' => $month,
            'kelas' => $request->get('kelas'),
            'golongan' => $request->get('golongan'),
            'jadwal' => $request->get('jadwal'),
        ])->with('success', 'Data berhasil diperbarui!');
    }

    
    public function exportMonthlyRecapExcel(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelas = $request->get('kelas');
        $golongan = $request->get('golongan');
        $jadwal = $request->get('jadwal');
        $request->merge(['month' => $month, 'kelas' => $kelas, 'golongan' => $golongan, 'jadwal' => $jadwal]);

        
        $data = $this->getMonthlyRecapData($request);
        $summaryPerSantri = $data['summaryPerSantri'] ?? [];

        
        $monthFormatted = Carbon::parse($month)->locale('id')->format('F Y');
        $exportDate = Carbon::now()->locale('id')->format('d M Y');

        
        $excelData = [];
        $no = 1;
        $totalKegiatan = 0;
        $totalHadir = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalAlpha = 0;
        $totalSantri = 0;
        
        foreach ($summaryPerSantri as $kelasKey => $santriData) {
            
            if ($kelas && (string)$kelasKey !== (string)$kelas) continue;
            
            foreach ($santriData as $santri) {
                $persen = $santri['total'] > 0 ? round($santri['hadir'] / $santri['total'] * 100, 1) : 0;
                $excelData[] = [
                    'no' => $no++,
                    'nama' => $santri['nama'],
                    'kelas' => $kelasKey,
                    'golongan' => $santri['golongan'] ?? 'Unknown',
                    'total' => $santri['total'],
                    'hadir' => $santri['hadir'],
                    'izin' => $santri['izin'],
                    'sakit' => $santri['sakit'],
                    'alpha' => $santri['alpha'],
                    'persentase' => $persen
                ];
                
                
                $totalKegiatan += $santri['total'];
                $totalHadir += $santri['hadir'];
                $totalIzin += $santri['izin'];
                $totalSakit += $santri['sakit'];
                $totalAlpha += $santri['alpha'];
                $totalSantri++;
            }
        }

        $stats = [
            'exportDate' => $exportDate,
            'totalSantri' => $totalSantri,
            'totalKegiatan' => $totalKegiatan,
            'totalHadir' => $totalHadir,
            'totalIzin' => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlpha' => $totalAlpha
        ];

        
        $filenameParts = ['rekap'];
        if ($kelas) $filenameParts[] = 'kelas_' . $kelas;
        if ($golongan) $filenameParts[] = strtolower($golongan);
        $filenameParts[] = $month;
        $filename = implode('_', $filenameParts) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapBulananExport($excelData, $monthFormatted, $kelas, $golongan, $stats),
            $filename
        );
    }

    
    public function exportMonthlyRecapPDF(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelas = $request->get('kelas');
        $golongan = $request->get('golongan');
        $jadwal = $request->get('jadwal');
        $request->merge(['month' => $month, 'kelas' => $kelas, 'golongan' => $golongan, 'jadwal' => $jadwal]);

        
        try {
            $data = $this->getMonthlyRecapData($request);
            $summaryPerSantri = $data['summaryPerSantri'] ?? [];
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            
            $summaryPerSantri = [];
        }

        
        $pdfData = [];
        foreach ($summaryPerSantri as $kelasKey => $santriData) {
            
            if ($kelas && (string)$kelasKey !== (string)$kelas) continue;
            
            foreach ($santriData as $santri) {
                $persen = $santri['total'] > 0 ? round($santri['hadir'] / $santri['total'] * 100, 1) : 0;
                $pdfData[] = [
                    'nama' => $santri['nama'],
                    'kelas' => $kelasKey,
                    'golongan' => $santri['golongan'] ?? 'Unknown',
                    'total' => $santri['total'],
                    'hadir' => $santri['hadir'],
                    'izin' => $santri['izin'],
                    'sakit' => $santri['sakit'],
                    'alpha' => $santri['alpha'],
                    'persentase' => $persen
                ];
            }
        }

        
        \Log::info('PDF Export Data', [
            'month' => $month,
            'kelas' => $kelas,
            'golongan' => $golongan,
            'summaryPerSantri_count' => count($summaryPerSantri),
            'pdfData_count' => count($pdfData)
        ]);

        
        $monthFormatted = \Carbon\Carbon::parse($month)->locale('id')->format('F Y');

        
        $filenameParts = ['rekap'];
        if ($kelas) $filenameParts[] = 'kelas_' . $kelas;
        if ($golongan) $filenameParts[] = strtolower($golongan);
        $filenameParts[] = $month;
        $filename = implode('_', $filenameParts) . '.pdf';

        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('absensi.rekap_bulanan_pdf', [
            'data' => $pdfData,
            'month' => $monthFormatted,
            'kelas' => $kelas,
            'golongan' => $golongan,
            'monthRaw' => $month,
            'jenis' => 'Diniyah'
        ]);

        
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
    
    
    


    protected function getMonthlyRecapSholatData(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelasFilter = $request->get('kelas');

        
        $processedCacheKey = $this->getDisplayCacheKey('rekap_sholat_processed_' . $month . '_' . $user->id . '_' . ($kelasFilter ?? 'all'));
        
        
        $cachedResult = Cache::get($processedCacheKey);
        if ($cachedResult !== null) {
            return $cachedResult;
        }

        
        $allSantri = $this->getCachedVisibleSantri($user);
        $filteredSantri = $this->filterSantriCollection($allSantri, $kelasFilter);
        $santriLookup = $this->buildSantriLookup($filteredSantri);
        $allVisibleSantriIds = $allSantri->pluck('id_santri')->all();

        $visibleSantriIds = array_keys($santriLookup);
        if (empty($visibleSantriIds)) {
            return [
                'month' => $month,
                'kelasFilter' => $kelasFilter,
                'kelasList' => ['10', '11', '12'],
                'summaryPerSantri' => [],
            ];
        }

        $rawCacheKey = 'rekap_sholat_raw_' . $month . '_' . $user->id;
        $attendanceData = Cache::remember($rawCacheKey, 1800, function () use ($month, $allVisibleSantriIds) {
            return Absensi::forMonth($month)
                ->select('id', 'timestamp', 'created_at', 'santri_id', 'kegiatan', 'status')
                ->whereIn('santri_id', $allVisibleSantriIds)
                ->where(function ($query) {
                    $query->where('kegiatan', 'like', 'Sholat%')
                        ->orWhere('kegiatan', 'like', 'Solat%');
                })
                ->orderBy('timestamp')
                ->get();
        });

        
        $summaryPerSantri = [];
        foreach ($attendanceData as $row) {
            $santriId = $row->santri_id;
            $status = strtoupper(trim($row->status));
            
            
            $santriInfo = $santriLookup[$santriId] ?? null;
            if (!$santriInfo) continue;
            
            $kelas = $santriInfo['kelas'];
            if (!isset($summaryPerSantri[$kelas])) {
                $summaryPerSantri[$kelas] = [];
            }
            if (!isset($summaryPerSantri[$kelas][$santriId])) {
                $summaryPerSantri[$kelas][$santriId] = $this->createAttendanceSummaryRow([
                    'nama' => $santriLookup[$santriId]['nama'] ?? $santriId,
                ]);
            }

            $tanggalAbsen = $row->timestamp->format('Y-m-d');

            
            if (!isset($summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen])) {
                $summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen] = $this->createAttendanceDetailRow();
            }

            
            $kegiatan = Absensi::formatKegiatanLabel($row->kegiatan);
            $jamAbsen = Absensi::resolveAttendanceTime($row->timestamp, $row->created_at);
            if (!isset($summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen]['kegiatan'][$status])) {
                $summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen]['kegiatan'][$status] = [];
            }
            $dailySummary = &$summaryPerSantri[$kelas][$santriId]['detail_per_hari'][$tanggalAbsen];
            $summaryRow = &$summaryPerSantri[$kelas][$santriId];

            $this->addUniqueSummaryValue($dailySummary['kegiatan'][$status], $kegiatan);
            $this->addUniqueSummaryValue($dailySummary['jam'], $jamAbsen);
            $this->incrementAttendanceCounters($summaryRow, $dailySummary, $status);

            unset($dailySummary, $summaryRow);
        }

        $this->finalizeAttendanceSummaryDetails($summaryPerSantri);

        
        ksort($summaryPerSantri);
        foreach ($summaryPerSantri as $kelas => &$santriData) {
            uasort($santriData, function ($a, $b) {
                return strcmp($a['nama'], $b['nama']);
            });
        }

        
        $allKelasList = ['10', '11', '12'];
        $existingKelas = array_keys($summaryPerSantri);
        $kelasList = array_unique(array_merge($allKelasList, $existingKelas));
        sort($kelasList);

        $result = [
            'month' => $month,
            'kelasFilter' => $kelasFilter,
            'kelasList' => $kelasList,
            'summaryPerSantri' => $summaryPerSantri,
        ];
        
        
        Cache::put($processedCacheKey, $result, now()->addMinutes(10));
        
        return $result;
    }

    public function monthlyRecapSholat(Request $request)
    {
        $data = $this->getMonthlyRecapSholatData($request);
        return view('absensi.rekap_bulanan_sholat', $data);
    }

    
    public function refreshMonthlyRecapSholat(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $this->forgetMonthlyRecapCaches($month);
        
        return redirect()->route('absensi.rekapBulananSholat', [
            'month' => $month,
            'kelas' => $request->get('kelas')
        ])->with('success', 'Data berhasil diperbarui!');
    }

    
    public function exportMonthlyRecapSholatExcel(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelas = $request->get('kelas');
        $request->merge(['month' => $month, 'kelas' => $kelas]);

        $data = $this->getMonthlyRecapSholatData($request);
        $summaryPerSantri = $data['summaryPerSantri'] ?? [];

        $monthFormatted = Carbon::parse($month)->locale('id')->format('F Y');
        $exportDate = Carbon::now()->locale('id')->format('d M Y');

        $excelData = [];
        $no = 1;
        $totalKegiatan = 0;
        $totalHadir = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalAlpha = 0;
        $totalSantri = 0;
        
        foreach ($summaryPerSantri as $kelasKey => $santriData) {
            if ($kelas && (string)$kelasKey !== (string)$kelas) continue;
            
            foreach ($santriData as $santri) {
                $persen = $santri['total'] > 0 ? round($santri['hadir'] / $santri['total'] * 100, 1) : 0;
                $excelData[] = [
                    'no' => $no++,
                    'nama' => $santri['nama'],
                    'kelas' => $kelasKey,
                    'total' => $santri['total'],
                    'hadir' => $santri['hadir'],
                    'izin' => $santri['izin'],
                    'sakit' => $santri['sakit'],
                    'alpha' => $santri['alpha'],
                    'persentase' => $persen
                ];
                
                $totalKegiatan += $santri['total'];
                $totalHadir += $santri['hadir'];
                $totalIzin += $santri['izin'];
                $totalSakit += $santri['sakit'];
                $totalAlpha += $santri['alpha'];
                $totalSantri++;
            }
        }

        $stats = [
            'exportDate' => $exportDate,
            'totalSantri' => $totalSantri,
            'totalKegiatan' => $totalKegiatan,
            'totalHadir' => $totalHadir,
            'totalIzin' => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlpha' => $totalAlpha
        ];

        $filenameParts = ['rekap_sholat'];
        if ($kelas) $filenameParts[] = 'kelas_' . $kelas;
        $filenameParts[] = $month;
        $filename = implode('_', $filenameParts) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapBulananSholatExport($excelData, $monthFormatted, $kelas, $stats),
            $filename
        );
    }

    
    public function exportMonthlyRecapSholatPDF(Request $request)
    {
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $kelas = $request->get('kelas');
        $request->merge(['month' => $month, 'kelas' => $kelas]);

        try {
            $data = $this->getMonthlyRecapSholatData($request);
            $summaryPerSantri = $data['summaryPerSantri'] ?? [];
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            $summaryPerSantri = [];
        }

        $pdfData = [];
        foreach ($summaryPerSantri as $kelasKey => $santriData) {
            if ($kelas && (string)$kelasKey !== (string)$kelas) continue;
            
            foreach ($santriData as $santri) {
                $persen = $santri['total'] > 0 ? round($santri['hadir'] / $santri['total'] * 100, 1) : 0;
                $pdfData[] = [
                    'nama' => $santri['nama'],
                    'kelas' => $kelasKey,
                    'total' => $santri['total'],
                    'hadir' => $santri['hadir'],
                    'izin' => $santri['izin'],
                    'sakit' => $santri['sakit'],
                    'alpha' => $santri['alpha'],
                    'persentase' => $persen
                ];
            }
        }

        $monthFormatted = \Carbon\Carbon::parse($month)->locale('id')->format('F Y');

        $filenameParts = ['rekap_sholat'];
        if ($kelas) $filenameParts[] = 'kelas_' . $kelas;
        $filenameParts[] = $month;
        $filename = implode('_', $filenameParts) . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('absensi.rekap_bulanan_sholat_pdf', [
            'data' => $pdfData,
            'month' => $monthFormatted,
            'kelas' => $kelas,
            'monthRaw' => $month
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    
    public function editAttendance(Request $request)
    {
        
        if (Auth::user()->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Admin yang dapat mengedit kehadiran.'
            ], 403);
        }

        $request->validate([
            'santri_id' => 'required|string',
            'tanggal' => 'required|date',
            'kegiatan_type' => 'required|in:diniyah,sholat', 
            'new_status' => 'required|in:HADIR,IZIN,SAKIT,ALPHA'
        ]);

        try {
            $santriId = $request->santri_id;
            $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');
            $kegiatanType = $request->kegiatan_type;
            $newStatus = strtoupper($request->new_status);

            $query = Absensi::query()
                ->where('santri_id', $santriId)
                ->whereBetween('timestamp', $this->buildDayRange($tanggal));

            if ($kegiatanType === 'diniyah') {
                $query->where('kegiatan', 'like', 'Ngaji%');
            } else {
                $query->where(function ($builder) {
                    $builder->where('kegiatan', 'like', 'Sholat%')
                        ->orWhere('kegiatan', 'like', 'Solat%');
                });
            }

            $updatedRows = $query->update(['status' => $newStatus]);

            if ($updatedRows > 0) {
                $this->clearAbsensiCache();

                Log::info('Attendance edited by Admin', [
                    'admin' => Auth::user()->email,
                    'santri_id' => $santriId,
                    'tanggal' => $tanggal,
                    'kegiatan_type' => $kegiatanType,
                    'new_status' => $newStatus,
                    'updated_rows' => $updatedRows
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Status kehadiran berhasil diperbarui di database.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data kehadiran yang sesuai tidak ditemukan.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Edit Attendance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kehadiran: ' . $e->getMessage()
            ], 500);
        }
    }
}
