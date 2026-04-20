@extends('layouts.app')

@section('content')
<style>
    .recap-header {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border-radius: 1rem;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 10px 24px rgba(15, 92, 77, 0.16);
    }
    .btn-recap-primary {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border: none;
    }
    .btn-recap-primary:hover {
        background: linear-gradient(135deg, #0a463b 0%, #165f51 100%);
        color: #fff;
    }
    .btn-recap-soft {
        background: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
        color: #fff;
        border: none;
    }
    .btn-recap-soft:hover {
        background: linear-gradient(135deg, #27795b 0%, #4a9478 100%);
        color: #fff;
    }
    .table-row-hover:hover {
        background-color: #f8f9fa !important;
    }
    .table-row-hover td {
        transition: background-color 0.2s ease;
    }
    [data-bs-toggle="collapse"] i {
        transition: transform 0.3s ease;
    }
    [data-bs-toggle="collapse"][aria-expanded="true"] i {
        transform: rotate(180deg);
    }
    .recap-export-group,
    .recap-class-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .recap-table-wrap,
    .recap-detail-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
    .recap-summary-table {
        min-width: 780px;
    }
    .recap-detail-table {
        min-width: 860px;
    }
    .recap-scroll-hint {
        display: none;
    }
    @media (max-width: 991.98px) {
        .recap-filter-form > [class*="col-"] {
            flex: 1 1 220px;
        }
        .recap-filter-form .btn {
            width: 100%;
        }
        .recap-filter-form .ms-xl-auto {
            margin-left: 0 !important;
        }
        .recap-search-meta,
        .recap-card-header {
            text-align: left !important;
        }
        .recap-card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.85rem;
        }
        .recap-class-actions,
        .recap-export-group {
            width: 100%;
        }
        .recap-class-actions .btn,
        .recap-export-group .btn {
            flex: 1 1 140px;
        }
        .recap-class-title {
            line-height: 1.55;
        }
    }
    @media (max-width: 575.98px) {
        .container.py-4 {
            padding-left: 0.35rem;
            padding-right: 0.35rem;
        }
        .recap-header {
            padding: 1rem 1.05rem;
        }
        .recap-header h2 {
            font-size: 1.2rem;
        }
        .recap-table-wrap,
        .recap-detail-table-wrap {
            margin: 0 -0.35rem;
            padding: 0 0.35rem 0.25rem;
        }
        .recap-summary-table,
        .recap-detail-table {
            font-size: 0.92rem;
        }
        .recap-summary-table th,
        .recap-summary-table td,
        .recap-detail-table th,
        .recap-detail-table td {
            padding: 0.55rem 0.5rem;
            vertical-align: middle;
        }
        .recap-search-meta {
            margin-top: 0.75rem;
        }
        .recap-scroll-hint {
            display: block;
            margin: -0.35rem 0 0.85rem;
            color: var(--text-soft);
            font-size: 0.82rem;
        }
        .recap-class-title .badge {
            display: inline-flex;
            margin: 0.35rem 0.35rem 0 0 !important;
        }
    }
</style>
<div class="container py-4">
    <div class="recap-header mb-4">
        <h2 class="mb-0"><i class="fas fa-mosque me-2"></i>Rekap Bulanan Sholat Jamaah</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="get" action="{{ route('absensi.rekapBulananSholat') }}" class="row g-2 align-items-end mb-4 recap-filter-form">
        <div class="col-12 col-md-auto">
            <label for="month" class="form-label">Bulan</label>
            <input type="month" id="month" name="month" value="{{ $month }}" class="form-control" />
        </div>
        <div class="col-12 col-md-auto">
            <label for="kelas" class="form-label">Kelas</label>
            <select id="kelas" name="kelas" class="form-select">
                <option value="">Semua</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k }}" {{ (string)$kelasFilter === (string)$k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-recap-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        </div>
        <div class="col-12 col-md-auto">
            <a href="{{ route('absensi.rekapBulananSholat.refresh', ['month' => $month, 'kelas' => $kelasFilter]) }}" 
               class="btn btn-recap-soft" 
               title="Refresh data untuk memuat presensi terbaru">
                <i class="fas fa-sync-alt me-1"></i>Refresh Data
            </a>
        </div>
        <div class="col-12 col-xl-auto ms-xl-auto recap-export-group">
            <a href="{{ route('absensi.rekapBulananSholat.exportExcel', ['month' => $month, 'kelas' => $kelasFilter]) }}" 
               class="btn btn-primary">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="{{ route('absensi.rekapBulananSholat.exportPDF', ['month' => $month, 'kelas' => $kelasFilter]) }}" 
               class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>
    </form>

    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="searchRekapSholat" 
                               class="form-control" 
                               placeholder="Cari berdasarkan nama santri...">
                    </div>
                </div>
                <div class="col-md-6 text-end recap-search-meta">
                    <span class="text-muted">
                        Ditemukan: <span id="rekapSholatResultCount">-</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="recap-scroll-hint">
        <i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke samping untuk melihat seluruh kolom di layar kecil.
    </div>

    @php
        $displayMonth = \Carbon\Carbon::parse($month)->locale('id')->format('F Y');
        $classHasData = false;
    @endphp

    @if(!empty($summaryPerSantri))
        @foreach($summaryPerSantri as $kelas => $santriData)
            @if($kelasFilter && (string)$kelas !== (string)$kelasFilter)
                @continue
            @endif

            @php
                $classHasData = true;
                $totalHadirKelas = 0;
                $totalKegiatanKelas = 0;
                foreach($santriData as $s) {
                    $totalHadirKelas += $s['hadir'];
                    $totalKegiatanKelas += $s['total'];
                }
                $avgKehadiranKelas = $totalKegiatanKelas > 0 
                    ? round($totalHadirKelas / $totalKegiatanKelas * 100, 1) 
                    : 0;
            @endphp

            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center recap-card-header">
                    <h5 class="mb-0 recap-class-title">
                        <i class="fas fa-users me-2"></i>
                        Kelas {{ $kelas }} - {{ $displayMonth }}
                        <span class="badge bg-light text-dark ms-2">{{ count($santriData) }} Santri</span>
                        <span class="badge bg-success ms-2">
                            <i class="fas fa-chart-line me-1"></i>Rata-rata: {{ $avgKehadiranKelas }}%
                        </span>
                    </h5>
                    <div class="recap-class-actions">
                        <a href="{{ route('absensi.rekapBulananSholat.exportExcel', ['month' => $month, 'kelas' => $kelas]) }}" 
                           class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </a>
                        <a href="{{ route('absensi.rekapBulananSholat.exportPDF', ['month' => $month, 'kelas' => $kelas]) }}" 
                           class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive recap-table-wrap">
                        <table class="table table-striped table-hover mb-0 recap-summary-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th style="width: 40%;">Nama Santri</th>
                                    <th class="text-center" style="width: 10%;">Total</th>
                                    <th class="text-center" style="width: 8%;">Hadir</th>
                                    <th class="text-center" style="width: 8%;">Izin</th>
                                    <th class="text-center" style="width: 8%;">Sakit</th>
                                    <th class="text-center" style="width: 8%;">Alpha</th>
                                    <th class="text-center" style="width: 13%;">Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($santriData as $santriId => $santri)
                                    @php
                                        $persen = $santri['total'] > 0 
                                            ? round($santri['hadir'] / $santri['total'] * 100, 1) 
                                            : 0;
                                        $badgeClass = $persen >= 80 ? 'success' : ($persen >= 60 ? 'warning' : 'danger');
                                        $iconClass = $persen >= 80 ? 'fa-check-circle' : ($persen >= 60 ? 'fa-exclamation-circle' : 'fa-times-circle');
                                    @endphp
                                    <tr class="table-row-hover clickable-row rekap-sholat-row" 
                                        style="cursor: pointer;" 
                                        onclick="toggleDetail('{{ $santriId }}')"
                                        data-nama="{{ strtolower($santri['nama']) }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $santri['nama'] }}</strong>
                                            <i class="fas fa-chevron-down ms-2 text-muted" id="icon-{{ $santriId }}"></i>
                                        </td>
                                        <td class="text-center fw-bold">{{ $santri['total'] }}</td>
                                        <td class="text-center"><span class="badge bg-success">{{ $santri['hadir'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning">{{ $santri['izin'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-info">{{ $santri['sakit'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $santri['alpha'] }}</span></td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $badgeClass }}">
                                                <i class="fas {{ $iconClass }} me-1"></i>
                                                {{ $persen }}%
                                            </span>
                                        </td>
                                    </tr>
                                    <tr id="detail-{{ $santriId }}" class="collapse-row" style="display: none;">
                                        <td colspan="8" class="p-3 bg-light">
                                            <div class="px-3">
                                                <h6 class="mb-3 text-primary"><i class="fas fa-calendar-alt me-2"></i>Detail Kehadiran Harian - {{ $santri['nama'] }}</h6>
                                                <div class="table-responsive recap-detail-table-wrap">
                                                    <table class="table table-sm table-bordered mb-0 recap-detail-table">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center">Tanggal</th>
                                                                <th class="text-center">Hadir</th>
                                                                <th class="text-center">Izin</th>
                                                                <th class="text-center">Sakit</th>
                                                                <th class="text-center">Alpha</th>
                                                                <th class="text-center">Sholat</th>
                                                                <th class="text-center">Jam</th>
                                                                <th class="text-center">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                ksort($santri['detail_per_hari']);
                                                            @endphp
                                                            @foreach($santri['detail_per_hari'] as $tanggal => $detail)
                                                                @php
                                                                    $totalHari = $detail['hadir'] + $detail['izin'] + $detail['sakit'] + $detail['alpha'];
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center">{{ \Carbon\Carbon::parse($tanggal)->locale('id')->format('d M Y') }}</td>
                                                                    <td class="text-center">{{ $detail['hadir'] }}</td>
                                                                    <td class="text-center">{{ $detail['izin'] }}</td>
                                                                    <td class="text-center">{{ $detail['sakit'] }}</td>
                                                                    <td class="text-center">{{ $detail['alpha'] }}</td>
                                                                    <td>
                                                                        @php
                                                                            $allKegiatan = [];
                                                                            $jamList = $detail['jam'] ?? [];
                                                                            if (isset($detail['kegiatan'])) {
                                                                                foreach ($detail['kegiatan'] as $statusKeg => $kegiatanList) {
                                                                                    if (is_array($kegiatanList)) {
                                                                                        $allKegiatan = array_merge($allKegiatan, $kegiatanList);
                                                                                    }
                                                                                }
                                                                                $allKegiatan = array_unique($allKegiatan);
                                                                            }
                                                                        @endphp
                                                                        @if(!empty($allKegiatan))
                                                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                                                @foreach($allKegiatan as $keg)
                                                                                    <span class="badge bg-primary">{{ $keg }}</span>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if(!empty($jamList))
                                                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                                                @foreach($jamList as $jam)
                                                                                    <span class="badge bg-dark">{{ $jam }}</span>
                                                                                @endforeach
                                                                            </div>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center fw-bold">{{ $totalHari }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        @if($kelasFilter && !$classHasData)
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                Tidak ada data rekap untuk <strong>Kelas {{ $kelasFilter }}</strong>
                pada bulan <strong>{{ $displayMonth }}</strong>.
            </div>
        @endif
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Tidak ada data presensi sholat untuk bulan <strong>{{ $displayMonth }}</strong>
            @if($kelasFilter)
                untuk <strong>Kelas {{ $kelasFilter }}</strong>
            @endif
            . Silakan pilih bulan lain atau ubah filter.
        </div>
    @endif
</div>

<script>
function toggleDetail(santriId) {
    const detailRow = document.getElementById('detail-' + santriId);
    const icon = document.getElementById('icon-' + santriId);
    
    if (detailRow.style.display === 'none' || !detailRow.style.display) {
        detailRow.style.display = 'table-row';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        detailRow.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}


document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchRekapSholat');
    const resultCount = document.getElementById('rekapSholatResultCount');
    const rows = document.querySelectorAll('.rekap-sholat-row');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            let hiddenCards = new Set();
            
            rows.forEach(function(row) {
                const nama = row.getAttribute('data-nama') || '';
                const card = row.closest('.card.shadow');
                
                const matches = !searchTerm || nama.includes(searchTerm);
                
                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                    if (card) {
                        hiddenCards.delete(card);
                    }
                } else {
                    row.style.display = 'none';
                    if (card) {
                        
                        const cardRows = card.querySelectorAll('.rekap-sholat-row');
                        const allHidden = Array.from(cardRows).every(r => r.style.display === 'none');
                        if (allHidden) {
                            hiddenCards.add(card);
                        }
                    }
                }
            });
            
            
            document.querySelectorAll('.card.shadow.mb-4').forEach(function(card) {
                if (hiddenCards.has(card) && searchTerm) {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                }
            });
            
            if (resultCount) {
                resultCount.textContent = searchTerm ? visibleCount + ' santri' : '-';
            }
        });
    }
});
</script>
@endsection



