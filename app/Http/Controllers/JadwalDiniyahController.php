<?php

namespace App\Http\Controllers;

use App\Models\JadwalDiniyah;
use App\Models\KitabDiniyah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalDiniyahController extends Controller
{
    public function index(Request $request)
    {
        [$tahunAjaran, $semester] = $this->resolvePeriodFilters($request);
        $formOptions = $this->buildFormOptions($tahunAjaran);
        $perPage = 10;

        $jadwalList = JadwalDiniyah::query()
            ->with('kitab:id_kitab,nama_kitab,kelas_kitab,pengampu_golongan')
            ->forPeriod($tahunAjaran, $semester)
            ->orderByDesc('is_active')
            ->orderBy('kelas')
            ->orderBy('golongan')
            ->orderBy('jam_mulai')
            ->orderBy('nama_kegiatan')
            ->paginate($perPage)
            ->withQueryString();

        $kitabOptions = KitabDiniyah::query()
            ->select('id_kitab', 'nama_kitab', 'kelas_kitab', 'pengampu_golongan')
            ->orderBy('nama_kitab')
            ->orderBy('kelas_kitab')
            ->get();

        $activePeriod = JadwalDiniyah::query()
            ->active()
            ->select('tahun_ajaran', 'semester')
            ->first();

        $periodSummary = JadwalDiniyah::query()
            ->forPeriod($tahunAjaran, $semester)
            ->selectRaw('COUNT(*) as total_jadwal')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as total_active')
            ->first();

        return view('jadwal_diniyah.index', array_merge([
            'jadwalList' => $jadwalList,
            'kitabOptions' => $kitabOptions,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'availableYears' => $formOptions['academicYearOptions'],
            'availableSemesters' => ['Ganjil', 'Genap'],
            'activePeriod' => $activePeriod,
            'periodSummary' => $periodSummary,
            'suggestedSourceYear' => JadwalDiniyah::previousAcademicYear($tahunAjaran),
            'perPage' => $perPage,
        ], $formOptions));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSchedule($request);
        $data['is_active'] = $this->isPeriodActive($data['tahun_ajaran'], $data['semester']);

        if ($this->duplicateScheduleExists($data)) {
            return $this->redirectToIndex($data['tahun_ajaran'], $data['semester'])
                ->with('error', 'Jadwal yang sama sudah ada pada periode tersebut.');
        }

        JadwalDiniyah::create($data);
        $this->clearJadwalCaches();

        return $this->redirectToIndex($data['tahun_ajaran'], $data['semester'])
            ->with('success', 'Jadwal diniyah berhasil ditambahkan.');
    }

    public function edit(JadwalDiniyah $jadwalDiniyah, Request $request)
    {
        $selectedYear = trim((string) $request->query('tahun_ajaran', $jadwalDiniyah->tahun_ajaran));
        $formOptions = $this->buildFormOptions($selectedYear);

        $kitabOptions = KitabDiniyah::query()
            ->select('id_kitab', 'nama_kitab', 'kelas_kitab', 'pengampu_golongan')
            ->orderBy('nama_kitab')
            ->orderBy('kelas_kitab')
            ->get();

        return view('jadwal_diniyah.edit', array_merge([
            'jadwalDiniyah' => $jadwalDiniyah,
            'kitabOptions' => $kitabOptions,
            'availableSemesters' => ['Ganjil', 'Genap'],
            'returnFilters' => [
                'tahun_ajaran' => $selectedYear,
                'semester' => trim((string) $request->query('semester', $jadwalDiniyah->semester)),
                'page' => (int) $request->query('page', 1),
            ],
        ], $formOptions));
    }

    public function update(Request $request, JadwalDiniyah $jadwalDiniyah): RedirectResponse
    {
        $data = $this->validateSchedule($request);
        $data['is_active'] = $this->isPeriodActive($data['tahun_ajaran'], $data['semester']);

        if ($this->duplicateScheduleExists($data, $jadwalDiniyah->id)) {
            return back()
                ->withErrors(['nama_kegiatan' => 'Jadwal yang sama sudah ada pada periode tersebut.'])
                ->withInput();
        }

        $jadwalDiniyah->update($data);
        $this->clearJadwalCaches();

        return $this->redirectToIndex($data['tahun_ajaran'], $data['semester'])
            ->with('success', 'Jadwal diniyah berhasil diperbarui.');
    }

    public function destroy(JadwalDiniyah $jadwalDiniyah, Request $request): RedirectResponse
    {
        $filters = $this->resolvePeriodFilters($request, $jadwalDiniyah->tahun_ajaran, $jadwalDiniyah->semester);

        $jadwalDiniyah->delete();
        $this->clearJadwalCaches();

        return $this->redirectToIndex($filters[0], $filters[1])
            ->with('success', 'Jadwal diniyah berhasil dihapus.');
    }

    public function duplicate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'source_semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'target_tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'target_semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
        ]);

        if (
            $validated['source_tahun_ajaran'] === $validated['target_tahun_ajaran']
            && $validated['source_semester'] === $validated['target_semester']
        ) {
            return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
                ->with('error', 'Sumber dan target duplikasi tidak boleh sama.');
        }

        $sourceRows = JadwalDiniyah::query()
            ->forPeriod($validated['source_tahun_ajaran'], $validated['source_semester'])
            ->get();

        if ($sourceRows->isEmpty()) {
            return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
                ->with('error', 'Tidak ada jadwal pada periode sumber untuk disalin.');
        }

        $created = 0;
        $skipped = 0;

        foreach ($sourceRows as $row) {
            $payload = [
                'kitab_id' => $row->kitab_id,
                'nama_kegiatan' => $row->nama_kegiatan,
                'tahun_ajaran' => $validated['target_tahun_ajaran'],
                'semester' => $validated['target_semester'],
                'kelas' => $row->kelas,
                'golongan' => $row->golongan,
                'pengampu' => $row->pengampu,
                'keterangan_waktu' => $row->keterangan_waktu,
                'jam_mulai' => optional($row->jam_mulai)->format('H:i:s'),
                'jam_selesai' => optional($row->jam_selesai)->format('H:i:s'),
                'is_active' => $this->isPeriodActive($validated['target_tahun_ajaran'], $validated['target_semester']),
            ];

            if ($this->duplicateScheduleExists($payload)) {
                $skipped++;
                continue;
            }

            JadwalDiniyah::create($payload);
            $created++;
        }

        if ($request->boolean('activate_after_duplicate') && ($created > 0 || JadwalDiniyah::query()->forPeriod($validated['target_tahun_ajaran'], $validated['target_semester'])->exists())) {
            $this->activatePeriodSchedules($validated['target_tahun_ajaran'], $validated['target_semester']);
        } else {
            $this->clearJadwalCaches();
        }

        return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
            ->with('success', $created . ' jadwal berhasil disalin, ' . $skipped . ' dilewati karena sudah ada.');
    }

    public function activate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
        ]);

        $affected = $this->activatePeriodSchedules($validated['tahun_ajaran'], $validated['semester']);

        if ($affected === 0) {
            return $this->redirectToIndex($validated['tahun_ajaran'], $validated['semester'])
                ->with('error', 'Belum ada jadwal pada periode ini untuk diaktifkan.');
        }

        return $this->redirectToIndex($validated['tahun_ajaran'], $validated['semester'])
            ->with('success', 'Jadwal aktif berhasil dipindahkan ke ' . $validated['tahun_ajaran'] . ' semester ' . $validated['semester'] . '.');
    }

    public function copyAssignments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'source_semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'target_tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'target_semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'overwrite_existing' => ['nullable', 'boolean'],
        ]);

        if (
            $validated['source_tahun_ajaran'] === $validated['target_tahun_ajaran']
            && $validated['source_semester'] === $validated['target_semester']
        ) {
            return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
                ->with('error', 'Periode sumber dan target tidak boleh sama saat menyalin pengampu dan jam.');
        }

        $sourceRows = JadwalDiniyah::query()
            ->forPeriod($validated['source_tahun_ajaran'], $validated['source_semester'])
            ->get();

        $targetRows = JadwalDiniyah::query()
            ->forPeriod($validated['target_tahun_ajaran'], $validated['target_semester'])
            ->get();

        if ($sourceRows->isEmpty()) {
            return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
                ->with('error', 'Periode sumber belum punya jadwal untuk disalin.');
        }

        if ($targetRows->isEmpty()) {
            return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
                ->with('error', 'Periode target belum punya jadwal. Tambahkan atau duplikasi jadwal dulu.');
        }

        $overwriteExisting = (bool) ($validated['overwrite_existing'] ?? false);
        $sourceByKitab = [];
        $sourceByFallback = [];

        foreach ($sourceRows as $row) {
            if ($row->kitab_id) {
                $sourceByKitab[$this->normalizeMatchText($row->kitab_id)] = $row;
            }

            $sourceByFallback[$this->buildFallbackMatchKey($row)] = $row;
        }

        $updated = 0;
        $matched = 0;
        $skipped = 0;

        foreach ($targetRows as $targetRow) {
            $sourceRow = null;

            if ($targetRow->kitab_id) {
                $sourceRow = $sourceByKitab[$this->normalizeMatchText($targetRow->kitab_id)] ?? null;
            }

            if (!$sourceRow) {
                $sourceRow = $sourceByFallback[$this->buildFallbackMatchKey($targetRow)] ?? null;
            }

            if (!$sourceRow) {
                $skipped++;
                continue;
            }

            $matched++;

            $targetHasAssignments = $this->rowHasAssignments($targetRow);
            if ($targetHasAssignments && !$overwriteExisting) {
                $skipped++;
                continue;
            }

            $changes = [
                'pengampu' => $sourceRow->pengampu,
                'keterangan_waktu' => $sourceRow->keterangan_waktu,
                'jam_mulai' => $this->formatTimeForDatabase($sourceRow->jam_mulai),
                'jam_selesai' => $this->formatTimeForDatabase($sourceRow->jam_selesai),
            ];

            if (!$this->assignmentsDiffer($targetRow, $changes)) {
                $skipped++;
                continue;
            }

            $targetRow->update($changes);
            $updated++;
        }

        if ($updated > 0) {
            $this->clearJadwalCaches();
        }

        return $this->redirectToIndex($validated['target_tahun_ajaran'], $validated['target_semester'])
            ->with('success', $updated . ' jadwal diperbarui, ' . $matched . ' cocok ditemukan, ' . $skipped . ' dilewati.');
    }

    protected function activatePeriodSchedules(string $tahunAjaran, string $semester): int
    {
        $affected = 0;

        DB::transaction(function () use ($tahunAjaran, $semester, &$affected) {
            JadwalDiniyah::query()->where('is_active', true)->update(['is_active' => false]);
            $affected = JadwalDiniyah::query()
                ->forPeriod($tahunAjaran, $semester)
                ->update(['is_active' => true]);
        });

        $this->clearJadwalCaches();

        return $affected;
    }

    protected function validateSchedule(Request $request): array
    {
        $validated = $request->validate([
            'kitab_id' => ['nullable', 'string', 'max:255'],
            'nama_kegiatan' => ['nullable', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'kelas' => ['nullable', 'string', 'max:50'],
            'golongan' => ['nullable', 'string', 'max:100'],
            'pengampu' => ['nullable', 'string', 'max:255'],
            'keterangan_waktu' => ['nullable', 'string', 'max:255'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i'],
        ]);

        $validated['kitab_id'] = $this->normalizeOptionalValue($validated['kitab_id'] ?? null);
        $validated['nama_kegiatan'] = $this->normalizeOptionalValue($validated['nama_kegiatan'] ?? null);

        if ($validated['kitab_id'] !== null) {
            $kitab = KitabDiniyah::query()
                ->select('id_kitab', 'nama_kitab')
                ->where('id_kitab', $validated['kitab_id'])
                ->first();

            if (!$kitab) {
                throw ValidationException::withMessages([
                    'kitab_id' => 'Kitab master yang dipilih tidak ditemukan.',
                ]);
            }

            if ($validated['nama_kegiatan'] === null) {
                $validated['nama_kegiatan'] = trim((string) $kitab->nama_kitab);
            }
        }

        if ($validated['nama_kegiatan'] === null) {
            throw ValidationException::withMessages([
                'nama_kegiatan' => 'Nama kegiatan wajib diisi.',
            ]);
        }

        $validated['kelas'] = $this->normalizeClassValue($validated['kelas'] ?? null);
        $validated['golongan'] = $this->normalizeOptionalValue($validated['golongan'] ?? null);
        $validated['golongan'] = $validated['golongan'] !== null ? strtoupper($validated['golongan']) : null;
        $validated['pengampu'] = $this->normalizeOptionalValue($validated['pengampu'] ?? null);
        $validated['keterangan_waktu'] = $this->normalizeOptionalValue($validated['keterangan_waktu'] ?? null);
        $validated['jam_mulai'] = $this->normalizeTimeValue($validated['jam_mulai'] ?? null);
        $validated['jam_selesai'] = $this->normalizeTimeValue($validated['jam_selesai'] ?? null);

        if ($validated['jam_mulai'] !== null && $validated['jam_selesai'] !== null && $validated['jam_selesai'] < $validated['jam_mulai']) {
            throw ValidationException::withMessages([
                'jam_selesai' => 'Jam selesai tidak boleh lebih awal dari jam mulai.',
            ]);
        }

        return $validated;
    }

    protected function duplicateScheduleExists(array $data, ?int $ignoreId = null): bool
    {
        $query = JadwalDiniyah::query()
            ->where('tahun_ajaran', $data['tahun_ajaran'])
            ->where('semester', $data['semester'])
            ->where('nama_kegiatan', $data['nama_kegiatan'])
            ->where('kelas', $data['kelas'])
            ->where('golongan', $data['golongan'])
            ->where('pengampu', $data['pengampu'])
            ->where('keterangan_waktu', $data['keterangan_waktu'])
            ->where('jam_mulai', $data['jam_mulai'])
            ->where('jam_selesai', $data['jam_selesai']);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    protected function resolvePeriodFilters(Request $request, ?string $defaultYear = null, ?string $defaultSemester = null): array
    {
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', $defaultYear ?: JadwalDiniyah::currentAcademicYear()));
        $semester = trim((string) $request->query('semester', $defaultSemester ?: JadwalDiniyah::currentSemester()));

        if (!preg_match('/^\d{4}\/\d{4}$/', $tahunAjaran)) {
            $tahunAjaran = JadwalDiniyah::currentAcademicYear();
        }

        if (!in_array($semester, ['Ganjil', 'Genap'], true)) {
            $semester = JadwalDiniyah::currentSemester();
        }

        return [$tahunAjaran, $semester];
    }

    protected function redirectToIndex(string $tahunAjaran, string $semester): RedirectResponse
    {
        return redirect()->route('jadwal_diniyah.index', [
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ]);
    }

    protected function clearJadwalCaches(): void
    {
        Cache::forget('jadwal_diniyah_active_list');
        Cache::forget('jadwal_diniyah_active_period');
        Cache::forget('jadwal_diniyah_summary');
    }

    protected function buildFormOptions(?string $selectedYear = null): array
    {
        return [
            'academicYearOptions' => $this->buildAcademicYearOptions($selectedYear),
            'namaKegiatanOptions' => $this->buildNamaKegiatanOptions(),
            'pengampuOptions' => $this->buildPengampuOptions(),
            'keteranganWaktuOptions' => $this->buildKeteranganWaktuOptions(),
        ];
    }

    protected function buildAcademicYearOptions(?string $selectedYear = null): array
    {
        $currentAcademicYear = JadwalDiniyah::currentAcademicYear();
        $currentStartYear = $this->extractAcademicYearStart($currentAcademicYear) ?? (int) now('Asia/Jakarta')->format('Y');
        $generatedYears = [];

        for ($offset = -1; $offset <= 3; $offset++) {
            $startYear = $currentStartYear + $offset;
            $generatedYears[] = $startYear . '/' . ($startYear + 1);
        }

        $storedYears = JadwalDiniyah::query()
            ->select('tahun_ajaran')
            ->distinct()
            ->pluck('tahun_ajaran')
            ->all();

        $options = $this->normalizeDistinctOptions(array_merge(
            $generatedYears,
            $storedYears,
            array_filter([$selectedYear])
        ));

        usort($options, function (string $left, string $right): int {
            return ($this->extractAcademicYearStart($right) ?? 0) <=> ($this->extractAcademicYearStart($left) ?? 0);
        });

        return $options;
    }

    protected function buildNamaKegiatanOptions(): array
    {
        $masterNames = KitabDiniyah::query()
            ->whereNotNull('nama_kitab')
            ->orderBy('nama_kitab')
            ->pluck('nama_kitab')
            ->all();

        $scheduleNames = JadwalDiniyah::query()
            ->whereNotNull('nama_kegiatan')
            ->distinct()
            ->orderBy('nama_kegiatan')
            ->pluck('nama_kegiatan')
            ->all();

        return $this->sortOptionValues($this->normalizeDistinctOptions(array_merge($masterNames, $scheduleNames)));
    }

    protected function buildPengampuOptions(): array
    {
        $userNames = User::query()
            ->whereIn('role', ['Pembina', 'Ustadz Pengajar'])
            ->get()
            ->map(function (User $user): string {
                return trim((string) ($user->nama_lengkap ?: $user->name));
            })
            ->all();

        $schedulePengampu = JadwalDiniyah::query()
            ->whereNotNull('pengampu')
            ->distinct()
            ->orderBy('pengampu')
            ->pluck('pengampu')
            ->filter(function (?string $value): bool {
                return !$this->looksLikeTimeDescription($value);
            })
            ->values()
            ->all();

        return $this->sortOptionValues($this->normalizeDistinctOptions(array_merge($userNames, $schedulePengampu)));
    }

    protected function buildKeteranganWaktuOptions(): array
    {
        $defaultOptions = [
            'Diniyah Sore',
            'Diniyah Malam',
            'Ngaji Tahfidz',
            '2 Waktu',
        ];

        $scheduleTimes = JadwalDiniyah::query()
            ->whereNotNull('keterangan_waktu')
            ->distinct()
            ->orderBy('keterangan_waktu')
            ->pluck('keterangan_waktu')
            ->all();

        $masterTimes = KitabDiniyah::query()
            ->select('pengampu_golongan')
            ->get()
            ->map(function (KitabDiniyah $kitab): ?string {
                return $kitab->waktu_ringkas;
            })
            ->filter()
            ->values()
            ->all();

        $legacyTimeLabels = JadwalDiniyah::query()
            ->whereNotNull('pengampu')
            ->distinct()
            ->pluck('pengampu')
            ->filter(function (?string $value): bool {
                return $this->looksLikeTimeDescription($value);
            })
            ->values()
            ->all();

        $extraOptions = $this->sortOptionValues($this->normalizeDistinctOptions(array_merge(
            $scheduleTimes,
            $masterTimes,
            $legacyTimeLabels
        )));

        return $this->normalizeDistinctOptions(array_merge($defaultOptions, $extraOptions));
    }

    protected function normalizeOptionalValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeClassValue(?string $value): ?string
    {
        $value = $this->normalizeOptionalValue($value);

        if ($value === null) {
            return null;
        }

        if (strtoupper($value) === 'ALL') {
            return 'ALL';
        }

        if (preg_match('/\b(10|11|12)\b/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    protected function normalizeTimeValue(?string $value): ?string
    {
        $value = $this->normalizeOptionalValue($value);

        return $value === null ? null : $value . ':00';
    }

    protected function isPeriodActive(string $tahunAjaran, string $semester): bool
    {
        return JadwalDiniyah::query()
            ->active()
            ->forPeriod($tahunAjaran, $semester)
            ->exists();
    }

    protected function buildFallbackMatchKey(JadwalDiniyah $row): string
    {
        return implode('|', [
            $this->normalizeMatchText($row->nama_kegiatan),
            $this->normalizeMatchText($row->kelas),
            $this->normalizeMatchText($row->golongan),
        ]);
    }

    protected function normalizeMatchText(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    protected function rowHasAssignments(JadwalDiniyah $row): bool
    {
        return $this->normalizeOptionalValue($row->pengampu) !== null
            || $this->normalizeOptionalValue($row->keterangan_waktu) !== null
            || $this->formatTimeForDatabase($row->jam_mulai) !== null
            || $this->formatTimeForDatabase($row->jam_selesai) !== null;
    }

    protected function formatTimeForDatabase($time): ?string
    {
        if ($time instanceof \Carbon\CarbonInterface) {
            return $time->format('H:i:s');
        }

        $time = $this->normalizeOptionalValue(is_string($time) ? $time : null);

        return $time;
    }

    protected function assignmentsDiffer(JadwalDiniyah $row, array $changes): bool
    {
        return $this->normalizeOptionalValue($row->pengampu) !== $this->normalizeOptionalValue($changes['pengampu'] ?? null)
            || $this->normalizeOptionalValue($row->keterangan_waktu) !== $this->normalizeOptionalValue($changes['keterangan_waktu'] ?? null)
            || $this->formatTimeForDatabase($row->jam_mulai) !== $this->formatTimeForDatabase($changes['jam_mulai'] ?? null)
            || $this->formatTimeForDatabase($row->jam_selesai) !== $this->formatTimeForDatabase($changes['jam_selesai'] ?? null);
    }

    protected function normalizeDistinctOptions(array $values): array
    {
        $normalized = [];
        $seen = [];

        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $lookupKey = strtoupper($value);
            if (isset($seen[$lookupKey])) {
                continue;
            }

            $seen[$lookupKey] = true;
            $normalized[] = $value;
        }

        return $normalized;
    }

    protected function sortOptionValues(array $values): array
    {
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        return $values;
    }

    protected function looksLikeTimeDescription(?string $value): bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return false;
        }

        return (bool) preg_match('/\b(DINIYAH|TAHFIDZ|NGAJI|SORE|MALAM|PAGI|SIANG|SUBUH|ASAR|MAGHRIB|ISYA|WAKTU)\b/i', $value);
    }

    protected function extractAcademicYearStart(?string $value): ?int
    {
        if (!preg_match('/^(\d{4})\/(\d{4})$/', trim((string) $value), $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
