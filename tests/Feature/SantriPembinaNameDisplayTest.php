<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriPembinaNameDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_santri_form_shows_pembina_full_name(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
        ]);

        User::factory()->create([
            'role' => 'Pembina',
            'name' => 'pembina.username',
            'nama_lengkap' => 'Ust. Ahmad Robihan, M.Pd.I',
        ]);

        $response = $this->actingAs($admin)->get(route('santri.create'));

        $response->assertOk();
        $response->assertSee('Ust. Ahmad Robihan, M.Pd.I');
    }
}
