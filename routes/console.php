<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:reset-password {email} {password}', function (string $email, string $password) {
    $normalizedEmail = strtolower(trim($email));
    $user = User::where('email', $normalizedEmail)->first();

    if (! $user) {
        $this->error("User not found: {$normalizedEmail}");
        return 1;
    }

    $user->password = Hash::make($password);
    $user->save();

    $this->info("Password updated for {$normalizedEmail}");
    return 0;
})->purpose('Reset a user\'s password by email');

Artisan::command('user:ensure {email} {name?} {--password=}', function (string $email, ?string $name) {
    $normalizedEmail = strtolower(trim($email));
    $password = $this->option('password') ?: 'PasswordBaru123!';

    $user = User::firstOrCreate(
        ['email' => $normalizedEmail],
        [
            'name' => $name ?: 'User',
            'nama_lengkap' => $name ?: 'User',
            'role' => 'Pembina',
            'kelas_kitab_hendel' => 'All',
            'password' => Hash::make($password),
        ]
    );

    if ($user->wasRecentlyCreated) {
        $this->info("User created: {$normalizedEmail} with temporary password: {$password}");
    } else {
        $this->info("User already exists: {$normalizedEmail}");
    }
})->purpose('Ensure a user exists; create with a temporary password if missing');
