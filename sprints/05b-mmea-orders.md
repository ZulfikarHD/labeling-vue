# Sprint 5B: MMEA Order Management

## Overview
MMEA orders have a **separate workflow** from regular orders. Key differences:
- No Kiri/Kanan (cut_side is NULL)
- No inschiet handling
- Different SIRINE API endpoint
- Different label format (includes lbr_kemas/pack_sheets)

---

## Story 5B.1: Register MMEA Order

**As an** operator  
**I want** to register MMEA production orders  
**So that** I can generate labels for MMEA products

**Acceptance Criteria:**
- [ ] Enter PO number
- [ ] Fetch spec from MMEA-specific API endpoint
- [ ] Show MMEA-specific fields (lbr_kemas)
- [ ] Enter total rims (not sheets)
- [ ] Generate labels without cut_side
- [ ] No inschiet calculation

### Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| po | number | required, unique | PO number |
| obc | text | readonly | From MMEA API |
| type | text | readonly | Product type |
| seri | text | readonly | Series |
| lbr_kemas | number | readonly | Sheets per pack (from API) |
| jml_rim | number | required, min:1 | Total rims to generate |
| team | select | required | Team assignment |

### Differences from Regular

| Aspect | Regular | MMEA |
|--------|---------|------|
| API Endpoint | /detail-order-pcht/{po} | /detail-order-mmea/{po} |
| Input | Total sheets | Total rims |
| Cut side | Left/Right | NULL |
| Inschiet | Yes (rim 999) | No |
| Labels per rim | 2 | 1 |
| Extra field | - | lbr_kemas (pack sheets) |

---

## Story 5B.2: MMEA Order List

**As a** user  
**I want** to see list of MMEA orders  
**So that** I can manage MMEA production

**Acceptance Criteria:**
- [ ] List all MMEA orders
- [ ] Search by PO number
- [ ] Show status, progress
- [ ] Actions: View, Edit, Delete, Print

### Page Components

| Component | Description |
|-----------|-------------|
| SearchInput | Search by PO |
| OrderTable | MMEA orders list |
| Pagination | Page navigation |
| ActionButtons | View, Edit, Delete, Print |

### Table Columns

| Column | Description |
|--------|-------------|
| No PO | PO number |
| No OBC | OBC reference |
| Type | Product type |
| Lbr Kemas | Pack sheets |
| Status | Status badge |
| Created | Registration date |
| Actions | View, Edit, Delete, Print icons |

---

## Story 5B.3: View MMEA Order Detail

**As a** user  
**I want** to see MMEA order details  
**So that** I can check production status

**Acceptance Criteria:**
- [ ] Show order info
- [ ] Show spec from API
- [ ] Show label list (no cut_side column)
- [ ] Show progress

### Order Detail Fields

| Field | Description |
|-------|-------------|
| No PO | Production order number |
| No OBC | OBC reference |
| Type | Product type |
| Lbr Kemas | Sheets per pack |
| Total Rims | Number of rims |
| Team | Assigned team |
| Status | Current status |
| Created | Registration date |

### Label Table Columns (MMEA)

| Column | Description |
|--------|-------------|
| Rim | Rim number |
| Inspector 1 | Periksa 1 NP |
| Inspector 2 | Periksa 2 NP |
| Time | Processing timestamp |
| Status | Done/Pending |
| Actions | Print button |

---

## Story 5B.4: Edit MMEA Order

**As an** admin  
**I want** to edit MMEA orders  
**So that** I can fix mistakes

**Acceptance Criteria:**
- [ ] Edit team assignment
- [ ] Edit rim count
- [ ] Regenerate labels if changed
- [ ] Cannot edit completed orders

### Editable Fields

| Field | Type | Description |
|-------|------|-------------|
| team | select | Reassign team |
| jml_rim | number | Change total rims |

---

## Story 5B.5: Delete MMEA Order

**As an** admin  
**I want** to delete MMEA orders  
**So that** I can remove incorrect entries

**Acceptance Criteria:**
- [ ] Confirmation with SweetAlert
- [ ] Cascade delete labels
- [ ] Success notification

---

## Routes Summary

```php
// MMEA Orders
Route::prefix('mmea')->group(function () {
    Route::get('/register', [MmeaController::class, 'create'])->name('mmea.create');
    Route::post('/register', [MmeaController::class, 'store'])->name('mmea.store');
    Route::get('/orders', [MmeaController::class, 'index'])->name('mmea.index');
    Route::get('/orders/{order}', [MmeaController::class, 'show'])->name('mmea.show');
    Route::get('/orders/{order}/edit', [MmeaController::class, 'edit'])->name('mmea.edit');
    Route::patch('/orders/{order}', [MmeaController::class, 'update'])->name('mmea.update');
    Route::delete('/orders/{order}', [MmeaController::class, 'destroy'])->name('mmea.destroy');
});
```

---

## Definition of Done (Sprint 5B)

- [ ] Can register MMEA orders
- [ ] MMEA API integration working
- [ ] MMEA order list with search
- [ ] Can view MMEA order detail
- [ ] Can edit MMEA orders
- [ ] Can delete MMEA orders
- [ ] Labels generated without cut_side

---

## Sprint 5B Checklist

```
[ ] 5B.1 Register MMEA Order
    [ ] Registration form
    [ ] MMEA API fetch
    [ ] lbr_kemas display
    [ ] Generate labels (no cut_side)

[ ] 5B.2 MMEA Order List
    [ ] Index page
    [ ] Search
    [ ] Table with actions
    [ ] Pagination

[ ] 5B.3 View MMEA Detail
    [ ] Show page
    [ ] Order info
    [ ] Label list
    [ ] Progress

[ ] 5B.4 Edit MMEA Order
    [ ] Edit form
    [ ] Team reassignment
    [ ] Rim count change

[ ] 5B.5 Delete MMEA Order
    [ ] Delete route
    [ ] Confirmation
    [ ] Cascade delete
```

---

## MMEA Label Generation Logic

```php
// MMEA: 1 label per rim, no cut_side, no inschiet
public function generateMmeaLabels(ProductionOrder $order): void
{
    $labels = [];
    
    for ($rim = 1; $rim <= $order->total_rims; $rim++) {
        $labels[] = [
            'production_order_id' => $order->id,
            'rim_number' => $rim,
            'cut_side' => null,        // NO cut_side for MMEA
            'is_inschiet' => false,    // NO inschiet for MMEA
            'pack_sheets' => $order->pack_sheets,
            'workstation_id' => $order->team_id,
        ];
    }
    
    Label::insert($labels);
}
```

