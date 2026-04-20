<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;  

class UserSeeder extends Seeder
{
    


    public function run(): void
    {
        
        User::truncate();  

        
        User::create([
            'name' => 'Admin',
            'email' => 'admin@sisantri.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',  
                'nama_lengkap' => 'Admin',
            'kelas_kitab_hendel' => 'All',
        ]);

        
        $pembinaUsers = [
            [
                'name' => 'Ustd.Maemon.Alh',
                'email' => 'pembina.12.putra@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustd.Maemon.Alh',
                'kelas_kitab_hendel' => 'PUTRA 12',
            ],
            [
                'name' => 'Ustzh.Khotimah.Alhz',
                'email' => 'pembina.12.putri@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustzh.Khotimah.Alhz',
                'kelas_kitab_hendel' => 'PUTRI 12',
            ],
            [
                'name' => 'Ustd.Fahri',
                'email' => 'pembina.11.putra@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustd.Fahri',
                'kelas_kitab_hendel' => 'PUTRA 11',
            ],
            [
                'name' => 'Ustzh.Alfi.Alhz',
                'email' => 'pembina.11.putri@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustzh.Alfi.Alhz',
                'kelas_kitab_hendel' => 'PUTRI 11',
            ],
            [
                'name' => 'Ustd.Ali Mahzumi, Lc.',
                'email' => 'pembina.10.putra@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustd.Ali Mahzumi, Lc.',
                'kelas_kitab_hendel' => 'PUTRA 10',
            ],
            [
                'name' => 'Ustzh.Fegitafatma.S.Ag.Alhz',
                'email' => 'pembina.10.putri@sisantri.com',
                'password' => bcrypt('password'),
                'role' => 'Pembina',
                'nama_lengkap' => 'Ustzh.Fegitafatma.S.Ag.Alhz',
                'kelas_kitab_hendel' => 'PUTRI 10',
            ],
        ];

        foreach ($pembinaUsers as $pembina) {
            User::create($pembina);
        }

        
        User::create([
            'name' => 'Ustadz',
            'email' => 'ustadz@sisantri.com',
            'password' => bcrypt('password'),
            'role' => 'Ustadz Pengajar',
            'nama_lengkap' => 'Ustadz Test',
            'kelas_kitab_hendel' => 'Nahwu Kls 11',
        ]);
    }
}
