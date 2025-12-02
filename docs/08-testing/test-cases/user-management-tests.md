# User Management Test Cases - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk test cases user management yang bertujuan untuk memastikan functionality CRUD user, workstation management, dan profile settings berfungsi dengan benar, yaitu: positive tests, negative tests, dan authorization tests yang mencakup semua scenario admin features.

## Overview

Test suite user management menggunakan PHPUnit dengan Laravel testing utilities yang mencakup 35 test cases dalam 2 file test utama untuk admin features, ditambah tests untuk profile management.

## Test Files

| File | Location | Coverage |
|------|----------|----------|
| UserManagementTest | `tests/Feature/Admin/UserManagementTest.php` | User CRUD |
| WorkstationManagementTest | `tests/Feature/Admin/WorkstationManagementTest.php` | Workstation CRUD |
| ProfileTest | `tests/Feature/ProfileTest.php` | Profile & Password |

## Running Tests

```bash
# Run semua admin tests
php artisan test tests/Feature/Admin/

# Run specific test file
php artisan test tests/Feature/Admin/UserManagementTest.php

# Run dengan coverage report
php artisan test tests/Feature/Admin/ --coverage

# Run dengan verbose output
php artisan test tests/Feature/Admin/ -v
```

---

## UserManagementTest

Test class untuk user management functionality yang mencakup CRUD operations dan authorization.

### Index Tests

#### TC-USER-001: Admin Can View User Index

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_user_index` |
| **Scenario** | Admin dapat akses halaman daftar user |
| **Precondition** | User dengan role Admin |
| **Input** | GET `/admin/users` |
| **Expected Result** | HTTP 200 OK, component Admin/Users/Index |
| **Priority** | High |

#### TC-USER-002: Search Users by NP

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_search_users_by_np` |
| **Scenario** | Admin dapat search user berdasarkan NP |
| **Precondition** | User dengan NP berbeda |
| **Input** | GET `/admin/users?search=ABC` |
| **Expected Result** | Filter applied, results filtered |
| **Priority** | Medium |

#### TC-USER-003: Filter Users by Role

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_filter_users_by_role` |
| **Scenario** | Admin dapat filter user berdasarkan role |
| **Precondition** | Users dengan role berbeda |
| **Input** | GET `/admin/users?role=operator` |
| **Expected Result** | Filter applied |
| **Priority** | Medium |

#### TC-USER-004: Filter Users by Status

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_filter_users_by_status` |
| **Scenario** | Admin dapat filter user berdasarkan status |
| **Precondition** | Users dengan status berbeda |
| **Input** | GET `/admin/users?status=inactive` |
| **Expected Result** | Filter applied |
| **Priority** | Medium |

#### TC-USER-005: Operator Cannot View Index

| Item | Value |
|------|-------|
| **Test Name** | `test_operator_cannot_view_user_index` |
| **Scenario** | Operator tidak dapat akses user management |
| **Precondition** | User dengan role Operator |
| **Input** | GET `/admin/users` |
| **Expected Result** | HTTP 403 Forbidden |
| **Priority** | High |

### Create Tests

#### TC-USER-006: Admin Can View Create Page

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_create_user_page` |
| **Scenario** | Admin dapat akses halaman create user |
| **Precondition** | User Admin, workstations exist |
| **Input** | GET `/admin/users/create` |
| **Expected Result** | HTTP 200 OK, has workstations and roles |
| **Priority** | High |

#### TC-USER-007: Create User with Default Password

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_create_user_with_default_password` |
| **Scenario** | Admin dapat create user dengan password default |
| **Precondition** | Workstation exists |
| **Input** | POST `/admin/users` dengan use_default=true |
| **Expected Result** | User created, password = Peruri[NP] |
| **Priority** | High |

