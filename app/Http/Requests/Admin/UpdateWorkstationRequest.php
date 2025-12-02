<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk validasi update workstation
 * dengan validasi nama unique kecuali untuk workstation yang sedang diedit
 */
class UpdateWorkstationRequest extends FormRequest
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
     * Mendefinisikan rules validasi untuk update workstation
     * dengan ignore unique check untuk workstation yang sedang diedit
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('workstations', 'name')->ignore($this->route('workstation')),
            ],
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
            'name.required' => 'Nama workstation wajib diisi.',
            'name.max' => 'Nama workstation maksimal :max karakter.',
            'name.unique' => 'Nama workstation sudah digunakan.',
        ];
    }
}
