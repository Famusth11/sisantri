<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JadwalDiniyah;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AbsensiSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Absensi::flushKitabNameMap();
        Carbon::setTestNow(Carbon::create(2026, 4, 20, 18, 45, 0, 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_store_sholat_attendance(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Presensi',
            'email' => 'admin.presensi@example.test',
        ]);

        Santri::create([
            'id_santri' => 'S1001',
            'nama' => 'Santri Sholat',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'Bilingual',
            'pembina' => 'Pembina A',
        ]);

        $response = $this->actingAs($admin)->postJson(route('absensi.storeSholat'), [
            'id_santri' => 'S1001',
            'jenis_sholat' => 'Subuh',
            'tanggal' => '2026-04-18',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Absensi Sholat berhasil dicatat untuk Santri Sholat',
            ]);

        $this->assertDatabaseHas('absensi', [
            'santri_id' => 'S1001',
            'kegiatan' => 'Sholat Subuh',
            'status' => 'HADIR',
            'petugas_id' => 'admin.presensi@example.test',
            'nama_santri' => 'Santri Sholat',
            'kelas' => '10',
            'golongan' => 'Bilingual',
        ]);

        $attendance = Absensi::query()->firstOrFail();

        $this->assertSame('2026-04-18', $attendance->timestamp->format('Y-m-d'));
    }

    public function test_pembina_cannot_store_sholat_for_santri_outside_visibility(): void
    {
        $pembina = User::factory()->create([
            'role' => 'Pembina',
            'nama_lengkap' => 'Pembina Putri 11',
            'kelas_kitab_hendel' => 'Kelas 11 Putri, Tajwid Kelas 11',
        ]);

        Santri::create([
            'id_santri' => 'S1002',
            'nama' => 'Santri Di Luar Akses',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'Bilingual',
            'pembina' => 'Pembina B',
        ]);

        $response = $this->actingAs($pembina)->postJson(route('absensi.storeSholat'), [
            'id_santri' => 'S1002',
            'jenis_sholat' => 'Maghrib',
            'tanggal' => '2026-04-18',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Akses ditolak untuk santri ini.',
            ]);

        $this->assertDatabaseCount('absensi', 0);
    }

    public function test_diniyah_submission_only_records_santri_that_match_the_active_schedule(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Diniyah',
            'email' => 'admin.diniyah@example.test',
        ]);

        $matchingSantri = Santri::create([
            'id_santri' => 'S2001',
            'nama' => 'Santri Sesuai Jadwal',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina C',
        ]);

        Santri::create([
            'id_santri' => 'S2002',
            'nama' => 'Santri Di Luar Jadwal',
            'jenis_kelamin' => 'Putra',
            'kelas' => '11',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina D',
        ]);

        $jadwal = JadwalDiniyah::create([
            'kitab_id' => 'KD100',
            'nama_kegiatan' => 'Nahwu',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pengampu' => 'Ustadz Nahwu',
            'keterangan_waktu' => 'Diniyah Sore',
            'jam_mulai' => '18:30:00',
            'jam_selesai' => '19:30:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from('/absensi/diniyah?jadwal_id=' . $jadwal->id)
            ->post(route('absensi.storeDiniyah'), [
                'jadwal_id' => $jadwal->id,
                'tanggal' => '2026-04-19',
                'absensi' => [
                    $matchingSantri->id_santri => 'Hadir',
                    'S2002' => 'Izin',
                ],
            ]);

        $response->assertRedirect('/absensi/diniyah?jadwal_id=' . $jadwal->id);
        $response->assertSessionHas('success', '1 absensi Diniyah berhasil dicatat untuk jadwal: Nahwu');

        $this->assertDatabaseHas('absensi', [
            'santri_id' => 'S2001',
            'kegiatan' => 'Ngaji Nahwu',
            'status' => 'HADIR',
            'petugas_id' => 'admin.diniyah@example.test',
            'nama_santri' => 'Santri Sesuai Jadwal',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
        ]);

        $this->assertDatabaseMissing('absensi', [
            'santri_id' => 'S2002',
            'kegiatan' => 'Ngaji Nahwu',
        ]);

        $this->assertSame(1, Absensi::query()->count());
    }
}
