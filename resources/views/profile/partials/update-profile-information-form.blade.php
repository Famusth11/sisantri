<div class="profile-form">
    <div class="mb-4">
        <h4 class="text-primary mb-2">
            <i class="fas fa-user-edit me-2"></i>
            Informasi Profil
        </h4>
        <p class="text-muted">
            Perbarui nama dan email akun Anda.
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate>
        @csrf
        @method('patch')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">
                    <i class="fas fa-user me-2"></i>{{ __('Username') }}
                </label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}" 
                       required 
                       autofocus 
                       autocomplete="name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="nama_lengkap" class="form-label">
                    <i class="fas fa-id-card me-2"></i>{{ __('Nama Lengkap') }}
                </label>
                <input type="text" 
                       class="form-control @error('nama_lengkap') is-invalid @enderror" 
                       id="nama_lengkap" 
                       name="nama_lengkap" 
                       value="{{ old('nama_lengkap', $user->nama_lengkap) }}" 
                       autocomplete="name">
                @error('nama_lengkap')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>{{ __('Email') }}
                </label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $user->email) }}" 
                       required 
                       autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="role" class="form-label">
                    <i class="fas fa-user-tag me-2"></i>{{ __('Role') }}
                </label>
                <input type="text" 
                       class="form-control bg-light" 
                       id="role" 
                       value="{{ $user->role }}" 
                       readonly>
            </div>
        </div>

        @if($user->role === 'Pembina' || $user->role === 'Ustadz Pengajar')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="kelas_kitab_hendel" class="form-label">
                    <i class="fas fa-graduation-cap me-2"></i>{{ __('Kelas/Kitab yang Dihandle') }}
                </label>
                <input type="text" 
                       class="form-control bg-light" 
                       id="kelas_kitab_hendel" 
                       value="{{ $user->kelas_kitab_hendel }}" 
                       readonly>
            </div>
        </div>
        @endif

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Email Anda belum terverifikasi.</strong>
                <button form="send-verification" class="btn btn-link p-0 ms-2">
                    Kirim ulang email verifikasi
                </button>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Tautan verifikasi baru sudah dikirim ke email Anda.
                </div>
            @endif
        @endif

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Tersimpan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </form>
</div>

