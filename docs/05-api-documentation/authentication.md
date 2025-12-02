# Authentication - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk sistem autentikasi Label Generator yang bertujuan untuk menjelaskan flow authentication, authorization, dan security measures, yaitu: login dengan NP, logout, session management, dan role-based access control yang terintegrasi dengan Laravel authentication system.

## Overview

Sistem autentikasi menggunakan NP (Nomor Pegawai) sebagai identifier unik dengan password untuk verifikasi, yang berbeda dari email-based authentication pada umumnya. Hal ini disesuaikan dengan kebutuhan internal perusahaan dimana setiap karyawan memiliki NP unik.

## Authentication Flow

### Login Flow

Flow autentikasi dilakukan melalui tahapan sebagai berikut:

1. **User Input** - User memasukkan NP dan password pada form login
2. **NP Normalization** - NP di-convert ke UPPERCASE untuk konsistensi
3. **Credential Validation** - Sistem memvalidasi credentials via `Auth::attempt()`
4. **Active Status Check** - Sistem memverifikasi apakah user aktif (`is_active = true`)
5. **Session Regeneration** - Session di-regenerate untuk security
6. **Redirect** - User di-redirect ke intended URL atau dashboard

### Logout Flow

Flow logout dilakukan melalui tahapan sebagai berikut:

1. **Request Validation** - Verifikasi user authenticated
2. **Guard Logout** - `Auth::guard('web')->logout()`
3. **Session Invalidation** - Session di-invalidate sepenuhnya
4. **Token Regeneration** - CSRF token di-regenerate
5. **Redirect** - User di-redirect ke halaman login

## Routes

### Guest Routes

Routes yang hanya dapat diakses oleh user yang belum authenticated:

| Method | URI | Controller | Action | Name |
|--------|-----|------------|--------|------|
| GET | `/login` | LoginController | create | login |
| POST | `/login` | LoginController | store | - |

### Authenticated Routes

Routes yang memerlukan authentication:

| Method | URI | Controller | Action | Name |
|--------|-----|------------|--------|------|
| POST | `/logout` | LoginController | destroy | logout |
| GET | `/` | - | home | home |

### Admin Routes

Routes khusus untuk admin dengan middleware `admin`:

| Method | URI | Controller | Action | Description |
|--------|-----|------------|--------|-------------|
| - | `/users/*` | UserController | - | User management (Sprint selanjutnya) |
| - | `/workstations/*` | WorkstationController | - | Workstation management (Sprint selanjutnya) |

## Authentication Components

### LoginController

Controller untuk menangani autentikasi user yang mencakup tampilan login page dan proses authentication:

```php
// app/Http/Controllers/Auth/LoginController.php

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function create(): Response
    
    // Memproses login request
    public function store(LoginRequest $request): RedirectResponse
    
    // Memproses logout request
    public function destroy(Request $request): RedirectResponse
}
```

### LoginRequest

Form Request untuk validasi login credentials dengan NP uppercase conversion:

```php
// app/Http/Requests/Auth/LoginRequest.php

class LoginRequest extends FormRequest
{
    // Validation rules
    public function rules(): array
    {
        return [
            'np' => ['required', 'string', 'max:5'],
            'password' => ['required', 'string'],
        ];
    }
    
    // NP uppercase conversion
    protected function prepareForValidation(): void
    
    // Authentication attempt dengan is_active check
    public function authenticate(): void
    
    // Rate limiting untuk prevent brute force
    public function ensureIsNotRateLimited(): void
}
```

### EnsureUserIsAdmin Middleware

Middleware untuk memastikan user memiliki role Admin:

```php
// app/Http/Middleware/EnsureUserIsAdmin.php

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Akses tidak diizinkan. Anda harus memiliki role Administrator.');
        }

        return $next($request);
    }
}
```

## Role-Based Access Control

### User Roles

Sistem menggunakan dua role utama yang didefinisikan dalam enum:

| Role | Value | Description | Access Level |
|------|-------|-------------|--------------|
| Admin | `admin` | Administrator sistem | Full access |
| Operator | `operator` | Operator produksi | Limited access |

