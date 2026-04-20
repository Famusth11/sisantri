<?php

namespace Tests\Feature;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SantriBulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_santri_from_csv_file(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
        ]);

        Santri::create([
            'id_santri' => 'S0001',
            'nama' => 'Data Lama',
            'jenis_kelamin' => 'Putra',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina Lama',
        ]);

        $csvContent = implode("\n", [
            'id_santri,nama,jenis_kelamin,kelas,golongan,pembina',
            'S0001,Data Diperbarui,Putra,11,TAHFIDZ,Pembina Baru',
            ',Santri Baru,Putri,10,BILINGUAL,Pembina Putri',
        ]);

        $file = UploadedFile::fake()->createWithContent('santri-import.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('santri.import'), [
            'import_file' => $file,
        ]);

        $response->assertRedirect(route('santri.index', [], false));

        $this->assertDatabaseHas('santri', [
            'id_santri' => 'S0001',
            'nama' => 'Data Diperbarui',
            'kelas' => '11',
            'golongan' => 'TAHFIDZ',
            'pembina' => 'Pembina Baru',
        ]);

        $this->assertDatabaseHas('santri', [
            'nama' => 'Santri Baru',
            'jenis_kelamin' => 'Putri',
            'kelas' => '10',
            'golongan' => 'BILINGUAL',
            'pembina' => 'Pembina Putri',
        ]);

        $this->assertSame(2, Santri::query()->count());
    }
}
