<?php

namespace Tests\Feature;

use App\Models\KitabDiniyah;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CorePagesAccessibleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_admin_can_access_core_operational_pages(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Sistem',
        ]);

        Santri::create([
            'id_santri' => 'S20260420001',
            'nama' => 'Santri Uji',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina Uji',
        ]);

        KitabDiniyah::create([
            'id_kitab' => 'K20260420001',
            'kelas_kitab' => '10',
            'pengampu_golongan' => 'BILINGUAL',
            'nama_kitab' => 'Kitab Uji',
        ]);

        $this->actingAs($admin);

        foreach ([
            '/dashboard',
            '/santri',
            '/user_roles',
            '/jadwal-diniyah',
            '/absensi/sholat',
            '/absensi/diniyah',
            '/absensi/rekap-bulanan',
            '/absensi/rekap-bulanan-sholat',
            '/profile',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}
