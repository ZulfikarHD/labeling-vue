# Sprint 5: Order Management

## Overview
Implement production order creation, listing, and detail views. This includes **Order Besar** (large orders processed per rim) and **Order Kecil** (small orders processed in batch).

---

## Story 5.1: Register PO - Order Besar

**As an** operator  
**I want** to register a new production order (large)  
**So that** I can generate labels for per-rim processing

**Acceptance Criteria:**
- [ ] Select team/workstation first
- [ ] Enter PO number
- [ ] Auto-fetch spec from SIRINE API on input
- [ ] Show OBC, type, series, machine from API
- [ ] Enter total sheets (jumlah lembar)
- [ ] System calculates rims and inschiet
- [ ] Generate empty labels on registration
- [ ] Redirect to verification list

### Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| team | select | required | Team/workstation selection |
| po | number | required, unique | Production order number |
| obc | text | readonly | Auto-filled from API |
| type | text | readonly | Product type from API |
| seri | text | readonly | Series from API |
| mesin | text | readonly | Machine from API |
| jml_lembar | number | required, min:1 | Total sheets |

### Workflow

1. User selects team
2. User enters PO number
3. System fetches spec from SIRINE API (debounced)
4. If found → display spec fields (readonly)
5. If not found → show error
6. User confirms total sheets
7. User submits
8. System creates order + generates empty labels
9. Redirect to "PO Siap Verif" list

---

## Story 5.2: PO Ready for Verification List (Order Besar)

**As an** operator  
**I want** to see list of registered POs ready for verification  
**So that** I can select which order to process

**Acceptance Criteria:**
- [ ] Filter by team
- [ ] Show PO number, OBC, type, status
- [ ] Show progress (labels done / total)
- [ ] Click to go to label processing
- [ ] Show registration date

### Page Components

| Component | Description |
|-----------|-------------|
| TeamFilter | Dropdown to filter by team |
| OrderTable | Table of registered orders |
| ProgressIndicator | Shows X/Y labels completed |
| ActionButton | Navigate to cetak label page |

### Table Columns

| Column | Description |
|--------|-------------|
| No PO | Production order number |
| No OBC | OBC reference |
| Type | Product type |
| Status | Badge (Registered/In Progress/Completed) |
| Progress | X/Y labels with visual indicator |
| Registered At | Date formatted |
| Action | Arrow button to process |

---

## Story 5.3: Order Kecil (Small Order - Batch Process)

**As an** operator  
**I want** to create and complete small orders in one step  
**So that** I can quickly process small jobs

**Acceptance Criteria:**
- [ ] Select team
- [ ] Enter PO number
- [ ] Auto-fetch spec from SIRINE API
- [ ] Enter inspector NP (periksa1, periksa2 optional)
- [ ] Enter total sheets
- [ ] System calculates rims
- [ ] All labels created AND assigned at once
- [ ] Order marked as completed immediately
- [ ] Print labels after submission

### Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| team | select | required | Team selection |
| po | number | required | PO number |
| obc | text | readonly | From API |
| type | text | readonly | From API |
| jml_lembar | number | required | Total sheets |
| periksa1 | text | required | Inspector 1 NP |
| periksa2 | text | optional | Inspector 2 NP |

### Workflow

1. User selects team
2. User enters PO number → auto-fetch spec
3. User enters total sheets
4. User enters inspector NP(s)
5. User submits
6. System creates order + generates labels + assigns all to inspector
7. Order status = completed
8. Print dialog opens automatically

---

## Story 5.4: Production Order List

**As a** user  
**I want** to see all production orders  
**So that** I can find and manage orders

**Acceptance Criteria:**
- [ ] List all orders with pagination
- [ ] Search by PO number
- [ ] Filter by status
- [ ] Filter by type (Regular/MMEA)
- [ ] Show progress indicator
- [ ] Click to view detail

### Page Components

| Component | Description |
|-----------|-------------|
| SearchInput | Search by PO number |
| StatusFilter | Filter by status |
| TypeFilter | Filter by order type |
| OrderTable | Paginated order list |
| Pagination | Page navigation |

### Table Columns

