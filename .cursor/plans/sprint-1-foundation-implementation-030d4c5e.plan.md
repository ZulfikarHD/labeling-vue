<!-- 030d4c5e-40a8-43af-b7d8-83968ccc5e24 017e7197-a18c-42e7-b091-0ebe6e10566b -->
# Sprint 1: Foundation Implementation Plan

## Current State Analysis

Project sudah memiliki foundation yang baik:

- Laravel 12.40.2 ✓
- Vue 3.5.18 ✓  
- Inertia.js v2 ✓
- TailwindCSS 4.1.11 ✓
- Wayfinder ✓

Yang masih perlu dikerjakan:

- Base layout component (Story 1.1)
- Custom database schema (Story 1.2)
- PHP Enums (Story 1.3)
- Eloquent Models dengan relationships (Story 1.4)

---

## Story 1.1: Finalisasi Project Setup

**Status**: 90% Complete

**Yang tersisa**:

1. Buat `resources/js/layouts/AppLayout.vue` sebagai base layout dengan iOS-style design
2. Verifikasi app berjalan tanpa error

---

## Story 1.2: Database Migrations

**Files yang akan dibuat/dimodifikasi**:

1. **Modify users migration** - Update schema untuk custom fields (np, role, workstation_id, is_active)

- Remove email field, add np (VARCHAR 5, UNIQUE)
- Add role ENUM, workstation_id FK, is_active

2. **Create workstations migration** - `database/migrations/xxxx_create_workstations_table.php`

- id, name (VARCHAR 50), is_active, timestamps

3. **Create production_orders migration** - `database/migrations/xxxx_create_production_orders_table.php`

- Full schema sesuai spec: po_number, obc_number, order_type, product_type, total_sheets, total_rims, start_rim, end_rim, inschiet_sheets, team_id, status

4. **Create labels migration** - `database/migrations/xxxx_create_labels_table.php`

- Full schema sesuai spec dengan foreign keys dan composite unique index

**Execution Order**: workstations → users → production_orders → labels (karena FK dependencies)

---

## Story 1.3: PHP Enums

**Files yang akan dibuat di `app/Enums/`**:

1. `OrderType.php` - regular, mmea
2. `OrderStatus.php` - registered, in_progress, completed
3. `CutSide.php` - left, right
4. `UserRole.php` - admin, operator

---

## Story 1.4: Eloquent Models

**Files yang akan dibuat/dimodifikasi**:

1. **Update `app/Models/User.php`**:

- Remove email-related fields
- Add relationships: belongsTo Workstation, hasMany Label
- Add casts untuk UserRole enum
- Add scopes: active, admins, operators

2. **Create `app/Models/Workstation.php`**:

- Relationships: hasMany User, ProductionOrder, Label
- Scopes: active

3. **Create `app/Models/ProductionOrder.php`**:

- Relationships: belongsTo Workstation (as team), hasMany Label
- Casts: order_type, status enums
- Scopes: regular, mmea, registered, inProgress, completed, forTeam
- Accessors: hasInschiet, progress

4. **Create `app/Models/Label.php`**:

- Relationships: belongsTo ProductionOrder, Workstation
- Casts: cut_side enum, is_inschiet, started_at, finished_at
- Scopes: pending, processed, inschiet, forOrder
- Accessors: isCompleted, isInProgress

---

## Documentation Update

**Update `documentation-structure.md`**:

- Rewrite dengan Indonesian formal style (yaitu:, antara lain:, dengan demikian)
- Add descriptive penjelasan untuk setiap folder
- Format mengikuti existing docs/README.md style

---

## Testing & Verification

1. Run `php artisan migrate:fresh`
2. Verify all migrations run successfully
3. Test model creation via tinker
4. Run `vendor/bin/pint --dirty` untuk code formatting
5. Run `yarn run lint` untuk frontend lint

### To-dos

- [ ] Buat AppLayout.vue base layout dengan iOS-style design
- [ ] Buat migration untuk workstations table
- [ ] Update users migration dengan custom schema (np, role, workstation_id)
- [ ] Buat migration untuk production_orders table
- [ ] Buat migration untuk labels table dengan FK dan indexes
- [ ] Buat PHP Enums: OrderType, OrderStatus, CutSide, UserRole
- [ ] Buat Workstation model dengan relationships dan scopes
- [ ] Update User model untuk custom schema, relationships, scopes
- [ ] Buat ProductionOrder model dengan relationships, scopes, accessors
- [ ] Buat Label model dengan relationships, scopes, accessors
- [ ] Update documentation-structure.md dengan Indonesian formal style
- [ ] Run migrate:fresh, verify models, run pint dan lint