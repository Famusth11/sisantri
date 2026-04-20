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
        min-width: 760px;
    }
    .recap-detail-table {
        min-width: 920px;
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
    }
</style>
<div class="container py-4">
    <div class="recap-header mb-4">
        <h2 class="mb-0">Rekap Bulanan Presensi</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="get" action="{{ route('absensi.rekapBulanan') }}" class="row g-2 align-items-end mb-4 recap-filter-form">
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
            <label for="golongan" class="form-label">Golongan</label>
            <select id="golongan" name="golongan" class="form-select">
                <option value="">Semua</option>
                @foreach($golonganList as $g)
                    <option value="{{ $g }}" {{ (string)$golonganFilter === (string)$g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-recap-primary">
                <i class="fas fa-filter me-1"></i>Tampilkan
            </button>
        </div>
        <div class="col-12 col-md-auto">
            <a href="{{ route('absensi.rekapBulanan.refresh', ['month' => $month, 'kelas' => $kelasFilter, 'golongan' => $golonganFilter]) }}" 
               class="btn btn-recap-soft" 
               title="Refresh data untuk memuat presensi terbaru">
                <i class="fas fa-sync-alt me-1"></i>Refresh Data
            </a>
        </div>
        @if($kelasFilter || $golonganFilter)
        <div class="col-12 col-xl-auto ms-xl-auto recap-export-group">
            <a class="btn btn-primary" href="{{ route('absensi.rekapBulanan.exportExcel', ['kelas'=>$kelasFilter,'golongan'=>$golonganFilter,'month'=>$month]) }}">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a class="btn btn-danger" href="{{ route('absensi.rekapBulanan.exportPDF', ['kelas'=>$kelasFilter,'golongan'=>$golonganFilter,'month'=>$month]) }}">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>
        @endif
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
                               id="searchRekap" 
                               class="form-control" 
                               placeholder="Cari berdasarkan nama santri...">
                    </div>
                </div>
                <div class="col-md-6 text-end recap-search-meta">
                    <span class="text-muted">
                        Ditemukan: <span id="rekapResultCount">-</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="recap-scroll-hint">
        <i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke samping untuk melihat kolom lengkap di layar kecil.
    </div>

    @php
        $hasData = false;
        if ($kelasFilter || $golonganFilter) {
            $hasData = count($summaryPerSantri) > 0;
        } else {
            $hasData = count($summaryPerSantri) > 0;
        }
    @endphp

    @if(($kelasFilter || $golonganFilter) && !$hasData)
        <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>
            Tidak ada data presensi untuk 
            @if($kelasFilter) <strong>Kelas {{ $kelasFilter }}</strong> @endif
            @if($kelasFilter && $golonganFilter) dan @endif
            @if($golonganFilter) <strong>Golongan {{ $golonganFilter }}</strong> @endif
            pada bulan <strong>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</strong>.
        </div>
    @endif

    @forelse($summaryPerSantri as $kelas => $santriData)
        @if(!$kelasFilter || (string)$kelasFilter === (string)$kelas)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center recap-card-header">
                <strong>Kelas {{ $kelas }}</strong>
                <div class="recap-class-actions">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('absensi.rekapBulanan.exportExcel', ['kelas'=>$kelas,'month'=>$month]) }}">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </a>
                    <a class="btn btn-sm btn-outline-danger" href="{{ route('absensi.rekapBulanan.exportPDF', ['kelas'=>$kelas,'month'=>$month]) }}">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive recap-table-wrap">
                    <table class="table table-striped table-hover mb-0 recap-summary-table">
                        <thead>
                            <tr>
                                <th>Nama Santri</th>
                                <th>Total</th>
                                <th>Hadir</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Alpha</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($santriData as $santriId => $santri)
                                @php 
                                    $persen = $santri['total']>0 ? round($santri['hadir']/$santri['total']*100,1) : 0;
                                    $detailPerHari = $santri['detail_per_hari'] ?? [];
                                    ksort($detailPerHari); // Urutkan tanggal
                                    $uniqueId = md5($kelas . '-' . $santriId); // Buat ID unik
                                @endphp
                                <tr data-toggle-detail="detail-{{ $uniqueId }}" 
                                    style="cursor: pointer;" 
                                    class="table-row-hover rekap-row" 
                                    data-nama="{{ strtolower($santri['nama']) }}">
                                    <td>
                                        <i class="fas fa-chevron-down me-2"></i>
                                        <strong>{{ $santri['nama'] }}</strong>
                                    </td>
                                    <td>{{ $santri['total'] }}</td>
                                    <td>{{ $santri['hadir'] }}</td>
                                    <td>{{ $santri['izin'] }}</td>
                                    <td>{{ $santri['sakit'] }}</td>
                                    <td>{{ $santri['alpha'] }}</td>
                                    <td><span class="badge {{ $persen>=75 ? 'bg-success' : ($persen>=50 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $persen }}%</span></td>
                                </tr>
                                <tr class="detail-row" id="detail-{{ $uniqueId }}" style="display: none;">
                                    <td colspan="8" class="p-0">
                                        <div class="p-3 bg-light">
                                            <h6 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Detail Kehadiran per Hari</h6>
                                            <div class="table-responsive recap-detail-table-wrap">
                                                <table class="table table-sm table-bordered mb-0 recap-detail-table">
                                                    <thead>
                                                        <tr class="bg-secondary text-white">
                                                            <th>Tanggal</th>
                                                            <th class="text-center">Hadir</th>
                                                            <th class="text-center">Izin</th>
                                                            <th class="text-center">Sakit</th>
                                                            <th class="text-center">Alpha</th>
                                                            <th>Kegiatan/Sholat</th>
                                                            <th>Jam</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($detailPerHari as $tanggal => $detail)
                                                            @php
                                                                $totalHari = $detail['hadir'] + $detail['izin'] + $detail['sakit'] + $detail['alpha'];
                                                                $tanggalObj = \Carbon\Carbon::parse($tanggal);
                                                                $tanggalFormatted = $tanggalObj->format('d/m/Y');
                                                                $dayName = $tanggalObj->format('l');
                                                                $dayNamesId = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                                                                $dayNameId = $dayNamesId[$dayName] ?? $dayName;
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $tanggalFormatted }}</strong><br>
                                                                    <small class="text-muted">{{ $dayNameId }}</small>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($detail['hadir'] > 0)
                                                                        <span class="badge bg-success">{{ $detail['hadir'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">0</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($detail['izin'] > 0)
                                                                        <span class="badge bg-warning text-dark">{{ $detail['izin'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">0</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($detail['sakit'] > 0)
                                                                        <span class="badge bg-info">{{ $detail['sakit'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">0</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($detail['alpha'] > 0)
                                                                        <span class="badge bg-danger">{{ $detail['alpha'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">0</span>
                                                                    @endif
                                                                </td>
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
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach($allKegiatan as $keg)
                                                                                @php
                                                                                    $displayKegiatan = preg_replace('/^(Ngaji|Diniyah|Tahfidz)\s+/iu', '', $keg);
                                                                                @endphp
                                                                                <span class="badge bg-primary">{{ $displayKegiatan }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(!empty($jamList))
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach($jamList as $jam)
                                                                                <span class="badge bg-dark">{{ $jam }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($totalHari > 0)
                                                                        @php
                                                                            $persenHari = round(($detail['hadir'] / $totalHari) * 100, 1);
                                                                        @endphp
                                                                        <span class="badge {{ $persenHari>=75 ? 'bg-success' : ($persenHari>=50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                                            {{ $persenHari }}%
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">Tidak ada data</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted">
                                                                    <i class="fas fa-info-circle me-2"></i>Tidak ada data kehadiran per hari
                                                                </td>
                                                            </tr>
                                                        @endforelse
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
        @endif
    @empty
        @if(!$kelasFilter)
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Belum ada data presensi untuk bulan <strong>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</strong>.
            </div>
        @endif
    @endforelse
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const toggleRows = document.querySelectorAll('[data-toggle-detail]');
    
    toggleRows.forEach(function(row) {
        row.addEventListener('click', function() {
            const detailId = this.getAttribute('data-toggle-detail');
            const detailRow = document.getElementById(detailId);
            const icon = this.querySelector('i.fas');
            
            if (detailRow) {
                
                if (detailRow.style.display === 'none' || detailRow.style.display === '') {
                    
                    detailRow.style.display = 'table-row';
                    
                    
                    if (icon) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                } else {
                    
                    detailRow.style.display = 'none';
                    
                    
                    if (icon) {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                }
            }
        });
    });

    
    const searchInput = document.getElementById('searchRekap');
    const resultCount = document.getElementById('rekapResultCount');
    const rows = document.querySelectorAll('.rekap-row');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            let hiddenCards = new Set();
            
            rows.forEach(function(row) {
                const nama = row.getAttribute('data-nama') || '';
                const card = row.closest('.card');
                
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
                        
                        const cardRows = card.querySelectorAll('.rekap-row');
                        const allHidden = Array.from(cardRows).every(r => r.style.display === 'none');
                        if (allHidden) {
                            hiddenCards.add(card);
                        }
                    }
                }
            });
            
            
            document.querySelectorAll('.card.mb-4').forEach(function(card) {
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


