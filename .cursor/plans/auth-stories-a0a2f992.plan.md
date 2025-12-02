<!-- a0a2f992-3f28-413c-925b-385c910fc939 c73ef397-7e96-48e7-acf2-5b6072d407e7 -->
# Implementation Plan: Authentication Stories 2.1-2.3

## Konteks

Aplikasi sudah memiliki foundation yang solid:

- [User.php](app/Models/User.php) dengan `np`, `password`, `role`, `is_active`
- [UserRole.php](app/Enums/UserRole.php) enum (Admin/Operator)
- [AppLayout.vue](resources/js/layouts/AppLayout.vue) dengan iOS-inspired design
- Database schema `users` table siap digunakan

## Story 2.1: Login System

### Backend Implementation

| File | Purpose |

|------|---------|

| `app/Http/Controllers/Auth/LoginController.php` | Handle login logic dengan NP uppercase conversion |

| `app/Http/Requests/Auth/LoginRequest.php` | Form Request validation dengan custom messages |

**Key Logic:**

- NP input di-convert ke UPPERCASE via `strtoupper()`
- Validate credentials via `Auth::attempt(['np' => $np, 'password' => $password])`
- Check `is_active` status sebelum allow login
- Regenerate session untuk security

### Frontend Implementation

| File | Purpose |

|------|---------|

| `resources/js/pages/Auth/Login.vue` | Login form dengan iOS-inspired design |

**UI Components:**

- Input NP (text, auto-uppercase via CSS)
- Input password (password toggle visibility)
- Remember me checkbox
- Submit button dengan loading state
- Error messages styling

### Routes

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
```

---

## Story 2.2: Logout

### Backend Implementation

**Di `LoginController.php`:**

- Method `destroy()` untuk logout
- Clear session via `Auth::guard('web')->logout()`
- Invalidate session dan regenerate token

### Frontend Implementation

**Update `AppLayout.vue`:**

- Tambah logout button di navigation
- Gunakan Inertia Form untuk POST request
- Loading state saat logout

### Routes

```php
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
```

---

## Story 2.3: Auth Middleware

### Middleware Implementation

| File | Purpose |

|------|---------|

| `app/Http/Middleware/EnsureUserIsAdmin.php` | Check role admin sebelum access |

**Registration di [bootstrap/app.php](bootstrap/app.php):**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => EnsureUserIsAdmin::class,
    ]);
});
```

### Protection Logic

| Middleware | Behavior |

|------------|----------|

| `auth` | Redirect ke login jika belum authenticated |

| `admin` | Return 403 jika role bukan Admin |

---

## Tests Implementation

| File | Coverage |

|------|----------|

| `tests/Feature/Auth/LoginTest.php` | Login success, invalid credentials, inactive user |

| `tests/Feature/Auth/LogoutTest.php` | Logout dan session clear |

| `tests/Feature/Auth/AuthMiddlewareTest.php` | Route protection dan admin access |

---

## Documentation Updates

### Files to Update

| File | Content |

|------|---------|

| `docs/05-api-documentation/authentication.md` | Complete auth flow documentation |

| `docs/08-testing/test-cases/authentication-tests.md` | Test cases documentation |

### Writing Style (Indonesian Formal)

- Connector words: "yaitu:", "antara lain:", "dengan demikian,"
- Technical terms in English, explanations in Indonesian
- Code examples dengan PHP/Vue snippets
- Tables untuk structured data

---

## Summary Files

**Create:**

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Middleware/EnsureUserIsAdmin.php`
- `resources/js/pages/Auth/Login.vue`
- `tests/Feature/Auth/LoginTest.php`
- `tests/Feature/Auth/LogoutTest.php`
- `tests/Feature/Auth/AuthMiddlewareTest.php`

**Modify:**

- `routes/web.php`
- `bootstrap/app.php`
- `resources/js/layouts/AppLayout.vue`
- `docs/05-api-documentation/authentication.md`
- `docs/08-testing/test-cases/authentication-tests.md`

### To-dos

- [ ] Buat LoginController dan LoginRequest dengan NP uppercase conversion
- [ ] Buat Login.vue page dengan iOS-inspired design
- [ ] Implementasi logout method dan update AppLayout dengan logout button
- [ ] Buat EnsureUserIsAdmin middleware dan register di bootstrap/app.php
- [ ] Update routes/web.php dengan auth routes
- [ ] Buat PHPUnit tests untuk login, logout, dan middleware
- [ ] Update dokumentasi authentication dan test cases