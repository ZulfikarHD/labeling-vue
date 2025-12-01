# Sprint 1: Foundation

## Overview
Setup fresh Laravel 11 project with Vue 3, Inertia.js, and TailwindCSS. Create database schema and models.

---

## Story 1.1: Project Setup

**As a** developer  
**I want** a fresh Laravel 11 project with Vue 3 + Inertia + Tailwind  
**So that** I have a clean foundation to build the label generator app

**Acceptance Criteria:**
- [ ] Fresh Laravel 11 installed
- [ ] Vue 3 configured with Inertia.js
- [ ] TailwindCSS installed and configured
- [ ] Base layout component created
- [ ] App runs without errors on `php artisan serve`

**Technical Notes:**
- Use Laravel Breeze with Vue + Inertia stack (but we'll customize auth later)
- Or manual setup: `composer require inertiajs/inertia-laravel`, `npm install @inertiajs/vue3`

---

## Story 1.2: Database Migrations

**As a** developer  
**I want** database tables created with proper schema  
**So that** I can store users, orders, and labels

**Acceptance Criteria:**
- [ ] `users` table created
- [ ] `workstations` table created
- [ ] `production_orders` table created
- [ ] `labels` table created
- [ ] All foreign keys defined
- [ ] Indexes on frequently queried columns
- [ ] Migrations run without errors

### Schema Details

#### users
```sql
id              BIGINT PK AUTO_INCREMENT
np              VARCHAR(5) UNIQUE NOT NULL    -- Employee number (Nomor Pegawai)
name            VARCHAR(100) NULL             -- Display name (optional)
password        VARCHAR(255) NOT NULL
role            ENUM('admin', 'operator') DEFAULT 'operator'
workstation_id  BIGINT FK NULL → workstations.id
is_active       BOOLEAN DEFAULT TRUE
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### workstations
```sql
id          BIGINT PK AUTO_INCREMENT
name        VARCHAR(50) NOT NULL              -- e.g., "Team 1", "WS-05"
is_active   BOOLEAN DEFAULT TRUE
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

#### production_orders
```sql
id              BIGINT PK AUTO_INCREMENT
po_number       BIGINT UNIQUE NOT NULL        -- Production Order number
obc_number      VARCHAR(50) NULL              -- OBC reference
order_type      ENUM('regular', 'mmea') NOT NULL DEFAULT 'regular'
product_type    VARCHAR(50) NOT NULL          -- e.g., 'pita cukai', 'hologram'
total_sheets    INT NOT NULL                  -- Total lembar (sheets)
total_rims      INT NOT NULL                  -- Calculated: floor(sheets/1000)
start_rim       INT DEFAULT 1
end_rim         INT NOT NULL
inschiet_sheets INT DEFAULT 0                 -- Remainder sheets
team_id         BIGINT FK NULL → workstations.id
status          ENUM('registered', 'in_progress', 'completed') DEFAULT 'registered'
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX: po_number, status, order_type, team_id
```

#### labels
```sql
id                  BIGINT PK AUTO_INCREMENT
production_order_id BIGINT FK NOT NULL → production_orders.id ON DELETE CASCADE
rim_number          INT NOT NULL              -- 1, 2, 3... or 999 for inschiet
cut_side            ENUM('left', 'right') NULL  -- NULL for MMEA orders
is_inschiet         BOOLEAN DEFAULT FALSE     -- TRUE if rim 999
inspector_np        VARCHAR(5) NULL           -- Primary inspector NP
inspector_2_np      VARCHAR(5) NULL           -- Secondary inspector NP (optional)
pack_sheets         INT NULL                  -- MMEA only: lembar per kemasan
started_at          DATETIME NULL             -- When inspection started
finished_at         DATETIME NULL             -- When inspection completed
workstation_id      BIGINT FK NULL → workstations.id
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE: (production_order_id, rim_number, cut_side)
INDEX: production_order_id, inspector_np
```

**Dependencies:**
- Story 1.1 completed

---

## Story 1.3: Enums

**As a** developer  
**I want** PHP Enums for type-safe values  
**So that** I avoid magic strings and get IDE support

**Acceptance Criteria:**
- [ ] `OrderType` enum created (regular, mmea)
- [ ] `OrderStatus` enum created (registered, in_progress, completed)
- [ ] `CutSide` enum created (left, right)
- [ ] `UserRole` enum created (admin, operator)
- [ ] All enums in `app/Enums/` folder

### Enum Definitions

```php
// app/Enums/OrderType.php
enum OrderType: string
{
    case Regular = 'regular';
    case Mmea = 'mmea';
}

// app/Enums/OrderStatus.php
enum OrderStatus: string
{
    case Registered = 'registered';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}

// app/Enums/CutSide.php
enum CutSide: string
{
    case Left = 'left';
    case Right = 'right';
}

// app/Enums/UserRole.php
enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';
}
```

**Dependencies:**
- Story 1.1 completed

---

## Story 1.4: Eloquent Models

**As a** developer  
**I want** Eloquent models with relationships defined  
**So that** I can easily query and manipulate data

**Acceptance Criteria:**
- [ ] `User` model with relationships
- [ ] `Workstation` model with relationships
- [ ] `ProductionOrder` model with relationships
- [ ] `Label` model with relationships
- [ ] All models cast enums properly
- [ ] Scopes defined for common queries

### Model Specifications

#### User Model
```php
// Relationships
belongsTo: Workstation (workstation_id)
hasMany: Label (inspector_np) - labels they inspected

// Casts
role → UserRole enum
is_active → boolean

// Scopes
scopeActive($query) - where is_active = true
scopeAdmins($query) - where role = admin
scopeOperators($query) - where role = operator
```

#### Workstation Model
```php
// Relationships
hasMany: User (workstation_id)
hasMany: ProductionOrder (team_id)
hasMany: Label (workstation_id)

// Casts
is_active → boolean

// Scopes
scopeActive($query)
```

#### ProductionOrder Model
```php
// Relationships
belongsTo: Workstation (team_id) as 'team'
hasMany: Label (production_order_id)

// Casts
order_type → OrderType enum
status → OrderStatus enum

// Scopes
scopeRegular($query) - where order_type = regular
scopeMmea($query) - where order_type = mmea
scopeRegistered($query) - where status = registered
scopeInProgress($query) - where status = in_progress
scopeCompleted($query) - where status = completed
scopeForTeam($query, $teamId)

// Accessors
hasInschiet → boolean (inschiet_sheets > 0)
progress → int (percentage of completed labels)
```

#### Label Model
```php
// Relationships
belongsTo: ProductionOrder (production_order_id) as 'order'
belongsTo: Workstation (workstation_id)

// Casts
cut_side → CutSide enum (nullable)
is_inschiet → boolean
started_at → datetime
finished_at → datetime

// Scopes
scopePending($query) - whereNull inspector_np
scopeProcessed($query) - whereNotNull inspector_np
scopeInschiet($query) - where is_inschiet = true
scopeForOrder($query, $orderId)

// Accessors
isCompleted → boolean (finished_at not null)
isInProgress → boolean (started_at not null && finished_at null)
```

**Dependencies:**
- Story 1.2 completed
- Story 1.3 completed

---

## Definition of Done (Sprint 1)

- [ ] All migrations run successfully
- [ ] All models created with relationships
- [ ] All enums created
- [ ] `php artisan migrate:fresh` works
- [ ] Can create test records via tinker
- [ ] No linting errors
- [ ] Code follows naming conventions in `.cursorrules`

---

## Sprint 1 Checklist

```
[ ] 1.1 Project Setup
    [ ] Laravel 11 installed
    [ ] Vue 3 + Inertia configured
    [ ] TailwindCSS configured
    [ ] Base layout created

[ ] 1.2 Database Migrations
    [ ] users migration
    [ ] workstations migration
    [ ] production_orders migration
    [ ] labels migration
    [ ] Foreign keys & indexes

[ ] 1.3 Enums
    [ ] OrderType
    [ ] OrderStatus
    [ ] CutSide
    [ ] UserRole

[ ] 1.4 Models
    [ ] User model
    [ ] Workstation model
    [ ] ProductionOrder model
    [ ] Label model
    [ ] All relationships defined
    [ ] All scopes defined
```

