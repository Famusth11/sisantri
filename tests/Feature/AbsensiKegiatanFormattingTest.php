<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\KitabDiniyah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiKegiatanFormattingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Absensi::flushKitabNameMap();
    }

    public function test_ngaji_kegiatan_code_is_formatted_to_kitab_name(): void
    {
        KitabDiniyah::query()->create([
            'id_kitab' => 'KIT002',
            'kelas_kitab' => 'Kelas 10',
            'pengampu_golongan' => 'Putra',
            'nama_kitab' => 'Tafsir Jalalain',
        ]);

        $this->assertSame('Tafsir Jalalain', Absensi::resolveKitabName('KIT002'));
        $this->assertSame('Ngaji Tafsir Jalalain', Absensi::formatKegiatanLabel('Ngaji KIT002'));
    }

    public function test_non_ngaji_kegiatan_stays_unchanged(): void
    {
        $this->assertSame('Sholat Subuh', Absensi::formatKegiatanLabel('Sholat Subuh'));
    }
}
