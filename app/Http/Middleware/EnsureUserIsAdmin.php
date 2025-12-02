<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user memiliki role Admin
 * sebelum mengakses route tertentu
 *
 * Middleware ini digunakan untuk protect admin-only routes
 * seperti user management, workstation management, dan konfigurasi sistem
 */
class EnsureUserIsAdmin
{
    /**
     * Handle incoming request dengan pengecekan role admin
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check apakah user authenticated dan memiliki role admin
        if (! $request->user() || ! $request->user()->isAdmin()) {
            // Return 403 Forbidden untuk unauthorized access
            abort(403, 'Akses tidak diizinkan. Anda harus memiliki role Administrator.');
        }

        return $next($request);
    }
}
