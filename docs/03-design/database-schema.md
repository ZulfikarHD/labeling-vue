# Database Schema

Dokumen ini menjelaskan struktur database untuk Label Generator System, yaitu: tabel-tabel utama, relasi antar tabel, dan penjelasan setiap kolom yang mencakup constraints dan indexes.

---

## Overview

Database menggunakan **SQLite** untuk development dan **MySQL** untuk production. Schema dirancang dengan pendekatan relational yang memastikan integritas data melalui foreign keys dan indexes untuk optimasi query.

---

## Entity Relationship Diagram

```
┌─────────────────┐     ┌─────────────────┐
│   workstations  │     │      users      │
├─────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ workstation_id  │
│ name            │     │ id (PK)         │
│ is_active       │     │ np (UNIQUE)     │
│ created_at      │     │ name            │
│ updated_at      │     │ password        │
└────────┬────────┘     │ role            │
         │              │ is_active       │
         │              └────────┬────────┘
         │                       │
         ▼                       │
┌─────────────────┐              │
│production_orders│              │
├─────────────────┤              │
│ id (PK)         │              │
│ po_number (UQ)  │              │
│ obc_number      │              │
│ order_type      │              │
│ product_type    │              │
│ total_sheets    │              │
│ total_rims      │              │
│ start_rim       │              │
│ end_rim         │              │
│ inschiet_sheets │              │
│ team_id (FK)────┼──────────────┘
│ status          │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│     labels      │
├─────────────────┤
│ id (PK)         │
│ production_     │
│   order_id (FK) │
│ rim_number      │
│ cut_side        │
│ is_inschiet     │
│ inspector_np    │────► users.np
│ inspector_2_np  │────► users.np
│ pack_sheets     │
│ started_at      │
│ finished_at     │
│ workstation_id  │────► workstations.id
│ created_at      │
│ updated_at      │
└─────────────────┘
```

---

## Tabel: workstations

Tabel ini menyimpan data stasiun kerja atau tim produksi yang digunakan untuk mengelompokkan operator dan production orders.

| Kolom | Tipe | Constraint | Deskripsi |
|-------|------|------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(50) | NOT NULL | Nama workstation, contoh: "Team 1", "WS-05" |
| is_active | BOOLEAN | DEFAULT TRUE | Status aktif workstation |
| created_at | TIMESTAMP | | Waktu pembuatan record |
| updated_at | TIMESTAMP | | Waktu update terakhir |

### Relasi
- **hasMany** → `users` (via `workstation_id`)
- **hasMany** → `production_orders` (via `team_id`)
- **hasMany** → `labels` (via `workstation_id`)

---

## Tabel: users

Tabel ini menyimpan data pengguna aplikasi dengan autentikasi menggunakan NP (Nomor Pegawai) sebagai identifier unik.

| Kolom | Tipe | Constraint | Deskripsi |
|-------|------|------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| np | VARCHAR(5) | UNIQUE, NOT NULL | Nomor Pegawai sebagai login identifier |
| name | VARCHAR(100) | NULL | Nama lengkap pengguna (opsional) |
| password | VARCHAR(255) | NOT NULL | Password terenkripsi |
| role | ENUM('admin', 'operator') | DEFAULT 'operator' | Role pengguna dalam sistem |
| workstation_id | BIGINT | FK, NULL | Workstation yang di-assign ke user |
| is_active | BOOLEAN | DEFAULT TRUE | Status aktif user |
| remember_token | VARCHAR(100) | NULL | Token untuk "remember me" |
| created_at | TIMESTAMP | | Waktu pembuatan record |
| updated_at | TIMESTAMP | | Waktu update terakhir |

### Indexes
- `users_np_unique` - Unique index pada kolom `np`
- `users_role_index` - Index pada kolom `role`
- `users_is_active_index` - Index pada kolom `is_active`

### Relasi
- **belongsTo** → `workstations` (via `workstation_id`)
- **hasMany** → `labels` (via `inspector_np`)

### Enum Values: role
- `admin` - Administrator dengan akses penuh
- `operator` - Operator untuk memproses label

---

## Tabel: production_orders

Tabel ini menyimpan data production order dari sistem SIRINE yang berisi informasi order produksi.

| Kolom | Tipe | Constraint | Deskripsi |
|-------|------|------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| po_number | BIGINT | UNIQUE, NOT NULL | Nomor Production Order dari SIRINE |
| obc_number | VARCHAR(50) | NULL | Nomor OBC reference |
| order_type | ENUM('regular', 'mmea') | DEFAULT 'regular' | Tipe order |
| product_type | VARCHAR(50) | NOT NULL | Jenis produk, contoh: "pita cukai" |
| total_sheets | INT UNSIGNED | NOT NULL | Total lembar (sheets) |
| total_rims | INT UNSIGNED | NOT NULL | Total rim: floor(sheets/1000) |
| start_rim | INT UNSIGNED | DEFAULT 1 | Nomor rim awal |
| end_rim | INT UNSIGNED | NOT NULL | Nomor rim akhir |
| inschiet_sheets | INT UNSIGNED | DEFAULT 0 | Lembar sisa (remainder) |
| team_id | BIGINT | FK, NULL | Tim yang ditugaskan |
| status | ENUM(...) | DEFAULT 'registered' | Status order |
| created_at | TIMESTAMP | | Waktu pembuatan record |
| updated_at | TIMESTAMP | | Waktu update terakhir |

