<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class Santri extends Model
{
    use HasFactory;

    protected const CACHE_VERSION = 'v2';

    protected $table = 'santri';
    
    protected $fillable = [
        'id_santri',
        'nama',
        'jenis_kelamin',
        'kelas',
        'golongan',
        'pembina',
    ];

    protected $primaryKey = 'id_santri';
    public $incrementing = false;
    protected $keyType = 'string';

    
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'santri_id', 'id_santri');
    }

    



    public static function listColumns(): array
    {
        return ['id_santri', 'nama', 'jenis_kelamin', 'kelas', 'golongan', 'pembina'];
    }

    public static function visibleListCacheKey($user, string $variant = 'full'): string
    {
        $userId = $user->id ?? 'guest';
        $role = $user->role ?? 'guest';

        return 'santri_list_' . $variant . '_' . $userId . '_' . $role . '_' . static::CACHE_VERSION;
    }

    public static function legacyVisibleListCacheKey($user): string
    {
        $userId = $user->id ?? 'guest';
        $role = $user->role ?? 'guest';

        return 'santri_list_' . $userId . '_' . $role;
    }

    public static function queryForUser($user = null): Builder
    {
        $query = static::query();
        static::applyVisibilityScope($query, $user);

        return $query;
    }

    public static function getAllForUser($user = null): Collection
    {
        return static::queryForUser($user)
            ->select(static::listColumns())
            ->orderBy('nama')
            ->get();
    }

    protected static function applyVisibilityScope(Builder $query, $user): void
    {
        if (!$user || $user->role === 'Admin') {
            return;
        }

        if ($user->role === 'Pembina') {
            $access = static::parsePembinaAccess($user);

            if (empty($access['jenis_kelamin']) || empty($access['kelas_list'])) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereRaw('UPPER(TRIM(jenis_kelamin)) = ?', [strtoupper(trim($access['jenis_kelamin']))])
                ->whereIn('kelas', $access['kelas_list']);

            return;
        }

        if ($user->role === 'Ustadz Pengajar') {
            $query->where('golongan', 'BILINGUAL');
            return;
        }

        $query->whereRaw('1 = 0');
    }

    protected static function parsePembinaAccess($user): array
    {
        $shouldLogDebug = app()->isLocal() && config('app.debug');
        $kelasKitabHendel = trim((string) ($user->kelas_kitab_hendel ?? ''));

        if ($kelasKitabHendel === '') {
            Log::warning('Pembina user has empty kelas_kitab_hendel', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            return [
                'jenis_kelamin' => null,
                'kelas_list' => [],
            ];
        }

        $kelasKitabHendelUpper = strtoupper($kelasKitabHendel);
        $userJenisKelamin = null;

        if (preg_match('/\b(PUTRA|PUTRI)\b/i', $kelasKitabHendelUpper, $matches)) {
            $userJenisKelamin = ucfirst(strtolower($matches[1]));
        }

        $userKelasList = [];

        if (preg_match_all('/\bKELAS\s+(\d+)\b/i', $kelasKitabHendelUpper, $kelasMatches)) {
            $userKelasList = array_unique($kelasMatches[1]);
        } elseif (preg_match_all('/\b(PUTRA|PUTRI)\s+(\d+)\b/i', $kelasKitabHendelUpper, $simpleMatches)) {
            $userKelasList = array_unique(array_slice($simpleMatches[2], 0));
        } elseif (preg_match_all('/\b(\d+)\b/', $kelasKitabHendelUpper, $numberMatches)) {
            $userKelasList = array_unique(array_filter($numberMatches[1], function ($num) {
                return in_array($num, ['10', '11', '12'], true);
            }));
        }

        if ($shouldLogDebug) {
            Log::debug('Pembina filtering santri', [
                'user_id' => $user->id,
                'kelas_kitab_hendel' => $kelasKitabHendel,
                'parsed_jenis_kelamin' => $userJenisKelamin,
                'parsed_kelas_list' => $userKelasList,
            ]);
        }

        if (empty($userJenisKelamin) || empty($userKelasList)) {
            Log::warning('Pembina filter invalid', [
                'user_id' => $user->id,
                'parsed_jenis_kelamin' => $userJenisKelamin,
                'parsed_kelas_list' => $userKelasList,
            ]);
        }

        return [
            'jenis_kelamin' => $userJenisKelamin,
            'kelas_list' => array_values($userKelasList),
        ];
    }

    


    public static function findById($id)
    {
        $id = trim($id);
        $cacheKey = 'santri_' . $id;
        
        return Cache::remember($cacheKey, 3600, function() use ($id) {
            return self::where('id_santri', $id)->first();
        });
    }

    


    public static function clearCache($user = null)
    {
        if (!$user) {
            return;
        }

        Cache::forget(static::legacyVisibleListCacheKey($user));
        Cache::forget(static::visibleListCacheKey($user, 'full'));
        Cache::forget(static::visibleListCacheKey($user, 'dashboard'));
    }
}
