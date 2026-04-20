<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserLinks extends Command
{
    protected $signature = 'test:user-links';
    protected $description = 'Test user management links';

    public function handle()
    {
        $this->info('Testing user management links...');
        
        try {
            $users = User::all();
            $this->info('Found ' . count($users) . ' users');
            
            foreach ($users as $user) {
                $this->info("\nUser: " . $user->name . " (ID: " . $user->id . ")");
                
                
                try {
                    $showRoute = route('user_roles.show', $user->id);
                    $editRoute = route('user_roles.edit', $user->id);
                    $updateRoute = route('user_roles.update', $user->id);
                    $destroyRoute = route('user_roles.destroy', $user->id);
                    
                    $this->info('  ✓ Show: ' . $showRoute);
                    $this->info('  ✓ Edit: ' . $editRoute);
                    $this->info('  ✓ Update: ' . $updateRoute);
                    $this->info('  ✓ Destroy: ' . $destroyRoute);
                    
                } catch (\Exception $e) {
                    $this->error('  ✗ Route error: ' . $e->getMessage());
                }
            }
            
            
            $this->info("\nTesting HTML generation...");
            $user = $users->first();
            if ($user) {
                $html = '
                <a href="' . route('user_roles.show', $user->id) . '" class="btn btn-info btn-sm">Lihat</a>
                <a href="' . route('user_roles.edit', $user->id) . '" class="btn btn-warning btn-sm">Edit</a>
                <form method="POST" action="' . route('user_roles.destroy', $user->id) . '" style="display: inline;" onsubmit="return confirm(\'Yakin hapus ' . $user->name . '?\')">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>';
                
                $this->info('Generated HTML:');
                $this->info($html);
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}