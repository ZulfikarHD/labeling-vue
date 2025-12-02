<?php

use App\Http\Controllers\Admin\ChangePasswordController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkstationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// ==================== ADMIN ROUTES ====================

/**
 * Routes khusus untuk admin dengan middleware admin
 * mencakup user management, workstation management, dll
 */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    // User Management
    Route::resource('users', UserController::class);

    // Workstation Management
    Route::patch('/workstations/{workstation}/toggle-active', [WorkstationController::class, 'toggleActive'])
        ->name('workstations.toggle-active');
    Route::resource('workstations', WorkstationController::class);

    // Change Password (Admin)
    Route::get('/change-password', [ChangePasswordController::class, 'index'])->name('change-password.index');
    Route::post('/change-password', [ChangePasswordController::class, 'store'])->name('change-password.store');
});
