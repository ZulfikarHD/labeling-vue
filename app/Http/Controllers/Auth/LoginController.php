<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk menangani autentikasi user
 * yang mencakup login dan logout functionality
 *
 * Login menggunakan NP (Nomor Pegawai) sebagai identifier
 * dengan automatic uppercase conversion untuk konsistensi
 */
class LoginController extends Controller
{
    /**
     * Menampilkan halaman login
     * dengan form NP dan password
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Memproses login request dengan validasi
     * dan pengecekan status aktif user
     *
     * Flow authentication:
     * 1. Validate input (NP di-convert ke uppercase)
     * 2. Attempt authentication
     * 3. Check is_active status
     * 4. Regenerate session untuk security
     * 5. Redirect ke intended URL atau dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Memproses logout dengan clear session
     * dan redirect ke halaman login
     *
     * Security measures:
     * 1. Logout dari web guard
     * 2. Invalidate session
     * 3. Regenerate CSRF token
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
