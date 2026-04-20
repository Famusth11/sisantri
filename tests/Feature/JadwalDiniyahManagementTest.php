<?php

namespace Tests\Feature;

use App\Models\JadwalDiniyah;
use App\Models\KitabDiniyah;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalDiniyahManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_schedule_form_uses_text_inputs_for_nama_and_pengampu(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        User::factory()->create([
            'role' => 'Pembina',
            'nama_lengkap' => 'Ust. Pembina Dropdown',
        ]);

        User::factory()->create([
            'role' => 'Ustadz Pengajar',
            'nama_lengkap' => 'Ust. Pengajar Dropdown',
        ]);

        KitabDiniyah::create([
            'id_kitab' => 'KD001',
            'kelas_kitab' => '11',
            'pengampu_golongan' => 'Diniyah Sore',
            'nama_kitab' => 'Nahwu',
        ]);

        JadwalDiniyah::create([
            'kitab_id' => 'KD001',
            'nama_kegiatan' => 'Shorof',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Genap',
            'kelas' => '11',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ust. Lama',
            'keterangan_waktu' => 'Diniyah Malam',
            'jam_mulai' => '18:00:00',
            'jam_selesai' => '19:00:00',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('jadwal_diniyah.index'));

        $response->assertOk();
        $response->assertSee('<input type="text"', false);
        $response->assertSee('name="nama_kegiatan"', false);
        $response->assertSee('Nahwu');
        $response->assertSee('name="pengampu"', false);
        $response->assertSee('<select name="keterangan_waktu"', false);
        $response->assertSee('Diniyah Sore');
        $response->assertSee('<select name="tahun_ajaran"', false);
        $response->assertSee(JadwalDiniyah::currentAcademicYear());
    }

    public function test_edit_schedule_form_keeps_dropdowns_for_nama_and_pengampu(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        User::factory()->create([
            'role' => 'Pembina',
            'nama_lengkap' => 'Ust. Pembina Dropdown',
        ]);

        KitabDiniyah::create([
            'id_kitab' => 'KD001',
            'kelas_kitab' => '11',
            'pengampu_golongan' => 'Diniyah Sore',
            'nama_kitab' => 'Nahwu',
        ]);

        $jadwal = JadwalDiniyah::create([
            'kitab_id' => 'KD001',
            'nama_kegiatan' => 'Nahwu',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'kelas' => '11',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ust. Pembina Dropdown',
            'keterangan_waktu' => 'Diniyah Sore',
            'jam_mulai' => '18:00:00',
            'jam_selesai' => '19:00:00',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('jadwal_diniyah.edit', $jadwal->id));

        $response->assertOk();
        $response->assertSee('<select name="nama_kegiatan"', false);
        $response->assertSee('<select name="pengampu"', false);
        $response->assertSee('Ust. Pembina Dropdown');
    }

    public function test_admin_can_create_and_activate_diniyah_schedule(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        KitabDiniyah::create([
            'id_kitab' => 'KD001',
            'kelas_kitab' => '10',
            'pengampu_golongan' => 'BILINGUAL',
            'nama_kitab' => 'Fathul Qorib',
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal_diniyah.store'), [
            'kitab_id' => 'KD001',
            'nama_kegiatan' => 'Fathul Qorib',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ustadz Ahmad',
            'keterangan_waktu' => 'Diniyah Sore',
            'jam_mulai' => '18:30',
            'jam_selesai' => '19:30',
        ]);

        $response->assertRedirect(route('jadwal_diniyah.index', [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ], false));

        $jadwal = JadwalDiniyah::query()->firstOrFail();

        $this->assertDatabaseHas('jadwal_diniyah', [
            'id' => $jadwal->id,
            'nama_kegiatan' => 'Fathul Qorib',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'pengampu' => 'Ustadz Ahmad',
            'keterangan_waktu' => 'Diniyah Sore',
            'is_active' => false,
        ]);

        $activateResponse = $this->actingAs($admin)->post(route('jadwal_diniyah.activate'), [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        $activateResponse->assertRedirect(route('jadwal_diniyah.index', [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ], false));

        $this->assertDatabaseHas('jadwal_diniyah', [
            'id' => $jadwal->id,
            'is_active' => true,
        ]);
    }

    public function test_active_schedule_is_shown_and_used_on_diniyah_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        Santri::create([
            'id_santri' => 'S0001',
            'nama' => 'Santri Jadwal',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina Uji',
        ]);

        $jadwal = JadwalDiniyah::create([
            'kitab_id' => 'KD001',
            'nama_kegiatan' => 'Fathul Qorib',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ustadz Ahmad',
            'keterangan_waktu' => 'Diniyah Sore',
            'jam_mulai' => '18:30:00',
            'jam_selesai' => '19:30:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/absensi/diniyah?jadwal_id=' . $jadwal->id);

        $response->assertOk();
        $response->assertSee('Jadwal aktif: 2026/2027 - Ganjil');
        $response->assertSee('Fathul Qorib');
        $response->assertSee('Santri Jadwal');
        $response->assertSee('Ustadz Ahmad');
        $response->assertSee('Diniyah Sore');
    }

    public function test_admin_can_copy_assignments_from_previous_period(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        JadwalDiniyah::create([
            'kitab_id' => 'KD010',
            'nama_kegiatan' => 'Alfiyah',
            'tahun_ajaran' => '2025/2026',
            'semester' => 'Genap',
            'kelas' => '11',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ustadz Sumber',
            'keterangan_waktu' => 'Diniyah Malam',
            'jam_mulai' => '19:00:00',
            'jam_selesai' => '20:00:00',
            'is_active' => true,
        ]);

        $target = JadwalDiniyah::create([
            'kitab_id' => 'KD010',
            'nama_kegiatan' => 'Alfiyah',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'kelas' => '11',
            'golongan' => 'BILINGUAL',
            'pengampu' => null,
            'keterangan_waktu' => null,
            'jam_mulai' => null,
            'jam_selesai' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('jadwal_diniyah.copy-assignments'), [
            'source_tahun_ajaran' => '2025/2026',
            'source_semester' => 'Genap',
            'target_tahun_ajaran' => '2026/2027',
            'target_semester' => 'Ganjil',
        ]);

        $response->assertRedirect(route('jadwal_diniyah.index', [
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
        ], false));

        $this->assertDatabaseHas('jadwal_diniyah', [
            'id' => $target->id,
            'pengampu' => 'Ustadz Sumber',
            'keterangan_waktu' => 'Diniyah Malam',
            'jam_mulai' => '19:00:00',
            'jam_selesai' => '20:00:00',
        ]);
    }

    public function test_jadwal_index_uses_pagination(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Jadwal',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            JadwalDiniyah::create([
                'kitab_id' => 'KD' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nama_kegiatan' => 'Jadwal ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'tahun_ajaran' => JadwalDiniyah::currentAcademicYear(),
                'semester' => JadwalDiniyah::currentSemester(),
                'kelas' => '10',
                'golongan' => 'BILINGUAL',
                'pengampu' => 'Pengampu ' . $i,
                'keterangan_waktu' => 'Diniyah Sore',
                'jam_mulai' => '18:00:00',
                'jam_selesai' => '19:00:00',
                'is_active' => false,
            ]);
        }

        $firstPage = $this->actingAs($admin)->get(route('jadwal_diniyah.index'));
        $firstPage->assertOk();
        $firstPage->assertSee('Jadwal 01');
        $firstPage->assertDontSee('Jadwal 11');

        $secondPage = $this->actingAs($admin)->get(route('jadwal_diniyah.index', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('Jadwal 11');
    }
}
