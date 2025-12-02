<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi pembuatan workstation baru
 * dengan validasi nama unique dan panjang maksimal
 */
class StoreWorkstationRequest extends FormRequest
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
     * Mendefinisikan rules validasi untuk pembuatan workstation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:workstations,name'],
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

    /**
     * Menyiapkan data sebelum validasi
     * dengan set default is_active ke true jika tidak ada
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->is_active ?? true,
        ]);
    }
}
