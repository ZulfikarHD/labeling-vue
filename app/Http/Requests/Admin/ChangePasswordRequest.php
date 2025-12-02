<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi change password oleh admin
 * dengan validasi user exists dan password requirements
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk request ini
     * Admin sudah divalidasi via middleware
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan rules validasi untuk change password
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'new_password' => ['required_without:use_default', 'nullable', 'string', 'min:6', 'confirmed'],
            'new_password_confirmation' => ['required_without:use_default', 'nullable', 'string'],
            'use_default' => ['boolean'],
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
            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User tidak ditemukan.',
            'new_password.required_without' => 'Password baru wajib diisi jika tidak menggunakan password default.',
            'new_password.min' => 'Password minimal :min karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'new_password_confirmation.required_without' => 'Konfirmasi password wajib diisi.',
        ];
    }

    /**
     * Menyiapkan data sebelum validasi
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'use_default' => $this->use_default ?? false,
        ]);
    }
}
