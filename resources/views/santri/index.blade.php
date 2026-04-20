@extends('layouts.app')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }
    .page-btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.04em;
        transition: all 0.3s ease;
    }
    .page-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .page-header__button-group {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .page-table {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .page-table thead th {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.04em;
        padding: 20px 15px;
    }
    .page-table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
    }
    .page-table tbody tr:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transform: scale(1.01);
        transition: all 0.3s ease;
    }
    .badge-gender {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.04em;
    }
    .badge-male {
        background: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
        color: white;
    }
    .badge-female {
        background: linear-gradient(135deg, #c6922d 0%, #d9ad4f 100%);
        color: white;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .btn-action {
        border-radius: 20px;
        padding: 8px 15px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }
    .qr-dropdown {
        border-radius: 20px;
    }
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .dropdown-item {
        transition: all 0.3s ease;
    }
    .dropdown-item:hover {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: white;
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
    .santri-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
    .santri-table {
        min-width: 1100px;
    }
    .santri-scroll-hint {
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
        .page-header__actions {
            text-align: left !important;
            margin-top: 0.85rem;
        }
        .page-header__button-group {
            justify-content: stretch;
        }
        .page-header__actions .btn {
            width: 100%;
        }
        .santri-filter-form .btn {
            width: 100%;
        }
        .page-filter-summary {
            text-align: left !important;
        }
    }
    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }
        .page-header .lead {
            font-size: 0.98rem;
        }
        .santri-table-wrap {
            margin: 0 -0.35rem;
            padding: 0 0.35rem 0.25rem;
        }
        .santri-scroll-hint {
            display: block;
            color: #607066;
            font-size: 0.82rem;
            margin-bottom: 0.85rem;
        }
        .page-table tbody tr:hover {
            transform: none;
        }
    }
    @media (max-width: 575.98px) {
        .page-table thead th,
        .page-table tbody td {
            padding: 0.75rem 0.65rem;
        }
        .page-btn {
            padding: 0.9rem 1rem;
            letter-spacing: 0.04em;
        }
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-users me-3"></i>
                    Data Santri
                </h1>
                <p class="lead mb-0">Daftar santri yang tersimpan di SISANTRI.</p>
            </div>
            <div class="col-md-4 text-end page-header__actions">
                @if(auth()->user()->role === 'Admin')
                <div class="page-header__button-group">
                    <a href="{{ route('santri.import.template') }}" class="btn btn-outline-light page-btn">
                        <i class="fas fa-file-download me-2"></i>
                        Template
                    </a>
                    <button type="button" class="btn btn-light page-btn" data-bs-toggle="modal" data-bs-target="#santriImportModal">
                        <i class="fas fa-file-import me-2"></i>
                        Import File
                    </button>
                    <a href="{{ route('santri.create') }}" class="btn btn-light page-btn">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Santri
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRouteName ?? 'santri.index') }}" class="row align-items-end g-3 santri-filter-form">
                <div class="col-12 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               id="searchSantri"
                               name="q"
                               class="form-control" 
                               value="{{ $search ?? '' }}"
                               placeholder="Cari berdasarkan ID, Nama, Kelas, Golongan, atau Pembina...">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label for="kelas" class="form-label">Data Perkelas</label>
                    <select name="kelas" id="kelas" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach(['10', '11', '12'] as $kelasOption)
                            <option value="{{ $kelasOption }}" {{ (string) ($kelasFilter ?? '') === $kelasOption ? 'selected' : '' }}>
                                Kelas {{ $kelasOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 col-lg-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-md-12 text-end page-filter-summary">
                    <span class="text-muted">
                        Menampilkan {{ $santriList->count() }} dari {{ $santriList->total() }} data
                        @if(!empty($kelasFilter))
                            untuk Kelas {{ $kelasFilter }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($santriList->isEmpty())
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-muted mb-4"></i>
                <h4 class="text-muted">Tidak ada data santri ditemukan</h4>
                <p class="text-muted">Mulai dengan menambahkan data santri baru ke sistem</p>
                @if(auth()->user()->role === 'Admin')
                <a href="{{ route('santri.create') }}" class="btn btn-primary page-btn">
                    <i class="fas fa-plus me-2"></i>
                    Tambahkan Santri Pertama
                </a>
                @endif
            </div>
        </div>
    @else
        <div class="card page-table">
            <div class="card-body p-0">
                <div class="santri-scroll-hint px-3 pt-3">
                    <i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke samping untuk melihat seluruh kolom di layar kecil.
                </div>
                <div class="table-responsive santri-table-wrap">
                    <table class="table page-table mb-0 santri-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-list-ol me-2"></i>No</th>
                                <th><i class="fas fa-hashtag me-2"></i>ID Santri</th>
                                <th><i class="fas fa-user me-2"></i>Nama</th>
                                <th><i class="fas fa-graduation-cap me-2"></i>Kelas</th>
                                <th><i class="fas fa-layer-group me-2"></i>Golongan</th>
                                <th><i class="fas fa-venus-mars me-2"></i>Jenis Kelamin</th>
                                <th><i class="fas fa-user-tie me-2"></i>Pembina</th>
                                <th><i class="fas fa-cogs me-2"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="santriTableBody">
                            @foreach($santriList as $santri)
                            <tr class="santri-row" 
                                data-id="{{ strtolower($santri->id_santri) }}" 
                                data-nama="{{ strtolower($santri->nama) }}" 
                                data-kelas="{{ strtolower($santri->kelas) }}" 
                                data-golongan="{{ strtolower($santri->golongan) }}" 
                                data-pembina="{{ strtolower($santri->pembina) }}">
                                <td>
                                    <span class="fw-semibold">{{ $santriList->firstItem() + $loop->index }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $santri->id_santri }}</span>
                                </td>
                                <td>
                                    <strong>{{ $santri->nama }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $santri->kelas }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $santri->golongan }}</span>
                                </td>
                                <td>
                                    @if($santri->jenis_kelamin === 'Putra')
                                        <span class="badge badge-gender badge-male">
                                            <i class="fas fa-mars me-1"></i>{{ $santri->jenis_kelamin }}
                                        </span>
                                    @else
                                        <span class="badge badge-gender badge-female">
                                            <i class="fas fa-venus me-1"></i>{{ $santri->jenis_kelamin }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $santri->pembina }}</td>
                                <td>
                                    <div class="action-buttons">
                                        @if(auth()->user()->role === 'Admin')
                                            <a href="{{ route('santri.edit', $santri->id_santri) }}" 
                                               class="btn btn-warning btn-action" 
                                               title="Edit Santri">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if(Route::has('santri.qr'))
                                            <a href="{{ route('santri.qr', $santri->id_santri) }}" 
                                               target="_blank" 
                                               class="btn btn-info btn-action" 
                                               title="Lihat QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-success btn-action dropdown-toggle" 
                                                        type="button" 
                                                        data-bs-toggle="dropdown" 
                                                        title="Download QR Code">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="{{ route('santri.qr', ['id' => $santri->id_santri, 'download' => '1']) }}">
                                                            <i class="fas fa-image me-2"></i>SVG Format
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="{{ route('santri.qr', ['id' => $santri->id_santri, 'download' => '1', 'format' => 'pdf']) }}">
                                                            <i class="fas fa-file-pdf me-2"></i>PDF Format
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            @endif
                                            
                                            <form method="POST" 
                                                  action="{{ route('santri.destroy', $santri->id_santri) }}" 
                                                  style="display: inline-block;" 
                                                  onsubmit="return confirm('Yakin ingin menghapus santri {{ $santri->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger btn-action" 
                                                        title="Hapus Santri">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            
                                        @elseif(auth()->user()->role === 'Pembina' && Route::has('santri.qr'))
                                            <a href="{{ route('santri.qr', $santri->id_santri) }}" 
                                               target="_blank" 
                                               class="btn btn-info btn-action" 
                                               title="Lihat QR Code">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-success btn-action dropdown-toggle" 
                                                        type="button" 
                                                        data-bs-toggle="dropdown" 
                                                        title="Download QR Code">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="{{ route('santri.qr', ['id' => $santri->id_santri, 'download' => '1']) }}">
                                                            <i class="fas fa-image me-2"></i>SVG Format
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="{{ route('santri.qr', ['id' => $santri->id_santri, 'download' => '1', 'format' => 'pdf']) }}">
                                                            <i class="fas fa-file-pdf me-2"></i>PDF Format
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                        @else
                                            <a href="{{ route('santri.view.show', $santri->id_santri) }}" 
                                               class="btn btn-info btn-action" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
            <div class="pagination-summary">
                Data Santri halaman {{ $santriList->currentPage() }} dari {{ max($santriList->lastPage(), 1) }}
                @if(!empty($kelasFilter))
                    - Kelas {{ $kelasFilter }}
                @endif
            </div>
            <div>
                {{ $santriList->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@if(auth()->user()->role === 'Admin')
<div class="modal fade" id="santriImportModal" tabindex="-1" aria-labelledby="santriImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="santriImportModalLabel">
                    <i class="fas fa-file-import me-2"></i>Import Santri dari File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('santri.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($errors->santriImport->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->santriImport->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <div class="fw-semibold mb-2">Format file yang didukung</div>
                        <div class="small">
                            Gunakan file <strong>Excel</strong> atau <strong>CSV</strong> dengan kolom:
                            <code>id_santri</code>, <code>nama</code>, <code>jenis_kelamin</code>, <code>kelas</code>, <code>golongan</code>, <code>pembina</code>.
                        </div>
                        <div class="small mt-2">
                            Jika <code>id_santri</code> dikosongkan, sistem akan membuat ID otomatis.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="santri_import_file" class="form-label fw-semibold">Pilih File</label>
                        <input type="file"
                               class="form-control @if($errors->santriImport->has('import_file')) is-invalid @endif"
                               id="santri_import_file"
                               name="import_file"
                               accept=".xlsx,.xls,.csv,text/csv">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('santri.import.template') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i>Unduh Template
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Import Santri
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchSantri');
        
        if (searchInput && window.innerWidth >= 768) {
            searchInput.focus();
        }

        @if(auth()->user()->role === 'Admin' && $errors->santriImport->any())
        const importModal = document.getElementById('santriImportModal');
        if (importModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(importModal).show();
        }
        @endif
    });
</script>
@endsection

