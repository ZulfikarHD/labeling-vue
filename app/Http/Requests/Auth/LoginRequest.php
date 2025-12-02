<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Form Request untuk validasi login credentials
 * dengan NP uppercase conversion dan rate limiting
 *
 * Request ini menangani validasi input login yang mencakup
 * NP (Nomor Pegawai) dan password, serta menerapkan
 * rate limiting untuk mencegah brute force attacks
 */
class LoginRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk request ini
     * Guest users diizinkan untuk attempt login
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan validation rules untuk login request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'np' => ['required', 'string', 'max:5'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mendapatkan custom error messages untuk validation
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'np.required' => 'NP wajib diisi',
            'np.max' => 'NP maksimal 5 karakter',
            'password.required' => 'Password wajib diisi',
        ];
    }

    /**
     * Prepare data sebelum validation
     * Convert NP ke uppercase untuk konsistensi
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('np')) {
            $this->merge([
                'np' => strtoupper($this->input('np')),
            ]);
        }
    }

    /**
     * Attempt authentication dengan credentials yang diberikan
     * termasuk pengecekan status aktif user
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Attempt login dengan NP dan password
        if (! Auth::attempt($this->only('np', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'np' => __('NP atau password salah'),
            ]);
        }

        // Check apakah user aktif setelah authentication berhasil
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'np' => __('Akun tidak aktif. Hubungi administrator'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Memastikan request tidak di-rate limited
     * untuk mencegah brute force attacks
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'np' => __('Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Mendapatkan throttle key untuk rate limiting
     * berdasarkan NP dan IP address
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('np')).'|'.$this->ip());
    }
}
