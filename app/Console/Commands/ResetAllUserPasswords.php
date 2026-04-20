<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAllUserPasswords extends Command
{
    




    protected $signature = 'users:reset-passwords {password} {--force : Force reset without confirmation}';

    




    protected $description = 'Reset password semua user menjadi password yang sama';

    


    public function handle()
    {
        $password = $this->argument('password');
        $force = $this->option('force');

        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter!');
            return 1;
        }

        $userCount = User::count();

        if (!$force) {
            if (!$this->confirm("Apakah Anda yakin ingin mengubah password semua {$userCount} user menjadi password yang sama?")) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $hashedPassword = Hash::make($password);
        $updated = User::query()->update(['password' => $hashedPassword]);

        $this->info("✅ Password berhasil diubah untuk {$updated} user!");
        $this->warn("⚠️  Password baru: {$password}");
        $this->warn("⚠️  Pastikan untuk menginformasikan password baru kepada semua user!");

        return 0;
    }
}
