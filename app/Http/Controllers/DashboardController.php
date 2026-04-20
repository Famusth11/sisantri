<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $attendanceStats = [];
        $santriCount = 0;

        try {
            $month = Carbon::now()->format('Y-m');
            $santriCacheKey = Santri::visibleListCacheKey($user, 'dashboard');
            $allSantri = Cache::remember($santriCacheKey, 1800, function () use ($user) {
                return Santri::queryForUser($user)
                    ->select('id_santri', 'nama')
                    ->orderBy('nama')
                    ->get();
            });
            $santriCount = $allSantri->count();

            $cacheKey = 'dashboard_stats_' . $user->id . '_' . $month;
            $attendanceStats = Cache::remember($cacheKey, 1800, function () use ($allSantri, $month) {
                $visibleSantriIds = $allSantri->pluck('id_santri')->all();

                if (empty($visibleSantriIds)) {
                    return [
                        'dailyStats' => [],
                        'statusStats' => ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0],
                        'topSantri' => [],
                        'month' => $month,
                    ];
                }

                $santriLookup = $allSantri->keyBy('id_santri');
                $baseQuery = Absensi::query()
                    ->forMonth($month)
                    ->whereIn('santri_id', $visibleSantriIds);

                $dailyStats = (clone $baseQuery)
                    ->selectRaw("DATE(timestamp) as attendance_date, SUM(CASE WHEN status = 'HADIR' THEN 1 ELSE 0 END) as hadir, COUNT(*) as total")
                    ->groupBy('attendance_date')
                    ->orderBy('attendance_date')
                    ->get()
                    ->mapWithKeys(function ($row) {
                        return [
                            $row->attendance_date => [
                                'hadir' => (int) $row->hadir,
                                'total' => (int) $row->total,
                            ],
                        ];
                    })
                    ->all();

                $statusCounts = (clone $baseQuery)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $statusStats = [
                    'hadir' => (int) ($statusCounts['HADIR'] ?? 0),
                    'izin' => (int) ($statusCounts['IZIN'] ?? 0),
                    'sakit' => (int) ($statusCounts['SAKIT'] ?? 0),
                    'alpha' => (int) (($statusCounts['ALPHA'] ?? 0) + ($statusCounts['TIDAK HADIR'] ?? 0)),
                ];

                $topSantri = (clone $baseQuery)
                    ->selectRaw("santri_id, SUM(CASE WHEN status = 'HADIR' THEN 1 ELSE 0 END) as hadir, COUNT(*) as total")
                    ->groupBy('santri_id')
                    ->get()
                    ->map(function ($row) use ($santriLookup) {
                        $santri = $santriLookup->get($row->santri_id);

                        return [
                            'hadir' => (int) $row->hadir,
                            'total' => (int) $row->total,
                            'nama' => $santri->nama ?? $row->santri_id,
                        ];
                    })
                    ->sort(function ($a, $b) {
                        $percentageA = $a['total'] > 0 ? ($a['hadir'] / $a['total']) * 100 : 0;
                        $percentageB = $b['total'] > 0 ? ($b['hadir'] / $b['total']) * 100 : 0;

                        if ($percentageA === $percentageB) {
                            return $b['hadir'] <=> $a['hadir'];
                        }

                        return $percentageB <=> $percentageA;
                    })
                    ->take(10)
                    ->values()
                    ->all();

                return [
                    'dailyStats' => $dailyStats,
                    'statusStats' => $statusStats,
                    'topSantri' => $topSantri,
                    'month' => $month,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error: ' . $e->getMessage());
            $attendanceStats = [
                'dailyStats' => [],
                'statusStats' => ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0],
                'topSantri' => [],
                'month' => Carbon::now()->format('Y-m'),
            ];
        }

        return view('dashboard', compact('attendanceStats', 'santriCount'));
    }
}
