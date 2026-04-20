<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tangani error "MySQL server has gone away" dengan reconnect
        $exceptions->render(function (\PDOException $e, $request) {
            if (str_contains($e->getMessage(), '2006') || 
                str_contains($e->getMessage(), 'MySQL server has gone away')) {
                try {
                    \Illuminate\Support\Facades\DB::reconnect();
                    \Illuminate\Support\Facades\Log::warning('Koneksi MySQL terputus, sudah di-reconnect', [
                        'error' => $e->getMessage()
                    ]);
                    // Reconnect berhasil, tapi kita tidak bisa retry operasi asli di sini
                    // Koneksi akan tersedia untuk request berikutnya
                } catch (\Exception $reconnectException) {
                    \Illuminate\Support\Facades\Log::error('Gagal reconnect MySQL', [
                        'error_asli' => $e->getMessage(),
                        'error_reconnect' => $reconnectException->getMessage()
                    ]);
                }
            }
            // Return null untuk membiarkan Laravel menangani exception secara normal
            return null;
        });
    })->create();
