@php
    $schedule = $jadwalDiniyah ?? null;
    $selectedKitab = old('kitab_id', $schedule?->kitab_id);
    $selectedNama = old('nama_kegiatan', $schedule?->nama_kegiatan);
    $selectedTahunAjaran = old('tahun_ajaran', $schedule?->tahun_ajaran ?? ($tahunAjaran ?? \App\Models\JadwalDiniyah::currentAcademicYear()));
    $selectedSemester = old('semester', $schedule?->semester ?? ($semester ?? \App\Models\JadwalDiniyah::currentSemester()));
    $selectedKelas = old('kelas', $schedule?->kelas);
    $selectedGolongan = old('golongan', $schedule?->golongan);
    $selectedPengampu = old('pengampu', $schedule?->pengampu);
    $selectedKeteranganWaktu = old('keterangan_waktu', $schedule?->keterangan_waktu);
    $selectedJamMulai = old('jam_mulai', $schedule?->jam_mulai?->format('H:i'));
    $selectedJamSelesai = old('jam_selesai', $schedule?->jam_selesai?->format('H:i'));
    $isAddScheduleForm = ($formContext ?? '') === 'add_schedule';
    $academicYearOptions = array_values(array_filter($academicYearOptions ?? []));
    $namaKegiatanOptions = array_values(array_filter($namaKegiatanOptions ?? []));
    $pengampuOptions = array_values(array_filter($pengampuOptions ?? []));
    $keteranganWaktuOptions = array_values(array_filter($keteranganWaktuOptions ?? []));

    if ($selectedTahunAjaran && !in_array($selectedTahunAjaran, $academicYearOptions, true)) {
        $academicYearOptions[] = $selectedTahunAjaran;
    }

    if ($selectedNama && !in_array($selectedNama, $namaKegiatanOptions, true)) {
        $namaKegiatanOptions[] = $selectedNama;
    }

    if ($selectedPengampu && !in_array($selectedPengampu, $pengampuOptions, true)) {
        $pengampuOptions[] = $selectedPengampu;
    }

    if ($selectedKeteranganWaktu && !in_array($selectedKeteranganWaktu, $keteranganWaktuOptions, true)) {
        $keteranganWaktuOptions[] = $selectedKeteranganWaktu;
    }
@endphp

