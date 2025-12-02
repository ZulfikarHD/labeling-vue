# Authentication Test Cases - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk test cases autentikasi yang bertujuan untuk memastikan functionality login, logout, dan middleware protection berfungsi dengan benar, yaitu: positive tests, negative tests, dan edge cases yang mencakup semua scenario authentication.

## Overview

Test suite authentication menggunakan PHPUnit dengan Laravel testing utilities yang mencakup 25 test cases dalam 3 file test terpisah berdasarkan functionality.

## Test Files

| File | Location | Coverage |
|------|----------|----------|
| LoginTest | `tests/Feature/Auth/LoginTest.php` | Login functionality |
| LogoutTest | `tests/Feature/Auth/LogoutTest.php` | Logout functionality |
| AuthMiddlewareTest | `tests/Feature/Auth/AuthMiddlewareTest.php` | Middleware protection |

## Running Tests

```bash
# Run semua auth tests
php artisan test tests/Feature/Auth/

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run dengan coverage report
php artisan test tests/Feature/Auth/ --coverage

# Run dengan verbose output
php artisan test tests/Feature/Auth/ -v
```

---

## LoginTest

Test class untuk login functionality yang mencakup login success, invalid credentials, dan inactive user scenarios.

### Test Cases

#### TC-LOGIN-001: Login Page Rendering

| Item | Value |
|------|-------|
| **Test Name** | `test_login_page_can_be_rendered` |
| **Scenario** | Guest dapat akses halaman login |
| **Precondition** | User tidak authenticated |
| **Input** | GET `/login` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-LOGIN-002: Valid Login

| Item | Value |
|------|-------|
| **Test Name** | `test_user_can_login_with_valid_credentials` |
| **Scenario** | User dapat login dengan credentials valid |
| **Precondition** | User aktif dengan NP '12345' |
| **Input** | POST `/login` dengan NP dan password yang benar |
| **Expected Result** | User authenticated, redirect ke `/` |
| **Priority** | High |

#### TC-LOGIN-003: NP Uppercase Conversion

| Item | Value |
|------|-------|
| **Test Name** | `test_np_is_converted_to_uppercase` |
| **Scenario** | NP lowercase di-convert ke uppercase |
| **Precondition** | User dengan NP 'ABCDE' |
| **Input** | POST `/login` dengan NP 'abcde' (lowercase) |
| **Expected Result** | User authenticated meskipun input lowercase |
| **Priority** | Medium |

#### TC-LOGIN-004: Invalid Password

| Item | Value |
|------|-------|
| **Test Name** | `test_user_cannot_login_with_invalid_password` |
| **Scenario** | User tidak dapat login dengan password salah |
| **Precondition** | User dengan NP '12345' |
| **Input** | POST `/login` dengan password 'wrong-password' |
| **Expected Result** | User tidak authenticated, session error 'np' |
| **Priority** | High |

#### TC-LOGIN-005: Nonexistent NP

| Item | Value |
|------|-------|
| **Test Name** | `test_user_cannot_login_with_nonexistent_np` |
| **Scenario** | User tidak dapat login dengan NP tidak terdaftar |
| **Precondition** | NP '99999' tidak ada di database |
| **Input** | POST `/login` dengan NP '99999' |
| **Expected Result** | User tidak authenticated, session error 'np' |
| **Priority** | High |

#### TC-LOGIN-006: Inactive User

| Item | Value |
|------|-------|
| **Test Name** | `test_inactive_user_cannot_login` |
| **Scenario** | User tidak aktif tidak dapat login |
| **Precondition** | User dengan NP '12345', is_active = false |
| **Input** | POST `/login` dengan credentials valid |
| **Expected Result** | User tidak authenticated, session error 'np' |
| **Priority** | High |

#### TC-LOGIN-007: Error Message Invalid Credentials

| Item | Value |
|------|-------|
| **Test Name** | `test_displays_error_message_for_invalid_credentials` |
| **Scenario** | Menampilkan error message untuk credentials salah |
| **Precondition** | - |
| **Input** | POST `/login` dengan credentials salah |
| **Expected Result** | Session error: "NP atau password salah" |
| **Priority** | Medium |

