<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class VerifyPembinaUsers extends Command
{
    




    protected $signature = 'verify:pembina-users';

    




    protected $description = 'Verify that all pembina users have been created successfully';

    


    public function handle()
    {
        $this->info('=== VERIFIKASI USER PEMBINA ===');
        $this->newLine();

        
        $totalUsers = User::count();
        $this->info("Total users: " . $totalUsers);
        $this->newLine();

        
        $pembinaUsers = User::where('role', 'Pembina')->get(['name', 'email', 'kelas_kitab_hendel']);

        $this->info('User Pembina yang dibuat:');
        $this->line(str_repeat('-', 80));

        $headers = ['Name', 'Email', 'Kelas Kitab Hendel'];
        $rows = [];

        foreach ($pembinaUsers as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                $user->kelas_kitab_hendel
            ];
        }

        $this->table($headers, $rows);
        $this->line(str_repeat('-', 80));
        $this->info('Total Pembina: ' . $pembinaUsers->count());
        $this->newLine();

        
        $expectedUsers = [
            'pembina.12.putra@sisantri.com',
            'pembina.12.putri@sisantri.com',
            'pembina.11.putra@sisantri.com',
            'pembina.11.putri@sisantri.com',
            'pembina.10.putra@sisantri.com',
            'pembina.10.putri@sisantri.com',
        ];

        $this->info('Verifikasi user yang diharapkan:');
        $allFound = true;

        foreach ($expectedUsers as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->line("✅ " . $email . " - " . $user->name . " (" . $user->kelas_kitab_hendel . ")");
            } else {
                $this->error("❌ " . $email . " - NOT FOUND");
                $allFound = false;
            }
        }

        $this->newLine();
        if ($allFound) {
            $this->info('=== SEMUA USER PEMBINA BERHASIL DIBUAT ===');
        } else {
            $this->error('=== ADA USER PEMBINA YANG BELUM DIBUAT ===');
        }

        return $allFound ? 0 : 1;
    }
}