#### TC-USER-008: Create User with Custom Password

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_create_user_with_custom_password` |
| **Scenario** | Admin dapat create user dengan custom password |
| **Precondition** | Workstation exists |
| **Input** | POST `/admin/users` dengan password custom |
| **Expected Result** | User created with custom password |
| **Priority** | High |

#### TC-USER-009: NP Required Validation

| Item | Value |
|------|-------|
| **Test Name** | `test_np_is_required` |
| **Scenario** | Validasi NP wajib diisi |
| **Precondition** | - |
| **Input** | POST `/admin/users` dengan NP kosong |
| **Expected Result** | Session error 'np' |
| **Priority** | High |

#### TC-USER-010: NP Unique Validation

| Item | Value |
|------|-------|
| **Test Name** | `test_np_must_be_unique` |
| **Scenario** | Validasi NP harus unique |
| **Precondition** | User dengan NP sama sudah ada |
| **Input** | POST `/admin/users` dengan NP duplicate |
| **Expected Result** | Session error 'np' |
| **Priority** | High |

#### TC-USER-011: NP Max Length Validation

| Item | Value |
|------|-------|
| **Test Name** | `test_np_max_length` |
| **Scenario** | Validasi NP maksimal 5 karakter |
| **Precondition** | - |
| **Input** | POST `/admin/users` dengan NP > 5 chars |
| **Expected Result** | Session error 'np' |
| **Priority** | Medium |

#### TC-USER-012: Workstation Required

| Item | Value |
|------|-------|
| **Test Name** | `test_workstation_is_required` |
| **Scenario** | Validasi workstation wajib dipilih |
| **Precondition** | - |
| **Input** | POST `/admin/users` tanpa workstation |
| **Expected Result** | Session error 'workstation_id' |
| **Priority** | High |

### Edit/Update Tests

#### TC-USER-013: Admin Can View Edit Page

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_edit_user_page` |
| **Scenario** | Admin dapat akses halaman edit user |
| **Precondition** | User exists |
| **Input** | GET `/admin/users/{id}/edit` |
| **Expected Result** | HTTP 200 OK, has user data |
| **Priority** | High |

#### TC-USER-014: Admin Can Update User

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_update_user` |
| **Scenario** | Admin dapat update user |
| **Precondition** | User exists |
| **Input** | PUT `/admin/users/{id}` |
| **Expected Result** | User updated, redirect success |
| **Priority** | High |

#### TC-USER-015: Admin Can Update Password

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_update_user_password` |
| **Scenario** | Admin dapat update password user |
| **Precondition** | User exists |
| **Input** | PUT `/admin/users/{id}` dengan password baru |
| **Expected Result** | Password changed |
| **Priority** | High |

#### TC-USER-016: Password Not Changed if Empty

| Item | Value |
|------|-------|
| **Test Name** | `test_password_not_changed_if_empty` |
| **Scenario** | Password tidak berubah jika field kosong |
| **Precondition** | User exists with password |
| **Input** | PUT `/admin/users/{id}` dengan password kosong |
| **Expected Result** | Password tetap sama |
| **Priority** | Medium |

### Delete Tests

#### TC-USER-017: Admin Can Delete Other User

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_delete_other_user` |
| **Scenario** | Admin dapat delete user lain |
| **Precondition** | Another user exists |
| **Input** | DELETE `/admin/users/{id}` |
| **Expected Result** | User deleted |
| **Priority** | High |

#### TC-USER-018: Admin Cannot Delete Self

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_cannot_delete_self` |
| **Scenario** | Admin tidak dapat delete diri sendiri |
| **Precondition** | Admin logged in |
| **Input** | DELETE `/admin/users/{adminId}` |
| **Expected Result** | Error, admin not deleted |
| **Priority** | High |

---

## WorkstationManagementTest

Test class untuk workstation management functionality yang mencakup CRUD operations dan toggle status.

### Index Tests

