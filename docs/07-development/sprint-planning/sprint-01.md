# Sprint 01: Foundation

Dokumen ini menjelaskan hasil implementasi Sprint 1 Foundation, yaitu: setup project, database migrations, PHP enums, dan Eloquent models yang menjadi fondasi aplikasi Label Generator System.

---

## Overview

Sprint 1 bertujuan untuk membangun fondasi aplikasi yang mencakup:
- Project setup dengan Laravel 12, Vue 3, Inertia.js, dan TailwindCSS
- Database schema untuk menyimpan data users, workstations, production orders, dan labels
- PHP Enums untuk type-safe values
- Eloquent Models dengan relationships dan scopes

---

## Status: COMPLETED

| Story | Status | Catatan |
|-------|--------|---------|
| 1.1 Project Setup | ✅ Selesai | Laravel 12, Vue 3, Inertia.js v2, Tailwind 4 |
| 1.2 Database Migrations | ✅ Selesai | 4 migrations utama |
| 1.3 PHP Enums | ✅ Selesai | 4 enums dibuat |
| 1.4 Eloquent Models | ✅ Selesai | 4 models dengan relationships |

---

## Story 1.1: Project Setup

### Deliverables
- Fresh Laravel 12 project dengan Vue 3 + Inertia.js v2
- TailwindCSS 4 configured
- Base layout component dengan iOS-style design

### Files Created/Modified
- `resources/js/layouts/AppLayout.vue` - Base layout dengan glass effect, spring animations, haptic feedback

### Tech Stack Verified
- PHP 8.4.1
- Laravel 12.40.2
- Vue 3.5.18
- Inertia.js v2.0.17
- TailwindCSS 4.1.11
- Laravel Wayfinder 0.1.12

---

## Story 1.2: Database Migrations

### Deliverables
- Tabel `workstations` untuk data stasiun kerja
- Tabel `users` dengan custom schema (NP, role, workstation_id)
- Tabel `production_orders` untuk data order produksi
- Tabel `labels` untuk data label per rim

### Files Created/Modified
- `database/migrations/2025_12_02_015822_create_workstations_table.php`
- `database/migrations/0001_01_01_000000_create_users_table.php` (modified)
- `database/migrations/2025_12_02_015852_create_production_orders_table.php`
- `database/migrations/2025_12_02_015852_create_labels_table.php`

### Schema Highlights
- Users menggunakan NP (5 digit) sebagai unique identifier
- Production Orders mendukung tipe regular dan MMEA
- Labels memiliki composite unique index untuk mencegah duplikasi

---

## Story 1.3: PHP Enums

### Deliverables
- `OrderType` enum (regular, mmea)
- `OrderStatus` enum (registered, in_progress, completed)
- `CutSide` enum (left, right)
- `UserRole` enum (admin, operator)

### Files Created
- `app/Enums/OrderType.php`
- `app/Enums/OrderStatus.php`
- `app/Enums/CutSide.php`
- `app/Enums/UserRole.php`

### Features
Setiap enum dilengkapi dengan helper methods, antara lain:
- `label()` - Display label untuk UI
- `color()` - Warna badge untuk tampilan
- Domain-specific methods seperti `labelsPerRim()`, `requiresCutSide()`, dll.

---

## Story 1.4: Eloquent Models

### Deliverables
- `User` model dengan relationships dan scopes
- `Workstation` model dengan relationships dan scopes
- `ProductionOrder` model dengan relationships, scopes, dan accessors
- `Label` model dengan relationships, scopes, dan accessors

### Files Created/Modified
- `app/Models/User.php` (modified)
- `app/Models/Workstation.php`
- `app/Models/ProductionOrder.php`
- `app/Models/Label.php`

### Factories Created
- `database/factories/UserFactory.php` (modified)
- `database/factories/WorkstationFactory.php`
- `database/factories/ProductionOrderFactory.php`
- `database/factories/LabelFactory.php`

### Model Features

#### User Model
- **Relationships**: belongsTo(Workstation), hasMany(Label)
- **Scopes**: active(), admins(), operators()
- **Methods**: isAdmin(), isOperator()

#### Workstation Model
- **Relationships**: hasMany(User), hasMany(ProductionOrder), hasMany(Label)
- **Scopes**: active()

#### ProductionOrder Model
- **Relationships**: belongsTo(Workstation as team), hasMany(Label)
- **Scopes**: regular(), mmea(), registered(), inProgress(), completed(), forTeam()
- **Accessors**: has_inschiet, progress
- **Methods**: isRegular(), isMmea(), isCompleted()

#### Label Model
- **Relationships**: belongsTo(ProductionOrder as order), belongsTo(Workstation)
- **Scopes**: pending(), processed(), inschiet(), forOrder()
- **Accessors**: is_completed, is_in_progress
- **Methods**: startInspection(), finishInspection()

---

## Testing

### Test Files Created
- `tests/Feature/Models/WorkstationTest.php` - 7 tests
- `tests/Feature/Models/UserModelTest.php` - 11 tests
- `tests/Feature/Models/ProductionOrderTest.php` - 17 tests
- `tests/Feature/Models/LabelTest.php` - 17 tests

### Test Results
```
Tests:    52 passed (79 assertions)
Duration: 0.69s
```

### Test Coverage
- Factory creation tests
- Relationship tests (belongsTo, hasMany)
- Scope tests
- Accessor tests
- Enum cast tests
- Helper method tests

---

## Verification Checklist

- [x] `php artisan migrate:fresh` berhasil tanpa error
- [x] Semua models dapat dibuat via factory
- [x] Relationships berfungsi dengan benar
- [x] Scopes mengembalikan data yang sesuai
- [x] Accessors menghitung nilai dengan benar
- [x] Enums ter-cast dengan benar
- [x] `vendor/bin/pint --dirty` berhasil
- [x] `yarn run lint` berhasil
- [x] `php artisan test` berhasil (52 tests passed)

---

## Notes

### Known Issues
Tidak ada known issues pada Sprint 1.

### Dependencies untuk Sprint Selanjutnya
Sprint 2 (Authentication) akan membutuhkan:
- Laravel Fortify untuk authentication
- Login form dengan NP sebagai identifier
- User management untuk admin

---

## References

- [Database Schema](../../03-design/database-schema.md)
- [Sprint 1 Original Plan](../../../sprints/01-foundation.md)

---

**Developer**: Zulfikar Hidayatullah  
**Completed**: 2025-12-02  
**Version**: 1.0.0