<form method="POST" action="{{ $formAction }}" class="schedule-form" data-sync-kitabs>
    @csrf
    @if(($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif
    @if(!empty($formContext))
        <input type="hidden" name="form_context" value="{{ $formContext }}">
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="kitab_id" class="form-label fw-bold">Kitab Master</label>
            <select name="kitab_id" id="kitab_id" class="form-select">
                <option value="">Pilih dari master kitab</option>
                @foreach($kitabOptions as $kitabOption)
                    <option value="{{ $kitabOption->id_kitab }}"
                            data-kit-nama="{{ $kitabOption->nama_kitab }}"
                            data-kit-waktu="{{ $kitabOption->waktu_ringkas }}"
                            {{ $selectedKitab === $kitabOption->id_kitab ? 'selected' : '' }}>
                        {{ $kitabOption->master_label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="nama_kegiatan" class="form-label fw-bold">Nama Kitab/Kegiatan</label>
            @if($isAddScheduleForm)
                <input type="text"
                       name="nama_kegiatan"
                       id="nama_kegiatan"
                       class="form-control"
                       value="{{ $selectedNama }}"
                       placeholder="Tulis nama kitab atau kegiatan"
                       required>
            @else
                <select name="nama_kegiatan" id="nama_kegiatan" class="form-select" required>
                    <option value="">Pilih nama kitab/kegiatan</option>
                    @foreach($namaKegiatanOptions as $namaKegiatanOption)
                        <option value="{{ $namaKegiatanOption }}" {{ $selectedNama === $namaKegiatanOption ? 'selected' : '' }}>
                            {{ $namaKegiatanOption }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="col-md-4">
            <label for="tahun_ajaran" class="form-label fw-bold">Tahun Ajaran</label>
            <select name="tahun_ajaran" id="tahun_ajaran" class="form-select" required>
                @foreach($academicYearOptions as $academicYearOption)
                    <option value="{{ $academicYearOption }}" {{ $selectedTahunAjaran === $academicYearOption ? 'selected' : '' }}>
                        {{ $academicYearOption }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="semester" class="form-label fw-bold">Semester</label>
            <select name="semester" id="semester" class="form-select" required>
                @foreach($availableSemesters as $semesterOption)
                    <option value="{{ $semesterOption }}" {{ $selectedSemester === $semesterOption ? 'selected' : '' }}>
                        {{ $semesterOption }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="kelas" class="form-label fw-bold">Kelas</label>
            <select name="kelas" id="kelas" class="form-select">
                <option value="">Semua kelas yang cocok</option>
                @foreach(['ALL' => 'Semua/Umum', '10' => 'Kelas 10', '11' => 'Kelas 11', '12' => 'Kelas 12'] as $kelasValue => $kelasLabel)
                    <option value="{{ $kelasValue }}" {{ (string) $selectedKelas === (string) $kelasValue ? 'selected' : '' }}>
                        {{ $kelasLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="golongan" class="form-label fw-bold">Golongan</label>
            <select name="golongan" id="golongan" class="form-select">
                <option value="">Semua golongan yang cocok</option>
                @foreach(['BILINGUAL', 'TAHFIDZ', 'PUTRA', 'PUTRI', 'ALL'] as $golonganOption)
                    <option value="{{ $golonganOption }}" {{ strtoupper((string) $selectedGolongan) === $golonganOption ? 'selected' : '' }}>
                        {{ $golonganOption }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="pengampu" class="form-label fw-bold">Pengampu</label>
            @if($isAddScheduleForm)
                <input type="text"
                       name="pengampu"
                       id="pengampu"
                       class="form-control"
                       value="{{ $selectedPengampu }}"
                       placeholder="Tulis nama pengampu">
            @else
                <select name="pengampu" id="pengampu" class="form-select">
                    <option value="">Pilih pengampu</option>
                    @foreach($pengampuOptions as $pengampuOption)
                        <option value="{{ $pengampuOption }}" {{ $selectedPengampu === $pengampuOption ? 'selected' : '' }}>
                            {{ $pengampuOption }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="col-md-4">
            <label for="keterangan_waktu" class="form-label fw-bold">Keterangan Waktu</label>
            <select name="keterangan_waktu" id="keterangan_waktu" class="form-select">
                <option value="">Pilih keterangan waktu</option>
                @foreach($keteranganWaktuOptions as $keteranganWaktuOption)
                    <option value="{{ $keteranganWaktuOption }}" {{ $selectedKeteranganWaktu === $keteranganWaktuOption ? 'selected' : '' }}>
                        {{ $keteranganWaktuOption }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="jam_mulai" class="form-label fw-bold">Jam Mulai</label>
            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="{{ $selectedJamMulai }}">
        </div>
        <div class="col-md-6">
            <label for="jam_selesai" class="form-label fw-bold">Jam Selesai</label>
            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="{{ $selectedJamSelesai }}">
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mt-4">
        <p class="text-muted small mb-0">
            Jadwal ini akan dipakai di presensi diniyah jika periodenya diaktifkan.
        </p>
        <div class="d-flex gap-2">
            @if(!empty($secondaryActionUrl) && !empty($secondaryActionLabel))
                <a href="{{ $secondaryActionUrl }}" class="btn btn-outline-secondary">
                    {{ $secondaryActionLabel }}
                </a>
            @endif
            <button type="submit" class="btn btn-success">
                {{ $submitLabel }}
            </button>
        </div>
    </div>
</form>

<script>
    (function() {
        const forms = document.querySelectorAll('[data-sync-kitabs]');

        forms.forEach(function(form) {
            const kitabSelect = form.querySelector('select[name="kitab_id"]');
            const namaField = form.querySelector('[name="nama_kegiatan"]');
            const waktuSelect = form.querySelector('select[name="keterangan_waktu"]');

            if (!kitabSelect || !namaField) {
                return;
            }

            kitabSelect.addEventListener('change', function() {
                const selectedOption = kitabSelect.options[kitabSelect.selectedIndex];
                const namaKitab = selectedOption ? selectedOption.dataset.kitNama : '';
                const waktuKitab = selectedOption ? selectedOption.dataset.kitWaktu : '';

                if (namaKitab) {
                    if (namaField.tagName === 'INPUT') {
                        if (namaField.value.trim() === '') {
                            namaField.value = namaKitab;
                        }
                    } else if (Array.from(namaField.options).some(function(option) {
                        return option.value === namaKitab;
                    })) {
                        namaField.value = namaKitab;
                    }
                }

                if (
                    waktuSelect
                    && waktuSelect.value.trim() === ''
                    && waktuKitab
                    && Array.from(waktuSelect.options).some(function(option) {
                        return option.value === waktuKitab;
                    })
                ) {
                    waktuSelect.value = waktuKitab;
                }
            });
        });
    })();
</script>