### Middleware Configuration

Middleware dikonfigurasi di `bootstrap/app.php`:

```php
$middleware->alias([
    'admin' => EnsureUserIsAdmin::class,
]);
```

### Usage dalam Routes

```php
// Route untuk semua authenticated users
Route::middleware('auth')->group(function () {
    // ...
});

// Route khusus admin
Route::middleware(['auth', 'admin'])->group(function () {
    // User management
    // Workstation management
});
```

## Security Measures

### Rate Limiting

Login attempts dibatasi untuk mencegah brute force attacks:

- **Maximum attempts**: 5 percobaan
- **Lockout duration**: Variable berdasarkan attempts
- **Throttle key**: Kombinasi NP dan IP address

### Session Security

Session diproteksi dengan security measures sebagai berikut:

- **Session regeneration** setelah login sukses
- **Session invalidation** setelah logout
- **CSRF token regeneration** setelah logout
- **Remember me** functionality dengan secure cookie

### Password Security

Password diproteksi dengan hashing:

- **Algorithm**: Bcrypt (default Laravel)
- **Auto-hashing**: Via `'password' => 'hashed'` cast

## Error Messages

### Login Error Messages

| Scenario | Message (Indonesian) |
|----------|---------------------|
| Invalid credentials | "NP atau password salah" |
| Inactive user | "Akun tidak aktif. Hubungi administrator" |
| Rate limited | "Terlalu banyak percobaan login. Silakan coba lagi dalam X detik." |
| NP required | "NP wajib diisi" |
| Password required | "Password wajib diisi" |
| NP max length | "NP maksimal 5 karakter" |

### Authorization Error Messages

| Scenario | HTTP Code | Message |
|----------|-----------|---------|
| Unauthorized (guest) | 302 | Redirect ke login |
| Forbidden (non-admin) | 403 | "Akses tidak diizinkan. Anda harus memiliki role Administrator." |

## Frontend Integration

### Inertia Shared Data

User data di-share via Inertia middleware untuk diakses di frontend:

```php
// app/Http/Middleware/HandleInertiaRequests.php

'auth' => [
    'user' => $request->user() ? [
        'id' => $request->user()->id,
        'np' => $request->user()->np,
        'name' => $request->user()->name,
        'role' => $request->user()->role->value,
    ] : null,
],
```

### Vue Components

#### Login Page (`pages/Auth/Login.vue`)

Login page dengan Inertia `useForm` helper yang mencakup:

- Input NP dengan auto-uppercase styling
- Input password dengan toggle visibility
- Remember me checkbox
- Submit button dengan loading state
- Error message display dengan validation errors

#### AppLayout (`layouts/AppLayout.vue`)

Layout dengan user menu yang mencakup:

- User info display (NP dan role)
- Logout button dengan POST request via Inertia
- Conditional rendering based on auth state

> **Note**: Untuk detail implementasi UI/UX dan VueUse utilities, lihat [Design System](../03-design/ui-ux/design-system.md)

## Testing

### Test Files

| File | Coverage |
|------|----------|
| `tests/Feature/Auth/LoginTest.php` | Login success, invalid credentials, inactive user |
| `tests/Feature/Auth/LogoutTest.php` | Logout dan session clearing |
| `tests/Feature/Auth/AuthMiddlewareTest.php` | Route protection dan admin access |

### Running Tests

```bash
# Run semua auth tests
php artisan test tests/Feature/Auth/

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run specific test method
php artisan test --filter=test_user_can_login_with_valid_credentials
```

## Quick Reference

### Default Credentials (Development)

```
NP: ADMIN
Password: password
Role: Admin
```

### Useful Commands

```bash
# Create user via tinker
php artisan tinker
>>> User::factory()->admin()->create(['np' => 'ADMIN'])

# Check active users
>>> User::active()->get()

# Reset user password
>>> User::where('np', 'ADMIN')->first()->update(['password' => Hash::make('newpassword')])
```

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
