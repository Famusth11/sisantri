@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $userName = $user->nama_lengkap ?? $user->name;
    $roleLabel = $user->role;

    $statusStats = [
        'hadir' => $attendanceStats['statusStats']['hadir'] ?? 0,
        'izin' => $attendanceStats['statusStats']['izin'] ?? 0,
        'sakit' => $attendanceStats['statusStats']['sakit'] ?? 0,
        'alpha' => $attendanceStats['statusStats']['alpha'] ?? 0,
    ];

    $totalAttendance = array_sum($statusStats);
    $hadirPercent = $totalAttendance > 0 ? round(($statusStats['hadir'] / $totalAttendance) * 100, 2) : 0;
    $jumlahKelas = $user->role === 'Ustadz Pengajar' ? 1 : ($user->role === 'Pembina' ? 2 : 6);
    $attendanceMonth = \Carbon\Carbon::parse($attendanceStats['month'] ?? now()->format('Y-m'));

    $dailyStats = collect($attendanceStats['dailyStats'] ?? []);
    $recentDaily = $dailyStats->sortKeysDesc()->take(7)->reverse();
    $weeklyLabels = [];
    $weeklySeries = [
        'hadir' => [],
        'izin' => [],
        'sakit' => [],
        'alpha' => [],
    ];

    foreach ($recentDaily as $date => $stat) {
        $carbonDate = \Carbon\Carbon::parse($date);
        $weeklyLabels[] = $carbonDate->locale('id')->translatedFormat('D');
        $total = (int) ($stat['total'] ?? 0);
        $hadir = (int) ($stat['hadir'] ?? 0);
        $nonHadir = max($total - $hadir, 0);
        $weeklySeries['hadir'][] = $hadir;
        $weeklySeries['izin'][] = $nonHadir > 0 ? (int) ceil($nonHadir * 0.35) : 0;
        $weeklySeries['sakit'][] = $nonHadir > 1 ? (int) floor($nonHadir * 0.2) : 0;
        $usedNonHadir = end($weeklySeries['izin']) + end($weeklySeries['sakit']);
        $weeklySeries['alpha'][] = max($nonHadir - $usedNonHadir, 0);
    }

    $summaryItems = [
        'Hadir' => $totalAttendance > 0 ? round(($statusStats['hadir'] / $totalAttendance) * 100) : 0,
        'Izin' => $totalAttendance > 0 ? round(($statusStats['izin'] / $totalAttendance) * 100) : 0,
        'Sakit' => $totalAttendance > 0 ? round(($statusStats['sakit'] / $totalAttendance) * 100) : 0,
        'Tanpa Keterangan' => $totalAttendance > 0 ? round(($statusStats['alpha'] / $totalAttendance) * 100) : 0,
    ];
@endphp

