# User Management API - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk API user management yang bertujuan untuk menjelaskan endpoints, validasi, dan business logic, yaitu: CRUD operations untuk user, default password handling, dan role-based filtering yang terintegrasi dengan Laravel authentication system.

## Overview

User management API memungkinkan admin untuk mengelola akun pengguna dalam sistem, yang mencakup pembuatan user baru, update informasi user, change password, dan penghapusan akun. Fitur ini hanya dapat diakses oleh user dengan role Admin melalui middleware `admin`.

## Authentication Requirements

Semua endpoint pada user management memerlukan:

- **Authentication**: User harus authenticated (middleware `auth`)
- **Authorization**: User harus memiliki role Admin (middleware `admin`)
- **CSRF Protection**: Berlaku untuk semua request non-GET

## Routes

### User CRUD Routes

| Method | URI | Controller | Action | Name | Description |
|--------|-----|------------|--------|------|-------------|
| GET | `/admin/users` | UserController | index | admin.users.index | Daftar user dengan search/filter |
| GET | `/admin/users/create` | UserController | create | admin.users.create | Form create user |
| POST | `/admin/users` | UserController | store | admin.users.store | Simpan user baru |
| GET | `/admin/users/{user}/edit` | UserController | edit | admin.users.edit | Form edit user |
| PUT/PATCH | `/admin/users/{user}` | UserController | update | admin.users.update | Update user |
| DELETE | `/admin/users/{user}` | UserController | destroy | admin.users.destroy | Hapus user |

### Change Password Route (Admin)

| Method | URI | Controller | Action | Name | Description |
|--------|-----|------------|--------|------|-------------|
| GET | `/admin/change-password` | ChangePasswordController | index | admin.change-password.index | Form change password |
| POST | `/admin/change-password` | ChangePasswordController | store | admin.change-password.store | Proses change password |

## Endpoints

### GET /admin/users

Menampilkan daftar user dengan fitur search dan filter yang mencakup pagination untuk performa optimal.

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Search berdasarkan NP (case-insensitive) |
| role | string | Filter berdasarkan role (`admin`, `operator`) |
| status | string | Filter berdasarkan status (`active`, `inactive`) |
| page | integer | Nomor halaman untuk pagination |

**Response (Inertia):**

```php
Inertia::render('Admin/Users/Index', [
    'users' => $paginatedUsers,
    'filters' => $appliedFilters,
    'roles' => $roleOptions,
]);
```

### POST /admin/users

Membuat user baru dengan opsi password default atau custom.

**Request Body:**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| np | string | required, max:5, unique | Nomor Pegawai |
| name | string | nullable, max:255 | Nama lengkap |
| password | string | required_without:use_default, min:6 | Password custom |
| use_default | boolean | - | Gunakan password default (Peruri + NP) |
| role | string | required, enum:admin,operator | Role user |
| workstation_id | integer | required, exists:workstations | Workstation assignment |
| is_active | boolean | default:true | Status aktif |

**Default Password Logic:**

```php
if ($request->boolean('use_default')) {
    $password = 'Peruri' . strtoupper($data['np']);
}
```

**Success Response:**

- Redirect ke `/admin/users` dengan flash message: "User berhasil dibuat."

### PUT /admin/users/{user}

Memperbarui data user yang sudah ada. NP tidak dapat diubah setelah dibuat.

**Request Body:**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| name | string | nullable, max:255 | Nama lengkap |
| password | string | nullable, min:6 | Password baru (kosongkan jika tidak diubah) |
| role | string | required, enum:admin,operator | Role user |
| workstation_id | integer | required, exists:workstations | Workstation assignment |
| is_active | boolean | - | Status aktif |

**Success Response:**

- Redirect ke `/admin/users` dengan flash message: "User berhasil diperbarui."

### DELETE /admin/users/{user}

Menghapus user dari database dengan validasi agar admin tidak dapat menghapus dirinya sendiri.

**Validation:**

- Admin tidak dapat menghapus akun sendiri

**Success Response:**

- Redirect ke `/admin/users` dengan flash message: "User berhasil dihapus."

**Error Response:**

- Redirect ke `/admin/users` dengan flash message: "Anda tidak dapat menghapus akun sendiri."

### POST /admin/change-password

Mengubah password user oleh admin dengan opsi password default atau custom.

**Request Body:**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| user_id | integer | required, exists:users | ID user target |
| new_password | string | required_without:use_default, min:6, confirmed | Password baru |
| new_password_confirmation | string | required_without:use_default | Konfirmasi password |
| use_default | boolean | - | Reset ke password default |

**Success Response:**

- Redirect ke `/admin/change-password` dengan flash message: "Password untuk user {NP} berhasil diubah."

## Error Messages

### Validation Error Messages

| Field | Scenario | Message |
|-------|----------|---------|
| np | Required | "NP wajib diisi." |
| np | Max length | "NP maksimal 5 karakter." |
| np | Unique | "NP sudah terdaftar." |
| password | Required | "Password wajib diisi jika tidak menggunakan password default." |
| password | Min length | "Password minimal 6 karakter." |
| role | Required | "Role wajib dipilih." |
| role | Invalid | "Role tidak valid." |
| workstation_id | Required | "Workstation wajib dipilih." |
| workstation_id | Not found | "Workstation tidak ditemukan." |

## Data Structures

### User Object

```typescript
interface User {
    id: number;
    np: string;
    name: string | null;
    role: 'admin' | 'operator';
    workstation_id: number | null;
    is_active: boolean;
    workstation?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}
```

### Role Options

```typescript
interface RoleOption {
    value: 'admin' | 'operator';
    label: 'Administrator' | 'Operator';
}
```

## Frontend Integration

### Vue Pages

| Page | Path | Description |
|------|------|-------------|
| Index | `pages/Admin/Users/Index.vue` | Daftar user dengan search/filter |
| Create | `pages/Admin/Users/Create.vue` | Form create user |
| Edit | `pages/Admin/Users/Edit.vue` | Form edit user |
| ChangePassword | `pages/Admin/ChangePassword.vue` | Form change password |

### Wayfinder Usage

```typescript
import { index, create, store, edit, update, destroy } from '@/actions/App/Http/Controllers/Admin/UserController';

// Navigate to user list
index.url() // "/admin/users"

// Navigate to create form
create.url() // "/admin/users/create"

// Submit create form
store.post() // POST /admin/users
```

## Testing

### Running Tests

```bash
# Run semua user management tests
php artisan test tests/Feature/Admin/UserManagementTest.php

# Run specific test
php artisan test --filter=test_admin_can_create_user_with_default_password
```

### Test Coverage

| Test | Scenario |
|------|----------|
| Index | Admin dapat view daftar user |
| Index | Search by NP berfungsi |
| Index | Filter by role berfungsi |
| Index | Filter by status berfungsi |
| Create | Admin dapat create user dengan password default |
| Create | Admin dapat create user dengan custom password |
| Update | Admin dapat update user |
| Update | Password tidak berubah jika kosong |
| Delete | Admin dapat delete user lain |
| Delete | Admin tidak dapat delete diri sendiri |
| Authorization | Operator tidak dapat access user management |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
