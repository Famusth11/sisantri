<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SingleAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_admin_cannot_be_created_from_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('user_roles.store'), [
            'name' => 'admin-baru',
            'email' => 'admin2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Admin',
            'nama_lengkap' => 'Admin Kedua',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(1, User::query()->where('role', 'Admin')->count());
    }

    public function test_existing_user_cannot_be_promoted_to_admin_when_primary_admin_exists(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $user = User::factory()->create([
            'role' => 'Pembina',
            'email' => 'pembina@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('user_roles.update', $user->id), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'Admin',
            'nama_lengkap' => $user->nama_lengkap,
            'kelas_kitab_hendel' => $user->kelas_kitab_hendel,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'Pembina',
        ]);
    }

    public function test_primary_admin_cannot_be_deleted_from_management_or_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $deleteFromManagement = $this->actingAs($admin)->delete(route('user_roles.destroy', $admin->id));
        $deleteFromManagement->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'Admin',
        ]);

        $deleteFromProfile = $this->actingAs($admin)->delete(route('profile.destroy'), [
            'password' => 'password123',
        ]);

        $deleteFromProfile->assertSessionHasErrors('password', null, 'userDeletion');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'Admin',
        ]);
    }

    public function test_user_import_rejects_admin_rows(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $csvContent = implode("\n", [
            'name,email,role,nama_lengkap,kelas_kitab_hendel,password',
            'admin-baru,admin-baru@example.com,Admin,Admin Baru,,password123',
        ]);

        $file = UploadedFile::fake()->createWithContent('user-import.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('user_roles.import'), [
            'import_file' => $file,
        ]);

        $response->assertSessionHasErrors('import_file', null, 'userImport');
        $this->assertSame(1, User::query()->where('role', 'Admin')->count());
    }
}
