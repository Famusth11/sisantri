<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $allowedRoles = explode(',', $roles);  
        $allowedRoles = array_map('trim', $allowedRoles);  

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Akses ditolak. Role Anda (' . $user->role . ') tidak diizinkan (' . implode(', ', $allowedRoles) . ').');
        }

        return $next($request);
    }
}
