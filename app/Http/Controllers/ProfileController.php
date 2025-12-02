<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola profile user sendiri
 * yang mencakup view dan update profile info
 *
 * Setiap user dapat melihat dan mengubah informasi profile mereka
 * kecuali NP yang bersifat readonly setelah dibuat
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profile settings
     * dengan informasi user yang sedang login
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->load('workstation'),
        ]);
    }

    /**
     * Memperbarui informasi profile user
     * NP tidak dapat diubah (readonly)
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Menghapus akun user sendiri
     * dengan validasi password terlebih dahulu
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.current_password' => 'Password tidak sesuai.',
        ]);

        $user = $request->user();

        auth()->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Akun Anda berhasil dihapus.');
    }
}