#### TC-LOGIN-008: Error Message Inactive User

| Item | Value |
|------|-------|
| **Test Name** | `test_displays_error_message_for_inactive_user` |
| **Scenario** | Menampilkan error message untuk user tidak aktif |
| **Precondition** | User tidak aktif dengan NP '12345' |
| **Input** | POST `/login` dengan credentials valid |
| **Expected Result** | Session error: "Akun tidak aktif. Hubungi administrator" |
| **Priority** | Medium |

#### TC-LOGIN-009: NP Required Validation

| Item | Value |
|------|-------|
| **Test Name** | `test_np_is_required` |
| **Scenario** | Validasi NP wajib diisi |
| **Precondition** | - |
| **Input** | POST `/login` dengan NP kosong |
| **Expected Result** | Session error 'np' |
| **Priority** | High |

#### TC-LOGIN-010: Password Required Validation

| Item | Value |
|------|-------|
| **Test Name** | `test_password_is_required` |
| **Scenario** | Validasi password wajib diisi |
| **Precondition** | - |
| **Input** | POST `/login` dengan password kosong |
| **Expected Result** | Session error 'password' |
| **Priority** | High |

#### TC-LOGIN-011: Authenticated User Redirect

| Item | Value |
|------|-------|
| **Test Name** | `test_authenticated_user_is_redirected_from_login` |
| **Scenario** | User authenticated di-redirect dari login page |
| **Precondition** | User sudah authenticated |
| **Input** | GET `/login` |
| **Expected Result** | Redirect ke `/` |
| **Priority** | Medium |

#### TC-LOGIN-012: Remember Me

| Item | Value |
|------|-------|
| **Test Name** | `test_user_can_login_with_remember_me` |
| **Scenario** | User dapat login dengan remember me option |
| **Precondition** | User aktif dengan NP '12345' |
| **Input** | POST `/login` dengan remember = true |
| **Expected Result** | User authenticated dengan remember cookie |
| **Priority** | Medium |

---

## LogoutTest

Test class untuk logout functionality yang mencakup logout success dan session clearing.

### Test Cases

#### TC-LOGOUT-001: Successful Logout

| Item | Value |
|------|-------|
| **Test Name** | `test_user_can_logout` |
| **Scenario** | User dapat logout |
| **Precondition** | User authenticated |
| **Input** | POST `/logout` |
| **Expected Result** | User tidak authenticated, redirect ke `/login` |
| **Priority** | High |

#### TC-LOGOUT-002: Session Invalidation

| Item | Value |
|------|-------|
| **Test Name** | `test_session_is_invalidated_after_logout` |
| **Scenario** | Session di-invalidate setelah logout |
| **Precondition** | User authenticated |
| **Input** | POST `/logout` |
| **Expected Result** | Session di-regenerate, user guest |
| **Priority** | High |

#### TC-LOGOUT-003: Guest Cannot Logout

| Item | Value |
|------|-------|
| **Test Name** | `test_guest_cannot_access_logout` |
| **Scenario** | Guest tidak dapat akses logout route |
| **Precondition** | User tidak authenticated |
| **Input** | POST `/logout` |
| **Expected Result** | Redirect ke `/login` |
| **Priority** | Medium |

#### TC-LOGOUT-004: Redirect After Logout

| Item | Value |
|------|-------|
| **Test Name** | `test_redirects_to_login_after_logout` |
| **Scenario** | Redirect ke login setelah logout |
| **Precondition** | User authenticated |
| **Input** | POST `/logout` |
| **Expected Result** | Redirect ke `/login` |
| **Priority** | Medium |

#### TC-LOGOUT-005: Protected Route After Logout

| Item | Value |
|------|-------|
| **Test Name** | `test_user_cannot_access_protected_route_after_logout` |
| **Scenario** | User tidak dapat akses protected route setelah logout |
| **Precondition** | User authenticated, akses home, logout |
| **Input** | GET `/` setelah logout |
| **Expected Result** | Redirect ke `/login` |
| **Priority** | High |

