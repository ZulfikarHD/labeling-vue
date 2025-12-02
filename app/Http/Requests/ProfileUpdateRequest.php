<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi update profile user
 * dengan validasi nama opsional
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk request ini
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan rules validasi untuk update profile
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
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
            'name.max' => 'Nama maksimal :max karakter.',
        ];
    }
}
