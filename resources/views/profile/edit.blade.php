@extends('layouts.app')

@section('content')
<style>
    .profile-page-header {
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        border-radius: 1rem;
        border: 1px solid rgba(207, 229, 220, 0.7);
        box-shadow: 0 10px 24px rgba(15, 92, 77, 0.16);
    }

    .profile-section-card {
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(65, 77, 67, 0.1);
        overflow: hidden;
    }

    .profile-section-card .card-header {
        background: linear-gradient(135deg, #dff2ea 0%, #eef6f2 100%) !important;
        color: #20352f;
        border-bottom: 1px solid #ddd5c3;
    }

    .profile-avatar-panel {
        background: linear-gradient(180deg, #fffdf8 0%, #f4efe2 100%);
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .profile-avatar-icon {
        color: #0f5c4d;
    }

    @media (max-width: 991.98px) {
        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .profile-page-header {
            padding: 1rem 1.05rem;
        }

        .profile-page-header h2 {
            font-size: 1.35rem;
        }

        .profile-section-card .card-body {
            padding: 1rem;
        }

        .profile-avatar-panel {
            margin-top: 1rem;
        }
    }
</style>
<div class="container-fluid">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-section-card">
                <div class="card-header profile-page-header">
                    <h2 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Profil
                    </h2>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-section-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Informasi Profil
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                        <div class="col-md-4">
                            <div class="text-center profile-avatar-panel">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle fa-2x profile-avatar-icon"></i>
                                </div>
                                <h5>{{ $user->nama_lengkap ?? $user->name }}</h5>
                                <p class="text-muted">{{ $user->role }}</p>
                                @if($user->role === 'Pembina' || $user->role === 'Ustadz Pengajar')
                                <p class="text-muted">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    {{ $user->kelas_kitab_hendel }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    @if($user->role === 'Admin')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-section-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Perlindungan Admin
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        Akun admin utama adalah pengendali utama sistem. Akun ini tidak bisa dihapus dan tidak bisa diganti menjadi role lain.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    
    @if($user->role !== 'Admin')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-section-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        Ubah Password
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-section-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-trash-alt me-2"></i>
                        Hapus Akun
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