| Column | Description |
|--------|-------------|
| No PO | PO number (link to detail) |
| No OBC | OBC reference |
| Type | Regular/MMEA badge |
| Team | Assigned workstation |
| Sum Rim | Total rims |
| Status | Status badge |
| Progress | Progress bar |
| Created | Date created |
| Actions | View, Edit, Delete buttons |

---

## Story 5.5: Edit Production Order

**As an** admin  
**I want** to edit existing production orders  
**So that** I can fix mistakes or update assignments

**Acceptance Criteria:**
- [ ] Edit team assignment
- [ ] Edit rim range (start_rim, end_rim)
- [ ] Cannot edit PO number (readonly)
- [ ] Regenerate labels if rim range changes
- [ ] Only admin or before processing started

### Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| po | text | readonly | Cannot change |
| obc | text | readonly | From API |
| team | select | required | Can reassign team |
| start_rim | number | required, min:1 | Starting rim |
| end_rim | number | required, >= start_rim | Ending rim |
| status | select | admin only | Change status |

---

## Story 5.6: Delete Production Order

**As an** admin  
**I want** to delete orders  
**So that** I can remove incorrect entries

**Acceptance Criteria:**
- [ ] Only admin can delete
- [ ] Confirmation modal with SweetAlert
- [ ] Shows warning about cascade delete
- [ ] Deletes order and all labels
- [ ] Cannot delete completed orders (optional)

### Delete Confirmation

| Element | Description |
|---------|-------------|
| Modal Type | SweetAlert warning |
| Title | "Delete Order?" |
| Message | "Order {PO} and all {X} labels will be deleted" |
| Confirm Button | "Delete" (danger style) |
| Cancel Button | "Cancel" |

---

## Routes Summary

```php
// Order Besar
Route::get('/order-besar/register', [OrderBesarController::class, 'register'])->name('order-besar.register');
Route::post('/order-besar/register', [OrderBesarController::class, 'store'])->name('order-besar.store');
Route::get('/order-besar/verif/{team?}', [OrderBesarController::class, 'poSiapVerif'])->name('order-besar.verif');

// Order Kecil
Route::get('/order-kecil/cetak', [OrderKecilController::class, 'create'])->name('order-kecil.create');
Route::post('/order-kecil/cetak', [OrderKecilController::class, 'store'])->name('order-kecil.store');

// General Orders
Route::get('/orders', [ProductionOrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [ProductionOrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}/edit', [ProductionOrderController::class, 'edit'])->name('orders.edit');
Route::patch('/orders/{order}', [ProductionOrderController::class, 'update'])->name('orders.update');
Route::delete('/orders/{order}', [ProductionOrderController::class, 'destroy'])->name('orders.destroy');
```

---

## Definition of Done (Sprint 5)

- [ ] Can register Order Besar (per-rim processing)
- [ ] Can view PO ready for verification
- [ ] Can create Order Kecil (batch processing)
- [ ] Order list with filters working
- [ ] Can edit orders (admin)
- [ ] Can delete orders (admin)
- [ ] Spec fetched from SIRINE API

---

## Sprint 5 Checklist

```
[ ] 5.1 Register PO - Order Besar
    [ ] Register form page
    [ ] Team selection
    [ ] SIRINE API fetch on PO input
    [ ] Spec display (readonly)
    [ ] Calculate rims/inschiet
    [ ] Generate empty labels
    [ ] Success redirect

[ ] 5.2 PO Ready for Verification
    [ ] List page
    [ ] Team filter
    [ ] Order table
    [ ] Progress indicator
    [ ] Navigate to processing

[ ] 5.3 Order Kecil (Batch)
    [ ] Create form
    [ ] Inspector NP inputs
    [ ] Batch label generation
    [ ] Auto-complete order
    [ ] Print after submit

[ ] 5.4 Production Order List
    [ ] Index page
    [ ] Search
    [ ] Filters
    [ ] Pagination
    [ ] Action buttons

[ ] 5.5 Edit Production Order
    [ ] Edit page
    [ ] Editable fields
    [ ] Validation
    [ ] Label regeneration

[ ] 5.6 Delete Production Order
    [ ] Delete route
    [ ] Confirmation modal
    [ ] Cascade delete
```
