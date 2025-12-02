<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Form Request untuk validasi update password user sendiri
 * dengan validasi current password dan password baru
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk request ini
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan rules validasi untuk update password
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', Password::min(6), 'confirmed'],
        ];
    }

    /**
     * Mendefinisikan custom error messages dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal :min karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
