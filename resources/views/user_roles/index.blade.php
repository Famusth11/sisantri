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
    .page-table tbody tr {
        transition: all 0.3s ease;
    }
    .page-table tbody tr:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transform: translateX(5px);
    }
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    .badge-role {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.04em;
    }
    .badge-admin {
        background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
        color: white;
    }
    .badge-pembina {
        background: linear-gradient(135deg, #c6922d 0%, #d9ad4f 100%);
        color: white;
    }
    .badge-ustadz {
        background: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
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
    .modal {
        z-index: 1055 !important;
        pointer-events: auto;
    }
    .modal-backdrop {
        z-index: 1050 !important;
        background-color: rgba(0, 0, 0, 0.5);
    }
    .modal-dialog {
        z-index: 1060 !important;
        pointer-events: auto;
        margin: 5% auto !important;
        max-width: 500px;
    }
    .modal-content {
        pointer-events: auto;
        position: relative;
        z-index: 1061 !important;
    }
    .modal.show {
        display: block !important;
    }
    .modal-dialog-centered {
        min-height: calc(100% - 3.5rem);
        display: flex;
        align-items: flex-start;
        margin-top: 5% !important;
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
    .user-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
    .user-table {
        min-width: 1260px;
    }
    .user-scroll-hint {
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
        .user-filter-form .btn {
            width: 100%;
        }
        .page-filter-summary {
            justify-content: flex-start !important;
        }
        .modal-dialog {
            max-width: calc(100vw - 1rem);
            margin: 0.75rem auto !important;
        }
    }
    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }
        .page-header .lead {
            font-size: 0.98rem;
        }
        .user-table-wrap {
            margin: 0 -0.35rem;
            padding: 0 0.35rem 0.25rem;
        }
        .user-scroll-hint {
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
                    <i class="fas fa-user-cog me-3"></i>
                    Manajemen Pengguna
                </h1>
                <p class="lead mb-0">Daftar pengguna dan hak akses di SISANTRI.</p>
            </div>
            <div class="col-md-4 text-end page-header__actions">
                @if(auth()->user()->role === 'Admin')
                <div class="page-header__button-group">
                    <a href="{{ route('user_roles.import.template') }}" class="btn btn-outline-light page-btn">
                        <i class="fas fa-file-download me-2"></i>
                        Template
                    </a>
                    <button type="button" class="btn btn-light page-btn" data-bs-toggle="modal" data-bs-target="#userImportModal">
                        <i class="fas fa-file-import me-2"></i>
                        Import File
                    </button>
                    <a href="{{ route('user_roles.create') }}" class="btn btn-light page-btn">
                        <i class="fas fa-plus me-2"></i>
                        Tambah User
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4" style="border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <form method="GET" action="{{ route($indexRouteName ?? 'user_roles.index') }}" class="row align-items-end g-3 user-filter-form">
                <div class="col-12 col-lg-6">
                    <div class="input-group" style="border-radius: 12px; overflow: hidden;">
                        <span class="input-group-text bg-light border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               id="searchUser" 
                               name="q"
                               class="form-control border-0" 
                               style="padding: 12px 15px;"
                               value="{{ $search ?? '' }}"
                               placeholder="Cari berdasarkan nama, email, atau role...">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Role</option>
                        @foreach(['Admin', 'Pembina', 'Ustadz Pengajar'] as $roleOption)
                            <option value="{{ $roleOption }}" {{ ($roleFilter ?? '') === $roleOption ? 'selected' : '' }}>
                                {{ $roleOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 col-lg-3">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <div class="d-flex align-items-center justify-content-end page-filter-summary">
                        <i class="fas fa-users text-muted me-2"></i>
                        <span class="text-muted fw-semibold">
                            Menampilkan <span class="text-primary">{{ $users->count() }}</span> dari 
                            <span class="text-primary">{{ $users->total() }}</span> user
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-table">
        <div class="card-body p-0">
            <div class="user-scroll-hint px-3 pt-3">
                <i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke samping untuk melihat seluruh kolom di layar kecil.
            </div>
            <div class="table-responsive user-table-wrap">
                <table class="table page-table mb-0 user-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-user me-2"></i>Nama & Email</th>
                            <th><i class="fas fa-user-tag me-2"></i>Role</th>
                            <th><i class="fas fa-id-card me-2"></i>Nama Lengkap</th>
                            <th><i class="fas fa-info-circle me-2"></i>Status</th>
                            <th><i class="fas fa-clock me-2"></i>Terakhir Login</th>
                            <th><i class="fas fa-calendar me-2"></i>Bergabung</th>
                            <th><i class="fas fa-cogs me-2"></i>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @forelse($users as $user)
                        <tr class="user-row" 
                            data-name="{{ strtolower($user->name) }}" 
                            data-email="{{ strtolower($user->email) }}" 
                            data-role="{{ strtolower($user->role) }}">
                            <td>
                                <span class="badge bg-secondary">{{ $user->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">{{ $user->name }}</strong>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'Admin')
                                    <span class="badge badge-role badge-admin">{{ $user->role }}</span>
                                @elseif($user->role === 'Pembina')
                                    <span class="badge badge-role badge-pembina">{{ $user->role }}</span>
                                @else
                                    <span class="badge badge-role badge-ustadz">{{ $user->role }}</span>
                                @endif
                            </td>
                            <td>{{ $user->nama_lengkap }}</td>
                            <td>
                                @php
                                    $isActive = auth()->check() && auth()->user()->id === $user->id;
                                @endphp
                                <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas fa-{{ $isActive ? 'check-circle' : 'clock' }} me-1"></i>
                                    {{ $isActive ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    @if($user->last_login_at)
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $user->last_login_at->diffForHumans() }}
                                        <br>
                                        <span style="font-size: 0.75rem;">
                                            {{ $user->last_login_at->format('d/m/Y H:i') }}
                                        </span>
                                @else
                                        <span class="text-muted">Belum pernah login</span>
                                @endif
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                                </small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if(auth()->user()->role === 'Admin')
                                        <a href="{{ route('user_roles.show', $user->id) }}" 
                                           class="btn btn-info btn-action" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user->role !== 'Admin')
                                            <a href="{{ route('user_roles.edit', $user->id) }}" 
                                               class="btn btn-warning btn-action" 
                                               title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-secondary btn-action" 
                                                    title="Ubah Password"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updatePasswordModal"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-email="{{ $user->email }}"
                                                    data-update-action="{{ route('user_roles.update-password', ['user' => $user->id]) }}">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <form method="POST" action="{{ route('user_roles.destroy', $user->id) }}" 
                                                  style="display: inline-block;" 
                                                  onsubmit="return confirm('Yakin hapus {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action" title="Hapus User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('profile.edit') }}"
                                               class="btn btn-outline-secondary btn-action"
                                               title="Kelola Admin Utama">
                                                <i class="fas fa-shield-alt"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('user_roles.view.show', $user->id) }}" 
                                           class="btn btn-info btn-action" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-lg mb-3"></i>
                                    <h5>Tidak ada user ditemukan</h5>
                                    <p>Mulai dengan menambahkan user baru</p>
                                    @if(auth()->user()->role === 'Admin')
                                    <a href="{{ route('user_roles.create') }}" class="btn btn-primary page-btn">
                                        <i class="fas fa-plus me-2"></i>Tambah User Pertama
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
        <div class="pagination-summary">
            Manajemen User halaman {{ $users->currentPage() }} dari {{ max($users->lastPage(), 1) }}
        </div>
        <div>
            {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

@if(auth()->user()->role === 'Admin')
    <div class="modal fade" id="userImportModal" tabindex="-1" aria-labelledby="userImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="userImportModalLabel">
                        <i class="fas fa-file-import me-2"></i>Import User dari File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form method="POST" action="{{ route('user_roles.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if($errors->userImport->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->userImport->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <div class="fw-semibold mb-2">Format file yang didukung</div>
                            <div class="small">
                                Gunakan file <strong>Excel</strong> atau <strong>CSV</strong> dengan kolom:
                                <code>name</code>, <code>email</code>, <code>role</code>, <code>nama_lengkap</code>, <code>kelas_kitab_hendel</code>, <code>password</code>.
                            </div>
                            <div class="small mt-2">
                                Role impor hanya untuk <code>Pembina</code> atau <code>Ustadz Pengajar</code>. Jika <code>password</code> user baru dikosongkan, sistem akan memakai password default <code>password123</code>.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="user_import_file" class="form-label fw-semibold">Pilih File</label>
                            <input type="file"
                                   class="form-control @if($errors->userImport->has('import_file')) is-invalid @endif"
                                   id="user_import_file"
                                   name="import_file"
                                   accept=".xlsx,.xls,.csv,text/csv">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('user_roles.import.template') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-download me-2"></i>Unduh Template
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Import User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel" aria-hidden="true" role="dialog" data-bs-backdrop="false" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="updatePasswordModalLabel">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" method="POST" id="updatePasswordForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Ubah password untuk user: <strong id="modalUserName">-</strong> (<span id="modalUserEmail">-</span>)
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password Baru
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   placeholder="Masukkan password baru (min. 8 karakter)">
                            <small class="form-text text-muted">Password minimal 8 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-2"></i>Konfirmasi Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="8"
                                   placeholder="Konfirmasi password baru">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-warning" id="updatePasswordBtn">
                            <span class="btn-text">
                                <i class="fas fa-key me-2"></i>Ubah Password
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    if (searchInput && window.innerWidth >= 768) {
        searchInput.focus();
    }

    @if(auth()->user()->role === 'Admin')
        @if($errors->userImport->any())
            const userImportModal = document.getElementById('userImportModal');
            if (userImportModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(userImportModal).show();
            }
        @endif

        const updatePasswordModal = document.getElementById('updatePasswordModal');
        const updatePasswordForm = document.getElementById('updatePasswordForm');
        const updatePasswordBtn = document.getElementById('updatePasswordBtn');
        const modalUserName = document.getElementById('modalUserName');
        const modalUserEmail = document.getElementById('modalUserEmail');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');

        if (updatePasswordModal) {
            updatePasswordModal.addEventListener('show.bs.modal', function(event) {
                const triggerButton = event.relatedTarget;
                if (!triggerButton) {
                    return;
                }

                updatePasswordForm.action = triggerButton.getAttribute('data-update-action') || '#';
                modalUserName.textContent = triggerButton.getAttribute('data-user-name') || '-';
                modalUserEmail.textContent = triggerButton.getAttribute('data-user-email') || '-';

                if (passwordInput) {
                    passwordInput.value = '';
                }
                if (passwordConfirmationInput) {
                    passwordConfirmationInput.value = '';
                }

                if (updatePasswordBtn) {
                    updatePasswordBtn.disabled = false;
                    const btnText = updatePasswordBtn.querySelector('.btn-text');
                    const btnLoading = updatePasswordBtn.querySelector('.btn-loading');
                    if (btnText && btnLoading) {
                        btnText.classList.remove('d-none');
                        btnLoading.classList.add('d-none');
                    }
                }
            });

            updatePasswordModal.addEventListener('shown.bs.modal', function() {
                if (passwordInput) {
                    setTimeout(() => passwordInput.focus(), 100);
                }
            });
        }

        if (updatePasswordForm && updatePasswordBtn) {
            updatePasswordForm.addEventListener('submit', function(e) {
                const passwordValue = passwordInput ? passwordInput.value : '';
                const passwordConfirmationValue = passwordConfirmationInput ? passwordConfirmationInput.value : '';

                if (passwordValue !== passwordConfirmationValue) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok!');
                    return false;
                }

                if (passwordValue.length < 8) {
                    e.preventDefault();
                    alert('Password minimal 8 karakter!');
                    return false;
                }

                const btnText = updatePasswordBtn.querySelector('.btn-text');
                const btnLoading = updatePasswordBtn.querySelector('.btn-loading');

                if (btnText && btnLoading) {
                    btnText.classList.add('d-none');
                    btnLoading.classList.remove('d-none');
                }
                updatePasswordBtn.disabled = true;
            });
        }
    @endif
});
</script>
@endsection

