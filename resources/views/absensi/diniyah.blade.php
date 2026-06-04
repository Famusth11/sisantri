@extends('layouts.app')

@section('content')
<style>
    :root {
        --brand-dark: #0f5c4d;
        --brand: #1f7a68;
        --brand-soft: #7ab8a6;
        --line-soft: #cfe5dc;
        --surface-soft: #eef6f2;
        --accent-dark: #2f8f6b;
        --accent: #5ca88a;
        --accent-soft: #a9d1c1;
        --brand-gradient: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        --surface-gradient: linear-gradient(135deg, #dff2ea 0%, #eef6f2 100%);
        --accent-gradient: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
    }
    
    .page-header {
        background: var(--brand-gradient);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 8px 24px rgba(15, 92, 77, 0.25);
        border: 1px solid rgba(207, 229, 220, 0.6);
    }
    .form-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(15, 92, 77, 0.08);
        overflow: hidden;
        border: 1px solid rgba(207, 229, 220, 0.5);
        content-visibility: auto;
        contain-intrinsic-size: 540px;
    }
    .form-header {
        background: var(--accent-gradient);
        color: white;
        padding: 20px;
        font-weight: 600;
        border: none;
    }
    .page-btn {
        border-radius: 12px;
        padding: 14px 32px;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        font-size: 0.95rem;
    }
    .page-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(15, 92, 77, 0.25);
    }
    .page-table {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.1);
        border: 1px solid rgba(207, 229, 220, 0.5);
        content-visibility: auto;
        contain-intrinsic-size: 620px;
    }
    .page-table thead th {
        background: var(--brand-gradient);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.5px;
        padding: 20px 15px;
    }
    .page-table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid var(--line-soft);
    }
    .page-table tbody tr:hover {
        background: var(--surface-gradient);
        transform: scale(1.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.15);
    }
    .page-table tbody tr.quick-select-row:hover {
        background: #f8f9fa;
        transform: none;
        box-shadow: none;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid var(--surface-soft);
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 0.2rem rgba(15, 92, 77, 0.18);
    }
    .jadwal-search-group {
        position: relative;
    }
    .jadwal-search-group .form-control {
        padding-left: 2.65rem;
    }
    .jadwal-search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #607066;
        pointer-events: none;
    }
    .jadwal-search-info {
        color: #607066;
        font-size: 0.82rem;
        min-height: 1.1rem;
    }
    .status-select {
        border-radius: 12px;
        padding: 8px 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid var(--surface-soft);
    }
    .status-select:focus {
        border-color: var(--brand-dark);
        box-shadow: 0 0 0 0.2rem rgba(15, 92, 77, 0.18);
    }
    .status-radio-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        justify-content: flex-start;
        min-width: 328px;
    }
    .status-radio-option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 76px;
        min-height: 36px;
        padding: 6px 8px;
        border: 1px solid var(--line-soft);
        border-radius: 10px;
        background: #fff;
        font-size: 0.86rem;
        line-height: 1;
        cursor: pointer;
        white-space: nowrap;
    }
    .status-radio-option input[type="radio"] {
        margin: 0;
        flex: 0 0 auto;
    }
    .status-radio-option:has(input[type="radio"]:checked) {
        border-color: var(--brand);
        background: var(--surface-soft);
        color: var(--brand-dark);
        font-weight: 600;
    }
    .stats-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 12px rgba(15, 92, 77, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(216, 225, 255, 0.5);
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(15, 92, 77, 0.15);
        border-color: var(--brand-soft);
    }
    .pagination-summary {
        color: #607066;
        font-size: 0.95rem;
    }
    .pagination .page-link {
        color: #0f5c4d;
    }
    .pagination .page-item.active .page-link {
        background-color: #0f5c4d;
        border-color: #0f5c4d;
        color: white;
    }
    .diniyah-table-wrap {
        -webkit-overflow-scrolling: touch;
        content-visibility: auto;
        contain-intrinsic-size: 560px;
    }
    .diniyah-table {
        min-width: 1180px;
        table-layout: fixed;
    }
    .diniyah-table th,
    .diniyah-table td {
        white-space: nowrap;
    }
    .diniyah-col-id {
        width: 130px;
    }
    .diniyah-col-nama {
        width: 280px;
    }
    .diniyah-col-kelas {
        width: 110px;
    }
    .diniyah-col-golongan {
        width: 150px;
    }
    .diniyah-col-pengampu {
        width: 180px;
    }
    .diniyah-col-status {
        width: 360px;
    }
    .quick-select-label {
        flex: 0 0 auto;
        color: #42544b;
    }
    .quick-select-panel {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        width: 100%;
    }
    .quick-select-actions {
        display: grid;
        grid-template-columns: repeat(4, 120px);
        gap: 8px;
        max-width: 100%;
    }
    .quick-select-actions .btn {
        min-height: 36px;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        white-space: nowrap;
        width: 100%;
    }
    .diniyah-scroll-hint {
        display: none;
    }
    @media (max-width: 991.98px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .page-header {
            padding: 1.15rem;
            margin-bottom: 1rem;
        }
        .stats-card {
            margin-bottom: 0.85rem;
        }
        .form-card .card-body {
            padding: 1rem !important;
        }
        .page-btn {
            padding: 0.9rem 1rem;
        }
    }
    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }
        .page-header .lead {
            font-size: 0.98rem;
        }
        .status-radio-group {
            gap: 0.4rem;
            min-width: 304px;
        }
        .status-radio-option {
            width: 70px;
            min-height: 34px;
            padding: 0.4rem 0.5rem;
            font-size: 0.8rem;
        }
        .quick-select-actions {
            grid-template-columns: repeat(2, 116px);
            gap: 6px;
        }
        .quick-select-actions .btn {
            font-size: 0.76rem;
        }
        .diniyah-table-wrap {
            margin: 0 -0.35rem;
            padding: 0 0.35rem 0.25rem;
        }
        .diniyah-scroll-hint {
            display: block;
            color: #607066;
            font-size: 0.82rem;
            margin-top: 0.75rem;
        }
    }
    @media (max-width: 575.98px) {
        .page-header {
            border-radius: 1rem;
        }
        .page-table thead th,
        .page-table tbody td {
            padding: 0.75rem 0.65rem;
        }
        .pagination-summary {
            text-align: left;
        }
        .quick-select-panel {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-book-open me-3"></i>
                    Presensi Diniyah/Tahfidz
                </h1>
                <p class="lead mb-0">Catat kehadiran santri untuk kegiatan diniyah dan tahfidz.</p>
                @if($activeScheduleInfo)
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark fs-6">
                            Jadwal aktif: {{ $activeScheduleInfo['tahun_ajaran'] }} - {{ $activeScheduleInfo['semester'] }}
                        </span>
                        @if(auth()->user()->role === 'Admin')
                            <a href="{{ route('jadwal_diniyah.index') }}" class="btn btn-sm btn-outline-light">
                                Atur Jadwal Diniyah
                            </a>
                            <a href="{{ route('jadwal_diniyah.index', array_filter([
                                'tahun_ajaran' => $activeScheduleInfo['tahun_ajaran'] ?? null,
                                'semester' => $activeScheduleInfo['semester'] ?? null,
                                'open_add' => 1,
                            ])) }}" class="btn btn-sm btn-light text-success">
                                <i class="fas fa-plus me-1"></i>Tambah Jadwal
                            </a>
                        @endif
                    </div>
                @elseif(auth()->user()->role === 'Admin')
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="{{ route('jadwal_diniyah.index') }}" class="btn btn-sm btn-outline-light">
                            Buat Jadwal Aktif Dulu
                        </a>
                        <a href="{{ route('jadwal_diniyah.index', ['open_add' => 1]) }}" class="btn btn-sm btn-light text-success">
                            <i class="fas fa-plus me-1"></i>Tambah Jadwal
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Periksa form presensi.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-lg text-primary mb-2"></i>
                    <h5 class="card-title">{{ count($santriList) }}</h5>
                    <p class="card-text">Total Santri</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <i class="fas fa-book fa-lg text-success mb-2"></i>
                    <h5 class="card-title">{{ $jadwalData->count() }}</h5>
                    <p class="card-text">Jadwal Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <i class="fas fa-calendar-day fa-lg text-info mb-2"></i>
                    <h5 class="card-title">{{ date('d/m/Y') }}</h5>
                    <p class="card-text">Tanggal Hari Ini</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <i class="fas fa-user-tag fa-lg text-warning mb-2"></i>
                    <h5 class="card-title">{{ auth()->user()->role }}</h5>
                    <p class="card-text">Role Anda</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card form-card">
                <div class="form-header">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Form Presensi Diniyah
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('absensi.storeDiniyah') }}" id="absensiForm">
                        @csrf
                        
                        <div class="row mb-4 g-3">
                            <div class="col-lg-4">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                                    <label class="form-label fw-bold mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>
                                        Pilih Jadwal Diniyah
                                    </label>
                                    @if(auth()->user()->role === 'Admin')
                                        <a href="{{ route('jadwal_diniyah.index', array_filter([
                                            'tahun_ajaran' => $activeScheduleInfo['tahun_ajaran'] ?? null,
                                            'semester' => $activeScheduleInfo['semester'] ?? null,
                                            'open_add' => 1,
                                        ])) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-plus me-1"></i>Tambah Jadwal
                                        </a>
                                    @endif
                                </div>
                                <div class="jadwal-search-group mb-2">
                                    <i class="fas fa-search jadwal-search-icon"></i>
                                    <input type="text"
                                           id="jadwalSearchInput"
                                           class="form-control"
                                           placeholder="Cari nama jadwal...">
                                </div>
                                <select name="jadwal_id" id="jadwalSelect" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Jadwal Diniyah --</option>
                                    @foreach($jadwalData as $jadwal)
                                        @php
                                            $namaJadwal = trim((string) ($jadwal->nama_kegiatan ?: $jadwal->kitab?->nama_kitab ?: $jadwal->kitab_id));
                                            $namaJadwal = $namaJadwal !== '' ? $namaJadwal : 'Jadwal #' . $jadwal->id;
                                            $jadwalSearchText = trim(implode(' ', array_filter([
                                                $namaJadwal,
                                                $jadwal->kitab?->nama_kitab,
                                                $jadwal->kelas ? 'Kelas ' . $jadwal->kelas : null,
                                                $jadwal->golongan,
                                                $jadwal->formatted_tanggal,
                                                $jadwal->formatted_jam,
                                            ])));
                                        @endphp
                                        <option value="{{ $jadwal->id }}"
                                                data-kelas="{{ $jadwal->kelas }}"
                                                data-golongan="{{ $jadwal->golongan }}"
                                                data-search="{{ strtolower($jadwalSearchText) }}"
                                                {{ (string) $selectedJadwal === (string) $jadwal->id ? 'selected' : '' }}>
                                            {{ $namaJadwal }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="jadwalSearchInfo" class="jadwal-search-info mt-1"></div>
                                @if($jadwalData->isEmpty())
                                    <div class="text-danger small mt-2">
                                        Jadwal aktif belum tersedia. Hubungi admin untuk mengatur jadwal diniyah terlebih dahulu.
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-school me-2"></i>
                                    Kelas Diajar
                                </label>
                                <select id="kelasFilter" name="kelas_mengajar" class="form-select" onchange="updateKelasFilter(this.value)" required>
                                    <option value="" disabled {{ old('kelas_mengajar', $kelasFilter ?? '') === '' ? 'selected' : '' }}>Pilih Kelas</option>
                                    @foreach(['10', '11', '12'] as $kelasOption)
                                        <option value="{{ $kelasOption }}" {{ (string) old('kelas_mengajar', $kelasFilter ?? '') === $kelasOption ? 'selected' : '' }}>
                                            Kelas {{ $kelasOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>
                                    Pengampu
                                </label>
                                <div class="form-control bg-light">
                                    {{ trim((string) (auth()->user()->nama_lengkap ?: auth()->user()->name ?: auth()->user()->email)) }}
                                </div>
                                <div class="form-text">Otomatis dari akun yang login.</div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Tanggal Presensi
                                </label>
                                <input type="date" name="tanggal" class="form-control" value="{{ now('Asia/Jakarta')->format('Y-m-d') }}">
                            </div>
                            <div class="col-12 d-flex align-items-end">
                                <div class="w-100">
                                    <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Memuat data santri...</p>
                                    </div>
                                    <button type="submit" class="btn btn-primary page-btn w-100" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Presensi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card page-table">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Data Santri ({{ count($santriList) }})
                                    @if(!empty($kelasFilter) && !$selectedJadwal)
                                        - Kelas {{ $kelasFilter }}
                                    @endif
                                    @if($selectedJadwal && $selectedJadwalData)
                                            @php
                                                $selectedJadwalName = trim((string) ($selectedJadwalData->nama_kegiatan ?: $selectedJadwalData->kitab?->nama_kitab ?: $selectedJadwalData->kitab_id));
                                            @endphp
                                            <small class="d-block mt-1">
                                                <i class="fas fa-book me-1"></i>
                                                {{ $selectedJadwalName !== '' ? $selectedJadwalName : 'Jadwal #' . $selectedJadwalData->id }}
                                            </small>
                                    @else
                                        <small class="d-block mt-1 text-warning">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pilih jadwal untuk melihat santri yang sesuai
                                        </small>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body border-bottom">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-8">
                                        <label for="searchSantriDiniyah" class="form-label fw-bold">
                                            <i class="fas fa-search me-2"></i>
                                            Cari Data Santri
                                        </label>
                                        <input type="text"
                                               id="searchSantriDiniyah"
                                               class="form-control"
                                               placeholder="Cari ID, nama, kelas, golongan, atau pengampu...">
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="pagination-summary">
                                            <span id="diniyahResultCount">{{ count($santriList) }}</span> dari {{ count($santriList) }} data
                                        </div>
                                    </div>
                                </div>
                                <div class="diniyah-scroll-hint">
                                    <i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke samping untuk melihat seluruh kolom di layar kecil.
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive diniyah-table-wrap">
                                    <table class="table page-table mb-0 diniyah-table">
                                        <colgroup>
                                            <col class="diniyah-col-id">
                                            <col class="diniyah-col-nama">
                                            <col class="diniyah-col-kelas">
                                            <col class="diniyah-col-golongan">
                                            <col class="diniyah-col-pengampu">
                                            <col class="diniyah-col-status">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-hashtag me-2"></i>ID Santri</th>
                                                <th><i class="fas fa-user me-2"></i>Nama</th>
                                                <th><i class="fas fa-graduation-cap me-2"></i>Kelas</th>
                                                <th><i class="fas fa-layer-group me-2"></i>Golongan</th>
                                                <th><i class="fas fa-chalkboard-user me-2"></i>Pengampu</th>
                                                <th><i class="fas fa-check-circle me-2"></i>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="diniyahTableBody">
                                            @foreach($santriList as $santri)
                                            @php
                                                $pengampu = trim((string) ($selectedJadwalData->pengampu ?? ''));
                                            @endphp
                                            <tr class="santri-row"
                                                data-id="{{ strtolower($santri->id_santri) }}"
                                                data-nama="{{ strtolower($santri->nama) }}"
                                                data-kelas="{{ strtolower($santri->kelas) }}"
                                                data-golongan="{{ strtolower($santri->golongan) }}"
                                                data-pengampu="{{ strtolower($pengampu) }}">
                                                <td>
                                                    <span class="badge bg-primary fs-6">{{ $santri->id_santri }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <strong>{{ $santri->nama }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $santri->kelas }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">{{ $santri->golongan }}</span>
                                                </td>
                                                <td>
                                                    @if($pengampu !== '')
                                                        <span class="badge bg-info text-dark">{{ $pengampu }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="status-radio-group">
                                                        @foreach(['Hadir', 'Izin', 'Sakit', 'Alpa'] as $statusOption)
                                                            <label class="status-radio-option">
                                                                <input type="radio"
                                                                       name="absensi[{{ $santri->id_santri }}]"
                                                                       value="{{ $statusOption }}">
                                                                <span>{{ $statusOption }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="pagination-summary" id="diniyahPageSummary">
                                        Data Santri halaman 1
                                    </div>
                                    <nav aria-label="Pagination Data Santri Diniyah">
                                        <ul class="pagination mb-0" id="diniyahPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let diniyahRowsCache = [];

document.addEventListener('DOMContentLoaded', function() {
    diniyahRowsCache = Array.from(document.querySelectorAll('#diniyahTableBody .santri-row'));

    document.getElementById('absensiForm').addEventListener('submit', function(e) {
        const jadwalSelect = document.querySelector('select[name="jadwal_id"]');
        if (!jadwalSelect.value) {
            e.preventDefault();
            alert('Silakan pilih jadwal diniyah terlebih dahulu!');
            return;
        }

        const hasSelection = document.querySelector('input[type="radio"][name^="absensi["]:checked') !== null;
        
        if (!hasSelection) {
            e.preventDefault();
            alert('Silakan pilih status untuk minimal satu santri!');
            return;
        }
    });

    addQuickSelectButtons();
    initializeDiniyahPagination();

    const jadwalSelectElement = document.getElementById('jadwalSelect');
    if (!jadwalSelectElement) {
        return;
    }

    initializeJadwalSearch(jadwalSelectElement);

    jadwalSelectElement.addEventListener('change', function() {
        const selectedValue = this.value;
        
        if (selectedValue) {
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('submitBtn').style.display = 'none';

            const url = new URL(window.location);
            url.searchParams.set('jadwal_id', selectedValue);
            window.location.href = url.toString();
        } else {
            const url = new URL(window.location);
            url.searchParams.delete('jadwal_id');
            window.location.href = url.toString();
        }
    });
});

function initializeJadwalSearch(jadwalSelectElement) {
    const searchInput = document.getElementById('jadwalSearchInput');
    const searchInfo = document.getElementById('jadwalSearchInfo');

    if (!searchInput) {
        return;
    }

    const options = Array.from(jadwalSelectElement.options).filter((option) => option.value !== '');

    const updateScheduleOptions = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        options.forEach((option) => {
            const searchText = option.getAttribute('data-search') || option.textContent.toLowerCase();
            const isSelected = option.value === jadwalSelectElement.value;
            const isVisible = !searchTerm || searchText.includes(searchTerm);

            option.hidden = !isVisible && !isSelected;
            option.disabled = !isVisible && !isSelected;

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (searchInfo) {
            searchInfo.textContent = searchTerm
                ? `${visibleCount} jadwal cocok`
                : '';
        }
    };

    searchInput.addEventListener('input', updateScheduleOptions);
    updateScheduleOptions();
}

function addQuickSelectButtons() {
    const tableBody = document.querySelector('.page-table tbody');
    if (!tableBody) return;

    const quickSelectRow = document.createElement('tr');
    quickSelectRow.className = 'bg-light quick-select-row';
    quickSelectRow.innerHTML = `
        <td colspan="6">
            <div class="quick-select-panel">
                <span class="quick-select-label fw-bold">
                    <i class="fas fa-magic me-2"></i>Quick Select:
                </span>
                <div class="quick-select-actions" role="group" aria-label="Quick Select Presensi">
                    <button type="button" class="btn btn-success" onclick="quickSelectAll('Hadir')">
                        <i class="fas fa-check me-1"></i>Semua Hadir
                    </button>
                    <button type="button" class="btn btn-warning" onclick="quickSelectAll('Izin')">
                        <i class="fas fa-exclamation me-1"></i>Semua Izin
                    </button>
                    <button type="button" class="btn btn-danger" onclick="quickSelectAll('Alpa')">
                        <i class="fas fa-times me-1"></i>Semua Alpa
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearAll()">
                        <i class="fas fa-eraser me-1"></i>Clear All
                    </button>
                </div>
            </div>
        </td>
    `;
    
    tableBody.insertBefore(quickSelectRow, tableBody.firstChild);
}

function quickSelectAll(status) {
    diniyahRowsCache.forEach((row) => {
        const targetRadio = row.querySelector(`input[type="radio"][value="${status}"]`);
        if (targetRadio) {
            targetRadio.checked = true;
        }
    });
    
    showAlert(`Semua santri di-set sebagai: ${status}`, 'info');
}

function updateKelasFilter(value) {
    const url = new URL(window.location);

    if (value) {
        url.searchParams.set('kelas', value);
    } else {
        url.searchParams.delete('kelas');
    }

    window.location.href = url.toString();
}

function clearAll() {
    diniyahRowsCache.forEach((row) => {
        row.querySelectorAll('input[type="radio"]').forEach((radio) => {
            radio.checked = false;
        });
    });
    
    showAlert('Semua pilihan status telah dihapus', 'info');
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const form = document.getElementById('absensiForm');
    form.insertBefore(alertDiv, form.firstChild);

    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

function initializeDiniyahPagination() {
    const searchInput = document.getElementById('searchSantriDiniyah');
    const resultCount = document.getElementById('diniyahResultCount');
    const pageSummary = document.getElementById('diniyahPageSummary');
    const pagination = document.getElementById('diniyahPagination');
    const rows = diniyahRowsCache;
    const quickSelectRow = document.querySelector('#diniyahTableBody .quick-select-row');

    if (!pagination || !rows.length) {
        if (pageSummary) {
            pageSummary.textContent = 'Data Santri halaman 1';
        }
        return;
    }

    let currentPage = 1;

    const debounce = (callback, delay = 150) => {
        let timeoutId = null;

        return (...args) => {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(() => callback(...args), delay);
        };
    };

    const renderRows = () => {
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        const perPage = 10;

        const filteredRows = rows.filter((row) => {
            const id = row.getAttribute('data-id') || '';
            const nama = row.getAttribute('data-nama') || '';
            const kelas = row.getAttribute('data-kelas') || '';
            const golongan = row.getAttribute('data-golongan') || '';
            const pengampu = row.getAttribute('data-pengampu') || '';

            return !searchTerm
                || id.includes(searchTerm)
                || nama.includes(searchTerm)
                || kelas.includes(searchTerm)
                || golongan.includes(searchTerm)
                || pengampu.includes(searchTerm);
        });

        const totalPages = Math.max(1, Math.ceil(filteredRows.length / perPage));
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * perPage;
        const endIndex = startIndex + perPage;

        rows.forEach((row) => {
            row.style.display = 'none';
        });

        filteredRows.slice(startIndex, endIndex).forEach((row) => {
            row.style.display = '';
        });

        if (quickSelectRow) {
            quickSelectRow.style.display = '';
        }

        if (resultCount) {
            resultCount.textContent = filteredRows.length;
        }

        if (pageSummary) {
            pageSummary.textContent = `Data Santri halaman ${currentPage} dari ${totalPages}`;
        }

        pagination.innerHTML = '';

        const createItem = (label, page, disabled = false, active = false) => {
            const li = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;
            button.disabled = disabled;
            button.addEventListener('click', () => {
                currentPage = page;
                renderRows();
            });

            li.appendChild(button);
            pagination.appendChild(li);
        };

        createItem('Prev', Math.max(1, currentPage - 1), currentPage === 1);

        const windowStart = Math.max(1, currentPage - 2);
        const windowEnd = Math.min(totalPages, currentPage + 2);

        if (windowStart > 1) {
            createItem('1', 1, false, currentPage === 1);
            if (windowStart > 2) {
                createItem('...', currentPage, true);
            }
        }

        for (let page = windowStart; page <= windowEnd; page++) {
            createItem(String(page), page, false, page === currentPage);
        }

        if (windowEnd < totalPages) {
            if (windowEnd < totalPages - 1) {
                createItem('...', currentPage, true);
            }
            createItem(String(totalPages), totalPages, false, currentPage === totalPages);
        }

        createItem('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages);
    };

    searchInput?.addEventListener('input', debounce(() => {
        currentPage = 1;
        renderRows();
    }, 120));

    renderRows();
}
</script>
@endsection


