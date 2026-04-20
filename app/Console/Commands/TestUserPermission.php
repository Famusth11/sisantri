<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestUserPermission extends Command
{
    protected $signature = 'test:user-permission';
    protected $description = 'Test user permission for user management';

    public function handle()
    {
        $this->info('Testing user permissions...');
        
        try {
            
            $users = User::all();
            
            foreach ($users as $user) {
                $this->info("\nTesting user: " . $user->name . " (Role: " . $user->role . ")");
                
                Auth::login($user);
                
                
                $middleware = new RoleMiddleware();
                $request = Request::create('/user_roles', 'GET');
                
                try {
                    $response = $middleware->handle($request, function($req) {
                        return response('OK');
                    }, 'Admin');
                    
                    if ($response->getStatusCode() === 200) {
                        $this->info('  ✓ Access granted');
                    } else {
                        $this->info('  ✗ Access denied (Status: ' . $response->getStatusCode() . ')');
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), '403') !== false) {
                        $this->info('  ✗ Access denied (403)');
                    } else {
                        $this->error('  ✗ Error: ' . $e->getMessage());
                    }
                }
            }
            
            
            $this->info("\nTesting specific routes...");
            $admin = User::where('role', 'Admin')->first();
            if ($admin) {
                Auth::login($admin);
                
                $routes = [
                    'user_roles.index' => route('user_roles.index'),
                    'user_roles.create' => route('user_roles.create'),
                ];
                
                foreach ($routes as $name => $url) {
                    $this->info("  $name: $url");
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}