#### TC-WS-001: Admin Can View Index

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_workstation_index` |
| **Scenario** | Admin dapat akses halaman daftar workstation |
| **Precondition** | Workstations exist |
| **Input** | GET `/admin/workstations` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-WS-002: Operator Cannot View Index

| Item | Value |
|------|-------|
| **Test Name** | `test_operator_cannot_view_workstation_index` |
| **Scenario** | Operator tidak dapat akses |
| **Precondition** | User Operator |
| **Input** | GET `/admin/workstations` |
| **Expected Result** | HTTP 403 Forbidden |
| **Priority** | High |

#### TC-WS-003: Guest Redirected

| Item | Value |
|------|-------|
| **Test Name** | `test_guest_is_redirected_from_workstation_index` |
| **Scenario** | Guest di-redirect ke login |
| **Precondition** | Not authenticated |
| **Input** | GET `/admin/workstations` |
| **Expected Result** | Redirect to `/login` |
| **Priority** | High |

### Create Tests

#### TC-WS-004: Admin Can View Create Page

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_create_workstation_page` |
| **Scenario** | Admin dapat akses halaman create |
| **Precondition** | User Admin |
| **Input** | GET `/admin/workstations/create` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-WS-005: Admin Can Create Workstation

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_create_workstation` |
| **Scenario** | Admin dapat create workstation |
| **Precondition** | User Admin |
| **Input** | POST `/admin/workstations` |
| **Expected Result** | Workstation created |
| **Priority** | High |

#### TC-WS-006: Name Required

| Item | Value |
|------|-------|
| **Test Name** | `test_workstation_name_is_required` |
| **Scenario** | Validasi nama wajib diisi |
| **Precondition** | - |
| **Input** | POST dengan name kosong |
| **Expected Result** | Session error 'name' |
| **Priority** | High |

#### TC-WS-007: Name Unique

| Item | Value |
|------|-------|
| **Test Name** | `test_workstation_name_must_be_unique` |
| **Scenario** | Validasi nama harus unique |
| **Precondition** | Workstation with same name exists |
| **Input** | POST dengan name duplicate |
| **Expected Result** | Session error 'name' |
| **Priority** | High |

#### TC-WS-008: Name Max Length

| Item | Value |
|------|-------|
| **Test Name** | `test_workstation_name_max_length` |
| **Scenario** | Validasi nama maks 50 karakter |
| **Precondition** | - |
| **Input** | POST dengan name > 50 chars |
| **Expected Result** | Session error 'name' |
| **Priority** | Medium |

### Update Tests

#### TC-WS-009: Admin Can View Edit Page

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_view_edit_workstation_page` |
| **Scenario** | Admin dapat akses halaman edit |
| **Precondition** | Workstation exists |
| **Input** | GET `/admin/workstations/{id}/edit` |
| **Expected Result** | HTTP 200 OK |
| **Priority** | High |

#### TC-WS-010: Admin Can Update

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_update_workstation` |
| **Scenario** | Admin dapat update workstation |
| **Precondition** | Workstation exists |
| **Input** | PUT `/admin/workstations/{id}` |
| **Expected Result** | Workstation updated |
| **Priority** | High |

#### TC-WS-011: Update Without Changing Name

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_update_workstation_without_changing_name` |
| **Scenario** | Update valid dengan nama tidak berubah |
| **Precondition** | Workstation exists |
| **Input** | PUT dengan name sama |
| **Expected Result** | Update success |
| **Priority** | Medium |

#### TC-WS-012: Update with Existing Name Fails

| Item | Value |
|------|-------|
| **Test Name** | `test_update_workstation_with_existing_name_fails` |
| **Scenario** | Update dengan nama duplicate gagal |
| **Precondition** | Two workstations exist |
| **Input** | PUT dengan name dari ws lain |
| **Expected Result** | Session error 'name' |
| **Priority** | High |

### Delete Tests

#### TC-WS-013: Delete Without Users

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_delete_workstation_without_users` |
| **Scenario** | Admin dapat delete workstation tanpa user |
| **Precondition** | Workstation tanpa user |
| **Input** | DELETE `/admin/workstations/{id}` |
| **Expected Result** | Workstation deleted |
| **Priority** | High |

#### TC-WS-014: Cannot Delete With Users

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_cannot_delete_workstation_with_users` |
| **Scenario** | Tidak dapat delete workstation dengan user |
| **Precondition** | Workstation dengan user |
| **Input** | DELETE `/admin/workstations/{id}` |
| **Expected Result** | Error, not deleted |
| **Priority** | High |

### Toggle Tests

#### TC-WS-015: Toggle Active Status

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_toggle_workstation_active_status` |
| **Scenario** | Admin dapat toggle status aktif |
| **Precondition** | Active workstation |
| **Input** | PATCH `/admin/workstations/{id}/toggle-active` |
| **Expected Result** | Status changed to inactive |
| **Priority** | High |

#### TC-WS-016: Toggle Inactive to Active

| Item | Value |
|------|-------|
| **Test Name** | `test_admin_can_toggle_inactive_workstation_to_active` |
| **Scenario** | Toggle dari nonaktif ke aktif |
| **Precondition** | Inactive workstation |
| **Input** | PATCH toggle-active |
| **Expected Result** | Status changed to active |
| **Priority** | High |

#### TC-WS-017: Operator Cannot Toggle

| Item | Value |
|------|-------|
| **Test Name** | `test_operator_cannot_toggle_workstation_status` |
| **Scenario** | Operator tidak dapat toggle status |
| **Precondition** | User Operator |
| **Input** | PATCH toggle-active |
| **Expected Result** | HTTP 403 Forbidden |
| **Priority** | High |

---

## Test Results Summary

| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| UserManagementTest | 18 | 106 | ✓ Pass |
| WorkstationManagementTest | 17 | 71 | ✓ Pass |
| **Total** | **35** | **177** | **✓ All Pass** |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete

