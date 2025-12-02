<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller untuk mengelola password user sendiri
 * yang memungkinkan user mengubah password mereka
 *
 * Perubahan password memerlukan verifikasi password lama
 * untuk keamanan
 */
class PasswordController extends Controller
{
    /**
     * Memperbarui password user yang sedang login
     * dengan verifikasi password lama terlebih dahulu
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->new_password,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
