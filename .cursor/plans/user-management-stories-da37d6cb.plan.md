<!-- da37d6cb-302f-4974-ad79-b970321b7b2c a02fc58c-59c0-4f1f-bde8-db78b29b952f -->
# Sprint 2: User Management Stories (2.4-2.8)

## Overview

Implementasi fitur user management untuk admin dan profile settings untuk semua user, yang mencakup CRUD users, password management, dan workstation management dengan dokumentasi lengkap mengikuti Indonesian formal style.

---

## Story 2.4: User Management - List Users (Admin)

### Backend Implementation

**Controller**: `app/Http/Controllers/Admin/UserController.php`

- Method `index()` dengan search (NP), filter (role, status), pagination
- Return Inertia render dengan data users + workstations

**Routes** (dalam `routes/web.php` dengan middleware `auth` dan `admin`):

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
});
```

### Frontend Implementation

**Page**: `resources/js/pages/Admin/Users/Index.vue`

- SearchInput component untuk search by NP
- Filter dropdowns (role, status)
- DataTable dengan columns: NP, Name, Role, Workstation, Status, Actions
- iOS design dengan staggered animations, glass effects

---

## Story 2.5: Create User (Admin)

### Backend Implementation

**Controller**: Method `create()` dan `store()` di `UserController`

**Form Request**: `app/Http/Requests/Admin/StoreUserRequest.php`

- Validation: np (required, unique, max:5), password (required, min:6), role, workstation_id

**Default Password Logic**:

```php
$password = $request->use_default 
    ? 'Peruri' . strtoupper($request->np)
    : $request->password;
```

### Frontend Implementation

**Page**: `resources/js/pages/Admin/Users/Create.vue`

- Form dengan fields: NP, password, use_default checkbox, role select, workstation select
- Auto-fill password saat use_default checked
- Validation errors dengan spring animation

**Page**: `resources/js/pages/Admin/Users/Edit.vue`

- Similar ke Create, dengan data existing user

---

## Story 2.6: Change Password (Admin)

### Backend Implementation

**Controller**: `app/Http/Controllers/Admin/ChangePasswordController.php`

- Method `index()` untuk menampilkan page
- Method `store()` untuk proses change password

**Form Request**: `app/Http/Requests/Admin/ChangePasswordRequest.php`

- Validation: user_id (required, exists), new_password (required, min:6, confirmed)

### Frontend Implementation

**Page**: `resources/js/pages/Admin/ChangePassword.vue`

- User select dropdown
- Password fields dengan toggle visibility
- Default password option checkbox

---

## Story 2.7: Profile Settings

### Backend Implementation

**Controller**: `app/Http/Controllers/ProfileController.php`

- Method `edit()` untuk view profile
- Method `update()` untuk update profile info
- Method `destroy()` untuk delete account (optional)

**Controller**: `app/Http/Controllers/PasswordController.php`

- Method `update()` untuk update own password

**Form Requests**:

- `app/Http/Requests/ProfileUpdateRequest.php`
- `app/Http/Requests/UpdatePasswordRequest.php` (current_password, new_password, confirmed)

### Frontend Implementation

**Page**: `resources/js/pages/Profile/Edit.vue`

- Section: Profile Info (readonly NP, editable name)
- Section: Update Password
- Section: Delete Account (optional)

---

## Story 2.8: Workstation Management (Admin)

### Backend Implementation

**Controller**: `app/Http/Controllers/Admin/WorkstationController.php`

- Full CRUD dengan soft toggle untuk is_active

**Form Request**: `app/Http/Requests/Admin/StoreWorkstationRequest.php`

- Validation: name (required, max:50, unique)

### Frontend Implementation

**Page**: `resources/js/pages/Admin/Workstations/Index.vue`

- List workstations dengan status badge
- Toggle active/inactive dengan haptic feedback

**Page**: `resources/js/pages/Admin/Workstations/Create.vue` dan `Edit.vue`

---

## Documentation Updates

### Files to Update/Create

| Path | Action | Content |

|------|--------|---------|

| `docs/05-api-documentation/endpoints/users.md` | Create | User API endpoints documentation |

| `docs/05-api-documentation/endpoints/workstations.md` | Create | Workstation API endpoints |

| `docs/06-user-guides/admin-guide.md` | Update | Admin guide untuk user & workstation management |

| `docs/08-testing/test-cases/user-management-tests.md` | Create | Test cases untuk user management |

### Writing Style Guidelines

- Bahasa Indonesia formal dengan connector words: "yaitu:", "antara lain:", "dengan demikian,"
- Technical terms tetap English (e.g., "middleware", "validation")
- Author footer: "Zulfikar Hidayatullah"

---

## Implementation Order

1. **Story 2.8** - Workstation Management (dependency untuk user assignment)
2. **Story 2.4** - User List
3. **Story 2.5** - Create User  
4. **Story 2.6** - Change Password (Admin)
5. **Story 2.7** - Profile Settings
6. **Documentation** - Update docs setelah semua fitur complete

---

## Key Files Summary

### Backend (Laravel)

- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/WorkstationController.php`
- `app/Http/Controllers/Admin/ChangePasswordController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/PasswordController.php`
- `app/Http/Requests/Admin/*.php` (Form Requests)

### Frontend (Vue + Inertia)

- `resources/js/pages/Admin/Users/{Index,Create,Edit}.vue`
- `resources/js/pages/Admin/Workstations/{Index,Create,Edit}.vue`
- `resources/js/pages/Admin/ChangePassword.vue`
- `resources/js/pages/Profile/Edit.vue`

### Tests

- `tests/Feature/Admin/UserManagementTest.php`
- `tests/Feature/Admin/WorkstationManagementTest.php`
- `tests/Feature/ProfileTest.php`

### Documentation

- `docs/05-api-documentation/endpoints/users.md`
- `docs/05-api-documentation/endpoints/workstations.md`
- `docs/06-user-guides/admin-guide.md`
- `docs/08-testing/test-cases/user-management-tests.md`

### To-dos

- [ ] Story 2.8: Workstation Management - Controller, Views, Routes, Tests
- [ ] Story 2.4: User List - Controller index, Index.vue dengan search/filter
- [ ] Story 2.5: Create User - store method, Create.vue, Edit.vue
- [ ] Story 2.6: Admin Change Password - ChangePasswordController, Vue page
- [ ] Story 2.7: Profile Settings - ProfileController, PasswordController, Edit.vue
- [ ] Update documentation: users.md, workstations.md, admin-guide.md, test-cases