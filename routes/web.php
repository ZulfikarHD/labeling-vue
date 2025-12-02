<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes untuk aplikasi Label Generator yang mencakup
| authentication, dashboard, dan feature routes lainnya
|
*/

// ==================== GUEST ROUTES ====================

/**
 * Routes untuk user yang belum authenticated
 * mencakup login page dan proses authentication
 */
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// ==================== AUTHENTICATED ROUTES ====================

/**
 * Routes untuk user yang sudah authenticated
 * mencakup dashboard dan feature routes
 */
Route::middleware('auth')->group(function (): void {
    // Dashboard / Home
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

// ==================== ADMIN ROUTES ====================

/**
 * Routes khusus untuk admin dengan middleware admin
 * mencakup user management, workstation management, dll
 */
Route::middleware(['auth', 'admin'])->group(function (): void {
    // Placeholder untuk admin routes
    // User management, workstation management akan ditambahkan di sprint berikutnya
});
