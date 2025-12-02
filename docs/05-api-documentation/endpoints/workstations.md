# Workstation Management API - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk API workstation management yang bertujuan untuk menjelaskan endpoints, validasi, dan business logic, yaitu: CRUD operations untuk workstation, toggle status aktif, dan relasi dengan user yang terintegrasi dengan sistem production.

## Overview

Workstation management API memungkinkan admin untuk mengelola tim/stasiun kerja produksi dalam sistem, yang mencakup pembuatan workstation baru, update informasi, toggle status aktif, dan penghapusan. Workstation digunakan untuk mengelompokkan operator dan production order dalam satu unit kerja.

## Authentication Requirements

Semua endpoint pada workstation management memerlukan:

- **Authentication**: User harus authenticated (middleware `auth`)
- **Authorization**: User harus memiliki role Admin (middleware `admin`)
- **CSRF Protection**: Berlaku untuk semua request non-GET

## Routes

| Method | URI | Controller | Action | Name | Description |
|--------|-----|------------|--------|------|-------------|
| GET | `/admin/workstations` | WorkstationController | index | admin.workstations.index | Daftar workstation |
| GET | `/admin/workstations/create` | WorkstationController | create | admin.workstations.create | Form create |
| POST | `/admin/workstations` | WorkstationController | store | admin.workstations.store | Simpan baru |
| GET | `/admin/workstations/{workstation}/edit` | WorkstationController | edit | admin.workstations.edit | Form edit |
| PUT/PATCH | `/admin/workstations/{workstation}` | WorkstationController | update | admin.workstations.update | Update |
| DELETE | `/admin/workstations/{workstation}` | WorkstationController | destroy | admin.workstations.destroy | Hapus |
| PATCH | `/admin/workstations/{workstation}/toggle-active` | WorkstationController | toggleActive | admin.workstations.toggle-active | Toggle status |

## Endpoints

### GET /admin/workstations

Menampilkan daftar semua workstation dengan informasi jumlah user yang terdaftar di masing-masing workstation.

**Response (Inertia):**

```php
Inertia::render('Admin/Workstations/Index', [
    'workstations' => Workstation::withCount('users')->orderBy('name')->get(),
]);
```

### POST /admin/workstations

Membuat workstation baru dengan validasi nama unique.

**Request Body:**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| name | string | required, max:50, unique | Nama workstation |
| is_active | boolean | default:true | Status aktif |

**Success Response:**

- Redirect ke `/admin/workstations` dengan flash message: "Workstation berhasil dibuat."

### PUT /admin/workstations/{workstation}

Memperbarui data workstation yang sudah ada.

**Request Body:**

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| name | string | required, max:50, unique (ignore current) | Nama workstation |
| is_active | boolean | - | Status aktif |

**Success Response:**

- Redirect ke `/admin/workstations` dengan flash message: "Workstation berhasil diperbarui."

### DELETE /admin/workstations/{workstation}

Menghapus workstation dari database dengan validasi agar workstation yang masih memiliki user tidak dapat dihapus.

**Validation:**

- Workstation tidak boleh memiliki user yang terdaftar

**Success Response:**

- Redirect ke `/admin/workstations` dengan flash message: "Workstation berhasil dihapus."

**Error Response:**

- Redirect ke `/admin/workstations` dengan flash message: "Tidak dapat menghapus workstation yang masih memiliki user."

### PATCH /admin/workstations/{workstation}/toggle-active

Toggle status aktif workstation antara aktif dan nonaktif.

**Success Response:**

- Redirect ke `/admin/workstations` dengan flash message: "Workstation berhasil diaktifkan/dinonaktifkan."

## Error Messages

### Validation Error Messages

| Field | Scenario | Message |
|-------|----------|---------|
| name | Required | "Nama workstation wajib diisi." |
| name | Max length | "Nama workstation maksimal 50 karakter." |
| name | Unique | "Nama workstation sudah digunakan." |

## Data Structures

### Workstation Object

```typescript
interface Workstation {
    id: number;
    name: string;
    is_active: boolean;
    users_count?: number;
    created_at: string;
    updated_at: string;
}
```

## Business Rules

### Workstation Status

Workstation memiliki dua status, yaitu:

1. **Aktif** - Workstation dapat dipilih saat membuat/edit user
2. **Nonaktif** - Workstation tidak muncul di dropdown selection

### Deletion Rules

Workstation tidak dapat dihapus jika:

- Masih memiliki user yang terdaftar
- Terkait dengan production order yang sedang berjalan

### Naming Convention

Rekomendasi format nama workstation:

- `Team 1`, `Team 2`, `Team 3` - Berdasarkan tim
- `Shift Pagi`, `Shift Siang`, `Shift Malam` - Berdasarkan shift
- `Line A`, `Line B` - Berdasarkan jalur produksi

## Frontend Integration

### Vue Pages

| Page | Path | Description |
|------|------|-------------|
| Index | `pages/Admin/Workstations/Index.vue` | Daftar workstation |
| Create | `pages/Admin/Workstations/Create.vue` | Form create |
| Edit | `pages/Admin/Workstations/Edit.vue` | Form edit |

### Features

- **Stats Cards**: Menampilkan total workstation dan jumlah aktif
- **Toggle Button**: Satu klik untuk toggle status aktif
- **Delete Confirmation**: Modal konfirmasi sebelum hapus
- **User Count**: Menampilkan jumlah user di setiap workstation

## Testing

### Running Tests

```bash
# Run semua workstation management tests
php artisan test tests/Feature/Admin/WorkstationManagementTest.php

# Run specific test
php artisan test --filter=test_admin_can_toggle_workstation_active_status
```

### Test Coverage

| Test | Scenario |
|------|----------|
| Index | Admin dapat view daftar workstation |
| Index | Operator tidak dapat access |
| Index | Guest di-redirect ke login |
| Create | Admin dapat create workstation |
| Create | Name required validation |
| Create | Name unique validation |
| Create | Name max length validation |
| Update | Admin dapat update workstation |
| Update | Nama sama (tidak berubah) valid |
| Update | Nama duplicate gagal |
| Delete | Admin dapat delete workstation tanpa user |
| Delete | Admin tidak dapat delete workstation dengan user |
| Toggle | Admin dapat toggle status aktif |
| Toggle | Operator tidak dapat toggle status |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
