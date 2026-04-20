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
    .form-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .form-header {
        background: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
        color: white;
        padding: 20px;
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
    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        padding: 12px 20px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0f5c4d;
        box-shadow: 0 0 0 0.2rem rgba(15, 92, 77, 0.18);
        outline: none;
    }
    .card-body {
        background: #fff;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .icon-input {
        position: relative;
    }
    .icon-input i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
    }
    .icon-input .form-control {
        padding-left: 45px;
    }
    .user-info-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .page-header__actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .form-actions {
        display: flex;
        justify-content: space-between;
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
            width: 100%;
        }
        .form-card .card-body {
            padding: 1rem !important;
        }
        .user-info-card {
            margin-bottom: 1rem;
        }
    }
    @media (max-width: 767.98px) {
        .page-header h1.display-5 {
            font-size: 1.45rem;
        }
        .page-header .lead {
            font-size: 0.98rem;
        }
    }
    @media (max-width: 575.98px) {
        .page-btn {
            width: 100%;
            padding: 0.9rem 1rem;
            letter-spacing: 0.04em;
        }
        .form-actions > * {
            flex: 1 1 100%;
        }
    }
</style>

<div class="container-fluid">
    
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-user-edit me-3"></i>
                    Edit User
                </h1>
                <p class="lead mb-0">Perbarui data pengguna: {{ $user->name }}</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="page-header__actions">
                <a href="{{ route('user_roles.show', $user->id) }}" class="btn btn-light page-btn me-2">
                    <i class="fas fa-eye me-2"></i>
                    Lihat Detail
                </a>
                <a href="{{ route('user_roles.index') }}" class="btn btn-light page-btn">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali
                </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-4">
            <div class="card user-info-card">
                <div class="card-body text-center">
                    <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                        <i class="fas fa-user fa-lg text-white"></i>
                    </div>
                    <h4 class="card-title">{{ $user->name }}</h4>
                    <p class="card-text text-muted">{{ $user->email }}</p>
                    
                    @if($user->role === 'Admin')
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="fas fa-crown me-1"></i>{{ $user->role }}
                        </span>
                    @elseif($user->role === 'Pembina')
                        <span class="badge bg-warning fs-6 px-3 py-2">
                            <i class="fas fa-user-tie me-1"></i>{{ $user->role }}
                        </span>
                    @else
                        <span class="badge bg-info fs-6 px-3 py-2">
                            <i class="fas fa-chalkboard-teacher me-1"></i>{{ $user->role }}
                        </span>
                    @endif
                    
                    <hr>
                    <div class="text-start">
                        <p class="mb-2">
                            <strong><i class="fas fa-id-card me-2"></i>Nama Lengkap:</strong><br>
                            <span class="text-muted">{{ $user->nama_lengkap }}</span>
                        </p>
                        @if($user->kelas_kitab_hendel)
                        <p class="mb-2">
                            <strong><i class="fas fa-book me-2"></i>Kelas/Kitab:</strong><br>
                            <span class="text-muted">{{ $user->kelas_kitab_hendel }}</span>
                        </p>
                        @endif
                        <p class="mb-0">
                            <strong><i class="fas fa-calendar me-2"></i>Bergabung:</strong><br>
                            <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Tidak diketahui' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="form-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Form Edit User
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('user_roles.update', $user->id) }}" id="editUserForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-user me-2"></i>
                                    Informasi Dasar
                                </h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nama Pengguna</label>
                                    <div class="icon-input">
                                        <i class="fas fa-user"></i>
                                        <input type="text" 
                                               name="name" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $user->name) }}" 
                                               placeholder="Masukkan nama pengguna"
                                               required>
                                    </div>
                                    @error('name') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="icon-input">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               value="{{ old('email', $user->email) }}" 
                                               placeholder="Masukkan email"
                                               required>
                                    </div>
                                    @error('email') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <div class="icon-input">
                                        <i class="fas fa-user-tag"></i>
                                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                            <option value="">-- Pilih Role --</option>
                                            @foreach(($roleOptions ?? ['Pembina', 'Ustadz Pengajar']) as $roleOption)
                                                <option value="{{ $roleOption }}" {{ old('role', $user->role) === $roleOption ? 'selected' : '' }}>
                                                    {{ $roleOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(!in_array('Admin', $roleOptions ?? [], true))
                                        <small class="text-muted">
                                            Role admin dikunci sebagai admin utama tunggal dan tidak bisa dipindahkan ke user lain.
                                        </small>
                                    @endif
                                    @error('role') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>

                            
                            <div class="col-md-6">
                                <h5 class="mb-3 text-success">
                                    <i class="fas fa-id-card me-2"></i>
                                    Informasi Tambahan
                                </h5>

                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <div class="icon-input">
                                        <i class="fas fa-id-card"></i>
                                        <input type="text" 
                                               name="nama_lengkap" 
                                               class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                               value="{{ old('nama_lengkap', $user->nama_lengkap) }}" 
                                               placeholder="Masukkan nama lengkap"
                                               required>
                                    </div>
                                    @error('nama_lengkap') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Penempatan / Akses</label>
                                    <div class="icon-input">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <select name="kelas_kitab_hendel"
                                                id="kelas_kitab_hendel"
                                                class="form-select @error('kelas_kitab_hendel') is-invalid @enderror">
                                            <option value="">Pilih sesuai role</option>
                                        </select>
                                    </div>
                                    <small class="text-muted">
                                        Untuk pembina pilih kelas binaan. Untuk ustadz pengajar pilih golongan yang diampu.
                                    </small>
                                    @error('kelas_kitab_hendel') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>

                            </div>
                        </div>

                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-warning">
                                    <i class="fas fa-lock me-2"></i>
                                    Ubah Password (Opsional)
                                </h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Kosongkan jika tidak ingin mengubah password
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <div class="icon-input">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" 
                                               name="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="Masukkan password baru">
                                    </div>
                                    @error('password') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div class="icon-input">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               class="form-control" 
                                               placeholder="Konfirmasi password baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-actions">
                                    <a href="{{ route('user_roles.index') }}" class="btn btn-secondary page-btn">
                                        <i class="fas fa-times me-2"></i>
                                        Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary page-btn" id="submitBtn">
                                        <span class="btn-text">
                                            <i class="fas fa-save me-2"></i>Update User
                                        </span>
                                        <span class="btn-loading d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Memperbarui...
                                        </span>
                                    </button>
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const roleSelect = document.querySelector('select[name="role"]');
    const accessSelect = document.getElementById('kelas_kitab_hendel');
    const accessOptionsByRole = @json($accessOptionsByRole ?? []);
    const currentAccessValue = @json(old('kelas_kitab_hendel', $user->kelas_kitab_hendel));

    function renderAccessOptions() {
        if (!roleSelect || !accessSelect) {
            return;
        }

        const role = roleSelect.value || '';
        const options = accessOptionsByRole[role] || [];
        const preservedValue = accessSelect.dataset.currentValue || currentAccessValue || '';

        accessSelect.innerHTML = '<option value="">Pilih sesuai role</option>';

        options.forEach(function(optionValue) {
            const option = document.createElement('option');
            option.value = optionValue;
            option.textContent = optionValue;

            if (optionValue === preservedValue) {
                option.selected = true;
            }

            accessSelect.appendChild(option);
        });

        const shouldEnable = role === 'Pembina' || role === 'Ustadz Pengajar';
        accessSelect.disabled = !shouldEnable;

        if (!shouldEnable) {
            accessSelect.value = '';
        }

        accessSelect.dataset.currentValue = accessSelect.value || preservedValue || '';
    }

    if (roleSelect && accessSelect) {
        accessSelect.dataset.currentValue = currentAccessValue || '';
        roleSelect.addEventListener('change', function() {
            accessSelect.dataset.currentValue = '';
            renderAccessOptions();
        });
        renderAccessOptions();
    }
    
    form.addEventListener('submit', function(e) {
        
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;
        
        if (password && password !== confirmPassword) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak cocok!');
            return;
        }
        
        if (password && password.length < 8) {
            e.preventDefault();
            alert('Password minimal 8 karakter!');
            return;
        }
        
        
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        
        
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        inputs.forEach(input => {
            if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                input.readOnly = true;
            }
        });
    });
    
    
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirmation"]');
    
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>
@endsection