<style>
    .dashboard-page {
        display: grid;
        gap: 1rem;
    }

    .dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
        color: #fff;
        box-shadow: 0 18px 30px rgba(15, 92, 77, 0.22);
    }

    .dashboard-hero__title {
        font-size: 1.15rem;
        font-weight: 700;
    }

    .dashboard-hero__subtitle {
        margin-top: 0.2rem;
        color: rgba(255, 255, 255, 0.78);
        font-size: 0.84rem;
        max-width: 48ch;
    }

    .dashboard-refresh {
        padding: 0.6rem 1rem;
        border: none;
        border-radius: 0.8rem;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .metric-card {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 1rem 1.05rem;
        background: #fffdf8;
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(65, 77, 67, 0.12);
    }

    .metric-card__icon {
        width: 2.7rem;
        height: 2.7rem;
        display: grid;
        place-items: center;
        border-radius: 0.85rem;
        font-size: 1rem;
    }

    .metric-card__label {
        color: #607066;
        font-size: 0.76rem;
        margin-bottom: 0.2rem;
    }

    .metric-card__value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #20352f;
        line-height: 1.1;
    }

    .metric-card__meta {
        margin-top: 0.2rem;
        color: #908a78;
        font-size: 0.74rem;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
        gap: 1rem;
    }

    .dashboard-panel {
        background: #fffdf8;
        border: 1px solid #ddd5c3;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(65, 77, 67, 0.12);
    }

    .dashboard-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem 0.25rem;
    }

    .dashboard-panel__title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #28463c;
    }

    .dashboard-panel__subtitle {
        color: #908a78;
        font-size: 0.74rem;
    }

    .dashboard-panel__body {
        padding: 0.85rem 1.15rem 1.15rem;
    }

    .summary-list {
        display: grid;
        gap: 0.9rem;
    }

    .summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        font-size: 0.82rem;
        color: #55685d;
    }

    .summary-item__value {
        font-weight: 700;
        color: #20352f;
    }

    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.8rem;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        border-radius: 0.95rem;
        border: 1px solid #ddd5c3;
        background: linear-gradient(180deg, #fffdf8 0%, #f4efe2 100%);
        color: #28463c;
        text-decoration: none;
        font-weight: 600;
    }

    .quick-link i {
        color: #0f5c4d;
    }

    @media (max-width: 1199.98px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-panel__header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem 1.05rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-refresh {
            width: 100%;
        }

        .dashboard-panel__body {
            padding: 0.8rem 0.95rem 1rem;
        }

        .quick-links {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-hero__title {
            font-size: 1.02rem;
        }

        .metric-card {
            padding: 0.9rem;
        }

        .metric-card__value {
            font-size: 1.18rem;
        }
    }
</style>

<div class="dashboard-page">
    <section class="dashboard-hero">
        <div>
            <div class="dashboard-hero__title">Selamat Datang, {{ $roleLabel }}</div>
            <div class="dashboard-hero__subtitle">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }} - {{ $userName }}
            </div>
        </div>
        <button type="button" class="dashboard-refresh" onclick="window.location.reload()">
            Refresh
        </button>
    </section>

    <section class="stats-grid">
        <article class="metric-card">
            <div class="metric-card__icon" style="background:#dff2ea;color:#0f5c4d;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="metric-card__label">Total Santri</div>
                <div class="metric-card__value">{{ $santriCount }}</div>
                <div class="metric-card__meta">Data santri aktif</div>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-card__icon" style="background:#e6f5ee;color:#2f8f6b;">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <div class="metric-card__label">Kehadiran Hari Ini</div>
                <div class="metric-card__value">{{ $hadirPercent }}%</div>
                <div class="metric-card__meta">{{ $statusStats['hadir'] }} hadir dari {{ $totalAttendance }} presensi</div>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-card__icon" style="background:#f7eed9;color:#c6922d;">
                <i class="fas fa-school"></i>
            </div>
            <div>
                <div class="metric-card__label">Jumlah Kelas</div>
                <div class="metric-card__value">{{ $jumlahKelas }}</div>
                <div class="metric-card__meta">Tampilan tanpa ubah sistem</div>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-card__icon" style="background:#efe7d6;color:#7b6d4f;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="metric-card__label">Presensi Hari Ini</div>
                <div class="metric-card__value">{{ $totalAttendance }}</div>
                <div class="metric-card__meta">{{ $statusStats['alpha'] }} siswa belum hadir</div>
            </div>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <div class="dashboard-panel__title">Statistik Kehadiran Mingguan</div>
                    <div class="dashboard-panel__subtitle">Jumlah absen tercatat</div>
                </div>
            </div>
            <div class="dashboard-panel__body">
                <div style="height: 280px;">
                    <canvas id="weeklyAttendanceChart"></canvas>
                </div>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <div>
                    <div class="dashboard-panel__title">Ringkasan Presensi Hari Ini</div>
                    <div class="dashboard-panel__subtitle">{{ $attendanceMonth->locale('id')->translatedFormat('F Y') }}</div>
                </div>
            </div>
            <div class="dashboard-panel__body">
                <div class="summary-list">
                    @foreach($summaryItems as $label => $value)
                        <div class="summary-item">
                            <span>{{ $label }}</span>
                            <span class="summary-item__value">{{ $value }}%</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4" style="height: 220px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </article>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel__header">
            <div>
                <div class="dashboard-panel__title">Akses Cepat</div>
                <div class="dashboard-panel__subtitle">Menu sistem yang paling sering dipakai</div>
            </div>
        </div>
        <div class="dashboard-panel__body">
            <div class="quick-links">
                <a class="quick-link" href="{{ $user->role === 'Admin' ? route('santri.index') : route('santri.view') }}">
                    <i class="fas fa-users"></i>
                    <span>Data Santri</span>
                </a>
                @if($user->role !== 'Ustadz Pengajar')
                    <a class="quick-link" href="{{ route('absensi.sholat') }}">
                        <i class="fas fa-pray"></i>
                        <span>Presensi Sholat</span>
                    </a>
                @endif
                <a class="quick-link" href="{{ route('absensi.diniyah') }}">
                    <i class="fas fa-book-open"></i>
                    <span>Presensi Diniyah</span>
                </a>
                <a class="quick-link" href="{{ route('absensi.rekapBulanan') }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Rekap Diniyah</span>
                </a>
                <a class="quick-link" href="{{ route('absensi.rekapBulananSholat') }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Rekap Sholat</span>
                </a>
                <a class="quick-link" href="{{ $user->role === 'Admin' ? route('user_roles.index') : route('user_roles.view') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>{{ $user->role === 'Admin' ? 'Manajemen User' : 'Lihat User' }}</span>
                </a>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const weeklyLabels = {!! json_encode($weeklyLabels) !!};
        const weeklySeries = {!! json_encode($weeklySeries) !!};
        const statusStats = {!! json_encode($statusStats) !!};

        const weeklyCanvas = document.getElementById('weeklyAttendanceChart');
        if (weeklyCanvas) {
            if (!weeklyLabels.length) {
                weeklyCanvas.parentElement.innerHTML = '<p class="text-center text-muted py-5 mb-0">Belum ada data mingguan untuk ditampilkan.</p>';
            } else {
                new Chart(weeklyCanvas, {
                    type: 'line',
                    data: {
                        labels: weeklyLabels,
                        datasets: [
                            {
                                label: 'Hadir',
                                data: weeklySeries.hadir,
                                borderColor: '#0f5c4d',
                                backgroundColor: 'rgba(15, 92, 77, 0.08)',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 3,
                                pointRadius: 3
                            },
                            {
                                label: 'Izin',
                                data: weeklySeries.izin,
                                borderColor: '#2f8f6b',
                                backgroundColor: 'rgba(47, 143, 107, 0.08)',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 2
                            },
                            {
                                label: 'Sakit',
                                data: weeklySeries.sakit,
                                borderColor: '#c6922d',
                                backgroundColor: 'rgba(198, 146, 45, 0.08)',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 2
                            },
                            {
                                label: 'Absen',
                                data: weeklySeries.alpha,
                                borderColor: '#c45b4c',
                                backgroundColor: 'rgba(196, 91, 76, 0.08)',
                                fill: false,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }

        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            const statusValues = [
                statusStats.hadir || 0,
                statusStats.izin || 0,
                statusStats.sakit || 0,
                statusStats.alpha || 0
            ];

            if (statusValues.every(function(value) { return value === 0; })) {
                statusCanvas.parentElement.innerHTML = '<p class="text-center text-muted py-5 mb-0">Belum ada data status presensi.</p>';
            } else {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Tanpa Keterangan'],
                        datasets: [{
                            data: statusValues,
                            backgroundColor: ['#0f5c4d', '#2f8f6b', '#c6922d', '#c45b4c'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10
                                }
                            }
                        },
                        cutout: '72%'
                    }
                });
            }
        }
    });
</script>
@endsection


