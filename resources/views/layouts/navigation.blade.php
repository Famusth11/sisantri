@php
    $user = Auth::user();
    $isAdmin = $user->role === 'Admin';
@endphp

<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand__mark">
            <i class="fas fa-mosque"></i>
        </div>
        <div>
            <div class="sidebar-brand__title">SISANTRI</div>
            <div class="sidebar-brand__caption">Sistem Informasi Santri</div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="sidebar-user__avatar">
            {{ strtoupper(substr($user->nama_lengkap ?? $user->name, 0, 1)) }}
        </div>
        <div>
            <div class="sidebar-user__name">{{ $user->nama_lengkap ?? $user->name }}</div>
            <div class="sidebar-user__role">{{ $user->role }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <a class="sidebar-link {{ request()->routeIs('santri.*') || request()->routeIs('santri.view*') ? 'is-active' : '' }}"
           href="{{ $isAdmin ? route('santri.index') : route('santri.view') }}">
            <i class="fas fa-users"></i>
            <span>Santri</span>
        </a>

        <div class="sidebar-group">
            <div class="sidebar-group__label">Presensi</div>
            @if($user->role !== 'Ustadz Pengajar')
                <a class="sidebar-link {{ request()->routeIs('absensi.sholat') ? 'is-active' : '' }}" href="{{ route('absensi.sholat') }}">
                    <i class="fas fa-pray"></i>
                    <span>Presensi Sholat</span>
                </a>
            @endif
            <a class="sidebar-link {{ request()->routeIs('absensi.diniyah') ? 'is-active' : '' }}" href="{{ route('absensi.diniyah') }}">
                <i class="fas fa-book-open"></i>
                <span>Presensi Diniyah</span>
            </a>
            @if($isAdmin)
                <a class="sidebar-link {{ request()->routeIs('jadwal_diniyah.*') ? 'is-active' : '' }}" href="{{ route('jadwal_diniyah.index') }}">
                    <i class="fas fa-book-medical"></i>
                    <span>Jadwal Diniyah</span>
                </a>
            @endif
            <a class="sidebar-link {{ request()->routeIs('absensi.rekapBulanan') ? 'is-active' : '' }}" href="{{ route('absensi.rekapBulanan') }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Rekap Diniyah</span>
            </a>
            <a class="sidebar-link {{ request()->routeIs('absensi.rekapBulananSholat') ? 'is-active' : '' }}" href="{{ route('absensi.rekapBulananSholat') }}">
                <i class="fas fa-chart-pie"></i>
                <span>Rekap Sholat</span>
            </a>
        </div>

        <a class="sidebar-link {{ request()->routeIs('user_roles.*') || request()->routeIs('user_roles.view*') ? 'is-active' : '' }}"
           href="{{ $isAdmin ? route('user_roles.index') : route('user_roles.view') }}">
            <i class="fas fa-user-cog"></i>
            <span>{{ $isAdmin ? 'Manajemen User' : 'Lihat User' }}</span>
        </a>

        <a class="sidebar-link {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="fas fa-cog"></i>
            <span>Pengaturan Profil</span>
        </a>
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="sidebar-logout">
        @csrf
        <button type="submit" class="sidebar-logout__button">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar</span>
        </button>
    </form>
</aside>