### Indexes
- `production_orders_po_number_unique` - Unique index pada `po_number`
- `production_orders_po_number_index` - Index pada `po_number`
- `production_orders_status_index` - Index pada `status`
- `production_orders_order_type_index` - Index pada `order_type`
- `production_orders_team_id_index` - Index pada `team_id`

### Relasi
- **belongsTo** → `workstations` (via `team_id`, alias: `team`)
- **hasMany** → `labels` (via `production_order_id`)

### Enum Values: order_type
- `regular` - Order reguler dengan 2 label per rim (left + right)
- `mmea` - Order MMEA dengan 1 label per rim tanpa cut side

### Enum Values: status
- `registered` - Order terdaftar, belum diproses
- `in_progress` - Order sedang dalam proses
- `completed` - Order sudah selesai

---

## Tabel: labels

Tabel ini menyimpan data label per rim yang merupakan unit terkecil yang di-track dalam sistem.

| Kolom | Tipe | Constraint | Deskripsi |
|-------|------|------------|-----------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| production_order_id | BIGINT | FK, NOT NULL | Production order yang memiliki label |
| rim_number | INT UNSIGNED | NOT NULL | Nomor rim: 1, 2, 3... atau 999 untuk inschiet |
| cut_side | ENUM('left', 'right') | NULL | Sisi potong (NULL untuk MMEA) |
| is_inschiet | BOOLEAN | DEFAULT FALSE | TRUE jika rim 999 |
| inspector_np | VARCHAR(5) | NULL | NP pemeriksa utama |
| inspector_2_np | VARCHAR(5) | NULL | NP pemeriksa kedua (opsional) |
| pack_sheets | INT UNSIGNED | NULL | Lembar per kemasan (khusus MMEA) |
| started_at | DATETIME | NULL | Waktu mulai inspeksi |
| finished_at | DATETIME | NULL | Waktu selesai inspeksi |
| workstation_id | BIGINT | FK, NULL | Workstation tempat label diproses |
| created_at | TIMESTAMP | | Waktu pembuatan record |
| updated_at | TIMESTAMP | | Waktu update terakhir |

### Indexes
- `labels_order_rim_side_unique` - Composite unique index pada (`production_order_id`, `rim_number`, `cut_side`)
- `labels_production_order_id_index` - Index pada `production_order_id`
- `labels_inspector_np_index` - Index pada `inspector_np`

### Relasi
- **belongsTo** → `production_orders` (via `production_order_id`, alias: `order`)
- **belongsTo** → `workstations` (via `workstation_id`)

### Enum Values: cut_side
- `left` - Sisi kiri
- `right` - Sisi kanan
- `NULL` - Tidak ada cut side (untuk MMEA)

---

## Business Rules

### Constants
```php
SHEETS_PER_RIM = 1000
INSCHIET_RIM = 999
```

### Kalkulasi Rim
```php
total_rims = floor(total_sheets / SHEETS_PER_RIM)
inschiet_sheets = total_sheets % SHEETS_PER_RIM
```

### Label Generation Rules

#### Regular Order
- 2 labels per rim: Left dan Right
- Inschiet (rim 999) jika `inschiet_sheets > 0`
- Processing priority: Inschiet first, lalu ascending rim number, left before right

#### MMEA Order
- 1 label per rim
- Tidak ada cut side (`cut_side = NULL`)
- Tidak ada inschiet

---

## Migration Files

| File | Deskripsi |
|------|-----------|
| `0001_01_01_000000_create_users_table.php` | Membuat tabel users, password_reset_tokens, sessions |
| `2025_12_02_015822_create_workstations_table.php` | Membuat tabel workstations |
| `2025_12_02_015852_create_production_orders_table.php` | Membuat tabel production_orders |
| `2025_12_02_015852_create_labels_table.php` | Membuat tabel labels |

---

## Eloquent Models

| Model | File | Relationships |
|-------|------|---------------|
| User | `app/Models/User.php` | belongsTo(Workstation), hasMany(Label) |
| Workstation | `app/Models/Workstation.php` | hasMany(User), hasMany(ProductionOrder), hasMany(Label) |
| ProductionOrder | `app/Models/ProductionOrder.php` | belongsTo(Workstation as team), hasMany(Label) |
| Label | `app/Models/Label.php` | belongsTo(ProductionOrder as order), belongsTo(Workstation) |

---

## PHP Enums

| Enum | File | Values |
|------|------|--------|
| UserRole | `app/Enums/UserRole.php` | admin, operator |
| OrderType | `app/Enums/OrderType.php` | regular, mmea |
| OrderStatus | `app/Enums/OrderStatus.php` | registered, in_progress, completed |
| CutSide | `app/Enums/CutSide.php` | left, right |

---

**Developer**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0.0
