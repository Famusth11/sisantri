<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserShow extends Command
{
    protected $signature = 'test:user-show {id}';
    protected $description = 'Test user show functionality';

    public function handle()
    {
        $id = $this->argument('id');
        $this->info("Testing user show for ID: $id");
        
        try {
            $user = User::find($id);
            
            if (!$user) {
                $this->error("User with ID $id not found");
                return;
            }
            
            $this->info("User found:");
            $this->info("  ID: " . $user->id);
            $this->info("  Name: " . $user->name);
            $this->info("  Email: " . $user->email);
            $this->info("  Role: " . $user->role);
            $this->info("  Nama Lengkap: " . $user->nama_lengkap);
            $this->info("  Kelas Kitab: " . ($user->kelas_kitab_hendel ?? 'null'));
            $this->info("  Created At: " . ($user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'null'));
            $this->info("  Updated At: " . ($user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : 'null'));
            
            
            $this->info("\nTesting route generation:");
            $this->info("  Show route: " . route('user_roles.show', $user->id));
            $this->info("  Edit route: " . route('user_roles.edit', $user->id));
            $this->info("  Update route: " . route('user_roles.update', $user->id));
            $this->info("  Destroy route: " . route('user_roles.destroy', $user->id));
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}