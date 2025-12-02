<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk validasi update user
 * dengan validasi role valid dan password optional
 */
class UpdateUserRequest extends FormRequest
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
     * Mendefinisikan rules validasi untuk update user
     * NP tidak dapat diubah setelah dibuat
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', new Enum(UserRole::class)],
            'workstation_id' => ['required', 'exists:workstations,id'],
            'is_active' => ['boolean'],
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
            'password.min' => 'Password minimal :min karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.enum' => 'Role tidak valid.',
            'workstation_id.required' => 'Workstation wajib dipilih.',
            'workstation_id.exists' => 'Workstation tidak ditemukan.',
        ];
    }
}
