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
    .user-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .user-header {
        background: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
        color: white;
        padding: 40px;
        text-align: center;
    }
    .avatar-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        background: rgba(255,255,255,0.2);
    }
    .info-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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
    .badge-role {
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0.04em;
        font-size: 1rem;
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
    .info-item {
        padding: 15px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
    .info-value {
        color: #6c757d;
        font-size: 1.1rem;
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
    .page-header__actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
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
            justify-content: flex-start;
            margin-top: 0.85rem;
        }
        .page-header__actions .btn {
            flex: 1 1 180px;
        }
        .user-header {
            padding: 1.5rem 1rem;
        }
    }
    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }
        .page-header .lead {
            font-size: 0.98rem;
        }
        .avatar-lg {
            width: 92px;
            height: 92px;
        }
        .badge-role {
            font-size: 0.88rem;
            padding: 0.65rem 1rem;
        }
        .info-value {
            font-size: 1rem;
        }
    }
    @media (max-width: 575.98px) {
        .page-btn {
            width: 100%;
            padding: 0.9rem 1rem;
            letter-spacing: 0.04em;
        }
        .card-body {
            padding: 1rem !important;
        }
        .modal-dialog {
            max-width: calc(100vw - 1rem);
            margin: 0.75rem auto !important;
        }
    }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-user me-3"></i>
                    Detail User
                </h1>
                <p class="lead mb-0">Data lengkap pengguna.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="page-header__actions">
                @if(auth()->user()->role === 'Admin')
                    @if($user->role === 'Admin')
                    <a href="{{ route('profile.edit') }}" class="btn btn-warning page-btn me-2">
                        <i class="fas fa-user-shield me-2"></i>
                        Kelola Admin
                    </a>
                    @else
                    <a href="{{ route('user_roles.edit', $user->id) }}" class="btn btn-warning page-btn me-2">
                        <i class="fas fa-edit me-2"></i>
                        Edit
                    </a>
                    @endif
                @endif
                <a href="{{ auth()->user()->role === 'Admin' ? route('user_roles.index') : route('user_roles.view') }}" class="btn btn-light page-btn">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali
                </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-4">
            <div class="card user-card">
                <div class="user-header">
                    <div class="avatar-lg">
                        <i class="fas fa-user fa-lg"></i>
                    </div>
                    <h3 class="mb-2">{{ $user->name }}</h3>
                    <p class="mb-3">{{ $user->email }}</p>
                    
                    @if($user->role === 'Admin')
                        <span class="badge badge-role badge-admin">
                            <i class="fas fa-crown me-2"></i>{{ $user->role }}
                        </span>
                    @elseif($user->role === 'Pembina')
                        <span class="badge badge-role badge-pembina">
                            <i class="fas fa-user-tie me-2"></i>{{ $user->role }}
                        </span>
                    @else
                        <span class="badge badge-role badge-ustadz">
                            <i class="fas fa-chalkboard-teacher me-2"></i>{{ $user->role }}
                        </span>
                    @endif
                </div>
                
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h5 class="text-muted">Informasi Akun</h5>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-hashtag me-2"></i>ID User
                        </div>
                        <div class="info-value">
                            <span class="badge bg-secondary fs-6">{{ $user->id }}</span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-id-card me-2"></i>Nama Lengkap
                        </div>
                        <div class="info-value">{{ $user->nama_lengkap }}</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-plus me-2"></i>Bergabung
                        </div>
                        <div class="info-value">
                            {{ $user->created_at ? $user->created_at->format('d F Y, H:i') : 'Tidak diketahui' }}
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-edit me-2"></i>Terakhir Update
                        </div>
                        <div class="info-value">
                            {{ $user->updated_at ? $user->updated_at->format('d F Y, H:i') : 'Tidak diketahui' }}
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-clock me-2"></i>Terakhir Login
                        </div>
                        <div class="info-value">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d F Y, H:i') }}
                                <br>
                                <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Belum pernah login</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            
            <div class="card info-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Dasar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user me-2"></i>Nama Pengguna
                                </div>
                                <div class="info-value">{{ $user->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </div>
                                <div class="info-value">{{ $user->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card info-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tag me-2"></i>
                        Informasi Role
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-shield-alt me-2"></i>Role
                                </div>
                                <div class="info-value">
                                    @if($user->role === 'Admin')
                                        <span class="badge badge-role badge-admin">
                                            <i class="fas fa-crown me-1"></i>{{ $user->role }}
                                        </span>
                                    @elseif($user->role === 'Pembina')
                                        <span class="badge badge-role badge-pembina">
                                            <i class="fas fa-user-tie me-1"></i>{{ $user->role }}
                                        </span>
                                    @else
                                        <span class="badge badge-role badge-ustadz">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>{{ $user->role }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-key me-2"></i>Status Akun
                                </div>
                                <div class="info-value">
                                @php
                                    $isActive = auth()->check() && auth()->user()->id === $user->id;
                                @endphp
                                    <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="fas fa-{{ $isActive ? 'check-circle' : 'clock' }} me-1"></i>
                                        {{ $isActive ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    @if($user->last_login_at)
                                        <br>
                                        <small class="text-muted mt-1 d-block">
                                            Terakhir login: {{ $user->last_login_at->diffForHumans() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card info-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Informasi Tambahan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-id-card me-2"></i>Nama Lengkap
                                </div>
                                <div class="info-value">{{ $user->nama_lengkap }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Penempatan / Akses
                                </div>
                                <div class="info-value">{{ $user->kelas_kitab_hendel ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            @if(auth()->user()->role === 'Admin')
            <div class="card info-card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Aksi
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->role === 'Admin')
                        <div class="alert alert-info mb-3">
                            Akun admin utama dilindungi. Pengelolaan nama, email, dan password dilakukan dari halaman profil sendiri.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary page-btn w-100">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Kelola Profil Admin
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('user_roles.index') }}" class="btn btn-info page-btn w-100">
                                    <i class="fas fa-list me-2"></i>
                                    Daftar User
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-3">
                                <a href="{{ route('user_roles.edit', $user->id) }}" class="btn btn-warning page-btn w-100">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit User
                                </a>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <button type="button" 
                                        class="btn btn-secondary page-btn w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#updatePasswordModal{{ $user->id }}">
                                    <i class="fas fa-key me-2"></i>
                                    Ubah Password
                                </button>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <a href="{{ route('user_roles.index') }}" class="btn btn-info page-btn w-100">
                                    <i class="fas fa-list me-2"></i>
                                    Daftar User
                                </a>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <form method="POST" action="{{ route('user_roles.destroy', $user->id) }}" 
                                      style="display: inline-block;" 
                                      onsubmit="return confirm('Yakin hapus user {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger page-btn w-100">
                                        <i class="fas fa-trash me-2"></i>
                                        Hapus User
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            
            @if($user->role !== 'Admin')
            <div class="modal fade" id="updatePasswordModal{{ $user->id }}" tabindex="-1" aria-labelledby="updatePasswordModalLabel{{ $user->id }}" aria-hidden="true" role="dialog" data-bs-backdrop="false" data-bs-keyboard="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="updatePasswordModalLabel{{ $user->id }}">
                                <i class="fas fa-key me-2"></i>Ubah Password
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('user_roles.update-password', ['user' => $user->id]) }}" method="POST" id="updatePasswordForm{{ $user->id }}">
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
                                    Ubah password untuk user: <strong>{{ $user->name }}</strong> ({{ $user->email }})
                                </div>
                                <div class="mb-3">
                                    <label for="password{{ $user->id }}" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password Baru
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password{{ $user->id }}" 
                                           name="password" 
                                           required 
                                           minlength="8"
                                           placeholder="Masukkan password baru (min. 8 karakter)">
                                    <small class="form-text text-muted">Password minimal 8 karakter</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation{{ $user->id }}" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Konfirmasi Password
                                    </label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation{{ $user->id }}" 
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
                                <button type="submit" class="btn btn-warning" id="updatePasswordBtn{{ $user->id }}">
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
            @else
            <div class="card info-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Aksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <a href="{{ route('user_roles.view') }}" class="btn btn-info page-btn w-100">
                                <i class="fas fa-list me-2"></i>
                                Kembali ke Daftar User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(auth()->user()->role === 'Admin')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($user->role !== 'Admin')
    
    const updatePasswordModal{{ $user->id }} = document.getElementById('updatePasswordModal{{ $user->id }}');
    if (updatePasswordModal{{ $user->id }}) {
        updatePasswordModal{{ $user->id }}.addEventListener('shown.bs.modal', function() {
            const passwordInput = document.getElementById('password{{ $user->id }}');
            if (passwordInput) {
                setTimeout(() => passwordInput.focus(), 100);
            }
        });
    }
    
    const updatePasswordForm{{ $user->id }} = document.getElementById('updatePasswordForm{{ $user->id }}');
    const updatePasswordBtn{{ $user->id }} = document.getElementById('updatePasswordBtn{{ $user->id }}');
    
    if (updatePasswordForm{{ $user->id }} && updatePasswordBtn{{ $user->id }}) {
        updatePasswordForm{{ $user->id }}.addEventListener('submit', function(e) {
            const password = document.getElementById('password{{ $user->id }}');
            const passwordConfirmation = document.getElementById('password_confirmation{{ $user->id }}');
            
            if (!password || !passwordConfirmation) {
                console.error('Password input fields not found');
                return;
            }
            
            const passwordValue = password.value;
            const passwordConfirmationValue = passwordConfirmation.value;
            
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
            
            
            const btnText = updatePasswordBtn{{ $user->id }}.querySelector('.btn-text');
            const btnLoading = updatePasswordBtn{{ $user->id }}.querySelector('.btn-loading');
            
            if (btnText && btnLoading) {
                btnText.classList.add('d-none');
                btnLoading.classList.remove('d-none');
            }
            updatePasswordBtn{{ $user->id }}.disabled = true;
        });
    } else {
        console.error('Update password form or button not found for user {{ $user->id }}');
    }
    @endif
});
</script>
@endif
@endsection

