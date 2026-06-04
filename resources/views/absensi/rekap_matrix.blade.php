@extends('layouts.app')

@section('content')
@php
    $isDiniyah = $type === 'diniyah';
    $routeName = $isDiniyah ? 'absensi.rekapBulanan.matrix' : 'absensi.rekapBulananSholat.matrix';
    $backRoute = $isDiniyah ? 'absensi.rekapBulanan' : 'absensi.rekapBulananSholat';
    $headerTitle = $jadwalFilter
        ? 'ABSENSI ' . ucwords(strtolower($jadwalFilter))
        : ($isDiniyah ? 'ABSENSI KEGIATAN DINIYAH' : 'ABSENSI SHOLAT');
@endphp

<style>
    @page {
        size: A4 landscape;
        margin: 6mm;
    }
    .matrix-toolbar {
        background: #fffdf8;
        border: 1px solid rgba(202, 191, 168, 0.72);
        border-radius: 0.75rem;
        padding: 1rem;
    }
    .matrix-filter-grid {
        display: grid;
        grid-template-columns: minmax(180px, 1.4fr) minmax(130px, 0.9fr) minmax(140px, 0.9fr) minmax(200px, 1.4fr) auto;
        gap: 0.75rem;
        align-items: end;
    }
    .matrix-actions {
        display: grid;
        grid-template-columns: repeat(3, max-content);
        gap: 0.55rem;
        justify-content: end;
    }
    .matrix-actions .btn {
        min-width: 108px;
        white-space: nowrap;
    }
    .matrix-sheet {
        background: #fff;
        color: #111;
        padding: 1rem;
        overflow-x: auto;
    }
    .matrix-title {
        text-align: center;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
    }
    .matrix-meta {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .matrix-table {
        border-collapse: collapse;
        width: max-content;
        min-width: {{ 42 + 180 + ((int) $daysInMonth * 30) + 145 }}px;
        table-layout: fixed;
        font-size: 0.78rem;
    }
    .matrix-table th,
    .matrix-table td {
        border: 1px solid #333;
        padding: 0.22rem 0.28rem;
        text-align: center;
        height: 24px;
    }
    .matrix-table thead th {
        background: #ffc400;
        font-weight: 800;
    }
    .matrix-table .col-no {
        width: 42px;
    }
    .matrix-table .col-name {
        width: 180px;
        text-align: left;
    }
    .matrix-table .col-day {
        width: 30px;
    }
    .matrix-table .col-pengampu {
        width: 145px;
        text-align: left;
    }
    .matrix-status {
        font-weight: 800;
    }
    .matrix-legend {
        font-size: 0.82rem;
        color: #444;
        margin-top: 0.65rem;
    }
    @media print {
        body {
            background: #fff !important;
        }
        nav,
        .matrix-toolbar,
        .sidebar,
        .navbar,
        .app-sidebar,
        .app-sidebar-backdrop,
        .app-topbar,
        .flash-stack {
            display: none !important;
        }
        .app-shell,
        .app-main,
        .main-content {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            min-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            border: 0 !important;
        }
        .container-fluid,
        .container {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .matrix-sheet {
            padding: 0;
            overflow: visible;
        }
        .matrix-title {
            margin-bottom: 4mm;
            font-size: 10pt;
        }
        .matrix-meta {
            margin-bottom: 2mm;
            font-size: 7.5pt;
        }
        .matrix-table {
            width: auto;
            min-width: 0;
            font-size: 6.8pt;
            line-height: 1.05;
        }
        .matrix-table th,
        .matrix-table td {
            padding: 1px;
            height: 5mm;
        }
        .matrix-table .col-no {
            width: 8mm;
        }
        .matrix-table .col-name {
            width: 40mm;
            max-width: 40mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .matrix-table .col-day {
            width: 6.2mm;
            min-width: 6.2mm;
            max-width: 6.2mm;
            padding-left: 0;
            padding-right: 0;
            text-align: center;
            white-space: nowrap;
        }
        .matrix-table .col-pengampu {
            width: 34mm;
            max-width: 34mm;
            font-size: 6.3pt;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .matrix-legend {
            margin-top: 3mm;
            font-size: 7pt;
        }
    }
    @media (max-width: 1199.98px) {
        .matrix-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .matrix-actions {
            grid-template-columns: repeat(3, minmax(110px, 1fr));
            justify-content: stretch;
        }
    }
    @media (max-width: 575.98px) {
        .matrix-filter-grid,
        .matrix-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="matrix-toolbar mb-3">
        <form method="GET" action="{{ route($routeName) }}" class="matrix-filter-grid">
            <div>
                <label for="month" class="form-label fw-bold">Bulan</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div>
                <label for="kelas" class="form-label fw-bold">Kelas</label>
                <select id="kelas" name="kelas" class="form-select">
                    <option value="">Semua</option>
                    @foreach($kelasList as $kelasOption)
                        <option value="{{ $kelasOption }}" {{ (string) $kelasFilter === (string) $kelasOption ? 'selected' : '' }}>
                            Kelas {{ $kelasOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="jenis_kelamin" class="form-label fw-bold">Putra/Putri</label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="form-select">
                    <option value="">Semua</option>
                    @foreach($jenisKelaminList as $jenisKelaminOption)
                        <option value="{{ $jenisKelaminOption }}" {{ (string) $jenisKelaminFilter === (string) $jenisKelaminOption ? 'selected' : '' }}>
                            {{ $jenisKelaminOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="jadwal" class="form-label fw-bold">{{ $isDiniyah ? 'Jadwal/Kegiatan' : 'Jenis Sholat' }}</label>
                <select id="jadwal" name="jadwal" class="form-select">
                    <option value="">Semua</option>
                    @foreach($jadwalList as $jadwalOption)
                        @php
                            $jadwalValue = preg_replace('/^(Ngaji|Diniyah|Tahfidz|Sholat|Solat)\s+/iu', '', $jadwalOption);
                            $jadwalValue = strtoupper(trim($jadwalValue));
                            $jadwalLabel = preg_replace('/^(Ngaji|Diniyah|Tahfidz|Sholat|Solat)\s+/iu', '', $jadwalOption);
                        @endphp
                        <option value="{{ $jadwalValue }}" {{ (string) $jadwalFilter === (string) $jadwalValue ? 'selected' : '' }}>
                            {{ $jadwalLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="matrix-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>Tampilkan
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Cetak
                </button>
                <a href="{{ route($backRoute, ['month' => $month, 'kelas' => $kelasFilter, 'jenis_kelamin' => $jenisKelaminFilter, 'jadwal' => $jadwalFilter]) }}" class="btn btn-outline-success">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>

    <div class="matrix-sheet">
        <div class="matrix-title">
            <div>{{ $headerTitle }}</div>
            <div>ASRAMA SMK TAKHASSUS</div>
        </div>
        <div class="matrix-meta">
            <div>BULAN: {{ strtoupper($monthLabel) }}</div>
            <div>
                KELAS: {{ $kelasFilter !== '' ? $kelasFilter : 'SEMUA' }}
                @if($jenisKelaminFilter !== '')
                    | {{ strtoupper($jenisKelaminFilter) }}
                @endif
            </div>
        </div>
        <table class="matrix-table">
            <colgroup>
                <col class="col-no">
                <col class="col-name">
                @for($day = 1; $day <= $daysInMonth; $day++)
                    <col class="col-day">
                @endfor
                <col class="col-pengampu">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">NO</th>
                    <th rowspan="2" class="col-name">NAMA SANTRI</th>
                    <th colspan="{{ $daysInMonth }}">TANGGAL</th>
                    <th rowspan="2" class="col-pengampu">PENGABSEN</th>
                </tr>
                <tr>
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        <th class="col-day">{{ $day }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="col-name">{{ $row['nama'] }}</td>
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <td class="matrix-status col-day">{{ $row['days'][$day] ?? '' }}</td>
                        @endfor
                        <td class="col-pengampu">{{ !empty($row['pengampu']) ? implode(', ', $row['pengampu']) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $daysInMonth + 3 }}" class="text-center">Tidak ada data santri.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="matrix-legend">
            Keterangan: H = Hadir, I = Izin, S = Sakit, A = Alpa.
        </div>
    </div>
</div>
@endsection
