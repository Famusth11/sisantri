<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserBulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_users_from_csv_file(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->create([
            'email' => 'lama@example.com',
            'name' => 'lama',
            'nama_lengkap' => 'User Lama',
            'role' => 'Pembina',
            'password' => Hash::make('password-lama'),
        ]);

        $csvContent = implode("\n", [
            'name,email,role,nama_lengkap,kelas_kitab_hendel,password',
            'lama-baru,lama@example.com,Pembina,User Lama Diperbarui,PUTRA KELAS 11,passwordbarux',
            'ustadz-baru,ustadz@example.com,Ustadz Pengajar,Ustadz Baru,BILINGUAL,',
        ]);

        $file = UploadedFile::fake()->createWithContent('user-import.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('user_roles.import'), [
            'import_file' => $file,
        ]);

        $response->assertRedirect(route('user_roles.index', [], false));

        $updatedUser = User::query()->where('email', 'lama@example.com')->firstOrFail();
        $newUser = User::query()->where('email', 'ustadz@example.com')->firstOrFail();

        $this->assertSame('lama-baru', $updatedUser->name);
        $this->assertSame('Pembina', $updatedUser->role);
        $this->assertSame('User Lama Diperbarui', $updatedUser->nama_lengkap);
        $this->assertSame('PUTRA KELAS 11', $updatedUser->kelas_kitab_hendel);
        $this->assertTrue(Hash::check('passwordbarux', $updatedUser->password));

        $this->assertSame('ustadz-baru', $newUser->name);
        $this->assertSame('Ustadz Pengajar', $newUser->role);
        $this->assertSame('BILINGUAL', $newUser->kelas_kitab_hendel);
        $this->assertTrue(Hash::check('password123', $newUser->password));
    }
}
