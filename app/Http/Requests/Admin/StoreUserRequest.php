<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request untuk validasi pembuatan user baru
 * dengan validasi NP unique, role valid, dan password requirements
 */
class StoreUserRequest extends FormRequest
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
     * Mendefinisikan rules validasi untuk pembuatan user
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'np' => [
                'required',
                'string',
                'max:5',
                Rule::unique('users', 'np'),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required_without:use_default',
                'nullable',
                'string',
                'min:6',
            ],
            'use_default' => ['boolean'],
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
            'np.required' => 'NP wajib diisi.',
            'np.max' => 'NP maksimal :max karakter.',
            'np.unique' => 'NP sudah terdaftar.',
            'password.required_without' => 'Password wajib diisi jika tidak menggunakan password default.',
            'password.min' => 'Password minimal :min karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.enum' => 'Role tidak valid.',
            'workstation_id.required' => 'Workstation wajib dipilih.',
            'workstation_id.exists' => 'Workstation tidak ditemukan.',
        ];
    }

    /**
     * Menyiapkan data sebelum validasi
     * dengan set default is_active ke true dan convert NP ke uppercase
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'np' => strtoupper($this->np ?? ''),
            'is_active' => $this->is_active ?? true,
            'use_default' => $this->use_default ?? false,
        ]);
    }
}