---

## AuthMiddlewareTest

Test class untuk middleware functionality yang mencakup route protection dan admin access.

### Test Cases

#### TC-MIDDLEWARE-001: Guest Redirect

| Item | Value |
|------|-------|
| **Test Name** | `test_guest_is_redirected_to_login` |
| **Scenario** | Guest di-redirect ke login saat akses protected route |
| **Precondition** | User tidak authenticated |
| **Input** | GET `/` |
| **Expected Result** | Redirect ke `/login` |
| **Priority** | High |

#### TC-MIDDLEWARE-002: Authenticated Access

| Item | Value |
|------|-------|
| **Test Name** | `test_authenticated_user_can_access_protected_route` |
| **Scenario** | User authenticated dapat akses protected route |
| **Precondition** | User authenticated |
| **Input** | GET `/` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-MIDDLEWARE-003: Admin Access

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_access_admin_route` |
| **Scenario** | Admin dapat akses admin-only route |
| **Precondition** | User dengan role Admin |
| **Input** | GET `/admin-test` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-MIDDLEWARE-004: Operator Cannot Access Admin

| Item | Value |
|------|-------|
| **Test Name** | `test_operator_cannot_access_admin_route` |
| **Scenario** | Operator tidak dapat akses admin-only route |
| **Precondition** | User dengan role Operator |
| **Input** | GET `/admin-test` |
| **Expected Result** | HTTP 403 Forbidden |
| **Priority** | High |

#### TC-MIDDLEWARE-005: Guest Cannot Access Admin

| Item | Value |
|------|-------|
| **Test Name** | `test_guest_cannot_access_admin_route` |
| **Scenario** | Guest tidak dapat akses admin-only route |
| **Precondition** | User tidak authenticated |
| **Input** | GET `/admin-test` |
| **Expected Result** | Redirect ke `/login` |
| **Priority** | High |

#### TC-MIDDLEWARE-006: Inactive User Login Block

| Item | Value |
|------|-------|
| **Test Name** | `test_inactive_user_cannot_login_to_access_protected_route` |
| **Scenario** | User tidak aktif tidak dapat login untuk akses protected route |
| **Precondition** | User tidak aktif dengan NP '12345' |
| **Input** | POST `/login` kemudian GET `/` |
| **Expected Result** | Login gagal, GET `/` redirect ke `/login` |
| **Priority** | Medium |

#### TC-MIDDLEWARE-007: Forbidden Status

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_middleware_returns_forbidden_status` |
| **Scenario** | Admin middleware return 403 untuk non-admin |
| **Precondition** | User Operator |
| **Input** | GET `/admin-test` |
| **Expected Result** | HTTP 403 Forbidden |
| **Priority** | Medium |

#### TC-MIDDLEWARE-008: Intended URL

| Item | Value |
|------|-------|
| **Test Name** | `test_auth_middleware_preserves_intended_url` |
| **Scenario** | Auth middleware preserve intended URL setelah login |
| **Precondition** | Guest akses protected route |
| **Input** | GET `/`, kemudian login |
| **Expected Result** | Redirect ke intended URL (`/`) setelah login |
| **Priority** | Medium |

---

## Test Data

### Factory States

User factory menyediakan states untuk berbagai scenario:

```php
// User aktif dengan role Operator (default)
User::factory()->create()

// User Admin
User::factory()->admin()->create()

// User Operator (explicit)
User::factory()->operator()->create()

// User tidak aktif
User::factory()->inactive()->create()

// Kombinasi
User::factory()->admin()->inactive()->create()
```

### Default Password

Semua factory users menggunakan password: `password`

---

## Test Results Summary

| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| LoginTest | 12 | 28 | ✓ Pass |
| LogoutTest | 5 | 10 | ✓ Pass |
| AuthMiddlewareTest | 8 | 17 | ✓ Pass |
| **Total** | **25** | **55** | **✓ All Pass** |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
