<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
       'name', 'email', 'password', 'email_verified_at', 'role', 'nama_lengkap', 'kelas_kitab_hendel', 'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    protected $hidden = ['password', 'remember_token'];

    
    public function scopeFilterSantri($query, $sheetsService, $user)
    {
        $santriQuery = Santri::query();

        if ($user->role === 'Admin') {
            return $santriQuery->get();
        }

        if ($user->role === 'Pembina') {
            return Santri::getAllForUser($user);
        }

        if ($user->role === 'Ustadz Pengajar') {
            $kelasKitab = trim((string) ($user->kelas_kitab_hendel ?? ''));

            if ($kelasKitab === '') {
                return collect();
            }

            return $santriQuery
                ->where(function ($builder) use ($kelasKitab) {
                    $builder->where('kelas', $kelasKitab)
                        ->orWhere('golongan', strtoupper($kelasKitab))
                        ->orWhere('golongan', ucfirst(strtolower($kelasKitab)));
                })
                ->get();
        }

        return collect();
    }
}
