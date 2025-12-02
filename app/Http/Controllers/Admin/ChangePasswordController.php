<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengganti password user oleh admin
 * yang memungkinkan admin mereset password user lain
 *
 * Fitur ini berguna saat user lupa password dan tidak bisa
 * melakukan reset sendiri
 */
class ChangePasswordController extends Controller
{
    /**
     * Menampilkan halaman change password
     * dengan daftar user yang bisa dipilih
     */
    public function index(): Response
    {
        $users = User::query()
            ->with('workstation')
            ->where('is_active', true)
            ->orderBy('np')
            ->get(['id', 'np', 'name', 'workstation_id']);

        return Inertia::render('Admin/ChangePassword', [
            'users' => $users,
        ]);
    }

    /**
     * Memproses perubahan password user
     * dengan opsi password default atau custom
     */
    public function store(ChangePasswordRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->user_id);

        // Tentukan password baru
        if ($request->boolean('use_default')) {
            $newPassword = 'Peruri'.$user->np;
        } else {
            $newPassword = $request->new_password;
        }

        $user->update([
            'password' => $newPassword,
        ]);

        return redirect()
            ->route('admin.change-password.index')
            ->with('success', "Password untuk user {$user->np} berhasil diubah.");
    }
}
