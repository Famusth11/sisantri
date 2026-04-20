<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PDOException;

class EnsureDatabaseConnection
{
    




    public function handle(Request $request, Closure $next): Response
    {
        
        
        $skipRoutes = [
            'login', 'register', 'password.request', 'password.reset',
            'password.email', 'password.store', 'verification.notice', 'verification.verify'
        ];
        
        $routeName = $request->route()?->getName();
        if (in_array($routeName, $skipRoutes)) {
            return $next($request);
        }

        
        $cacheKey = 'db_connection_check_' . md5($request->ip());
        $lastCheck = Cache::get($cacheKey);
        
        
        if (!$lastCheck || (time() - $lastCheck) > 5) {
            try {
                
                $pdo = DB::connection()->getPdo();
                
                
                Cache::put($cacheKey, time(), 5);
            } catch (PDOException $e) {
                
                if (str_contains($e->getMessage(), '2006') || 
                    str_contains($e->getMessage(), 'MySQL server has gone away') ||
                    str_contains($e->getMessage(), 'timeout')) {
                    Log::warning('Koneksi MySQL terputus atau timeout, mencoba reconnect', [
                        'error' => $e->getMessage(),
                        'route' => $routeName
                    ]);
                    
                    try {
                        
                        DB::reconnect();
                        Cache::put($cacheKey, time(), 5);
                        Log::info('Koneksi MySQL berhasil di-reconnect');
                    } catch (\Exception $reconnectException) {
                        Log::error('Gagal reconnect MySQL', [
                            'error_asli' => $e->getMessage(),
                            'error_reconnect' => $reconnectException->getMessage()
                        ]);
                    }
                } else {
                    
                    Log::warning('Database connection error (non-critical)', [
                        'error' => $e->getMessage(),
                        'route' => $routeName
                    ]);
                }
            } catch (\Exception $e) {
                
                Log::warning('Database check error', [
                    'error' => $e->getMessage(),
                    'route' => $routeName
                ]);
            }
        }

        return $next($request);
    }
}
