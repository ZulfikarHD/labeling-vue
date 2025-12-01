# Sprint 6: Label Processing (Cetak Label)

## Overview
Implement the label processing workflow where operators process labels one by one (Order Besar) or view/reprint processed labels.

---

## Story 6.1: Cetak Label - Order Besar (Per Rim Processing)

**As an** operator  
**I want** to process labels one rim at a time  
**So that** I can track inspection progress per rim

**Acceptance Criteria:**
- [ ] Show order info and spec
- [ ] Show all labels with status
- [ ] Get next available label (priority: inschiet first)
- [ ] Enter inspector NP
- [ ] Process label (assign NP, set timestamp)
- [ ] Auto-finish previous session
- [ ] Print label after processing
- [ ] Update order status automatically

### Page Components

| Component | Description |
|-----------|-------------|
| OrderHeader | PO number, OBC, type, team |
| SpecCard | Specification from API |
| ProgressBar | X/Y labels completed |
| LabelTable | All labels with status |
| NextLabelCard | Shows next label to process |
| ProcessForm | Inspector NP input + Process button |
| LabelDetailModal | View processed label details |

### Order Header Fields

| Field | Description |
|-------|-------------|
| No PO | Production order number |
| No OBC | OBC reference |
| Type | Product type |
| Seri | Series |
| Team | Assigned workstation |

### Label Table Columns

| Column | Description |
|--------|-------------|
| Rim | Rim number (999 = inschiet) |
| Potongan | Kiri/Kanan |
| Periksa 1 | Inspector 1 NP or "-" |
| Periksa 2 | Inspector 2 NP or "-" |
| Start | Start timestamp |
| Finish | Finish timestamp |
| Status | Done/Pending indicator |
| Actions | Print, View Detail |

### Process Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| inspector_np | text | required, max:5 | Primary inspector NP |
| inspector_2_np | text | optional, max:5 | Secondary inspector NP |
| Process Button | button | - | Submit and print |

### Label Detail Modal Fields

| Field | Description |
|-------|-------------|
| No PO | PO number |
| No OBC | OBC number |
| Seri | Series |
| No Rim | Rim number |
| Potongan | Kiri/Kanan |
| Periksa 1 | Inspector 1 |
| Periksa 2 | Inspector 2 |
| Start Time | When started |
| Finish Time | When finished |
| Duration | Processing time |

---

## Story 6.2: Processing Priority Logic

**As a** system  
**I want** to determine the next label to process  
**So that** operators follow the correct order

**Acceptance Criteria:**
- [ ] Inschiet (rim 999) processed FIRST
- [ ] Within rim: Kiri before Kanan
- [ ] Regular rims in ascending order
- [ ] Skip already processed labels

### Processing Order

1. Rim 999 Kiri (if exists and not processed)
2. Rim 999 Kanan (if exists and not processed)
3. Rim 1 Kiri
4. Rim 1 Kanan
5. Rim 2 Kiri
6. Rim 2 Kanan
7. ... continue ascending

---

## Story 6.3: Auto-Finish Previous Session

**As a** system  
**I want** to finish user's previous open sessions  
**So that** time tracking is accurate

**Acceptance Criteria:**
- [ ] When user processes new label
- [ ] Find all labels where inspector = user AND finish = null
- [ ] Set finish = now() for all of them
- [ ] Then assign user to new label

---

## Story 6.4: Print Label After Processing

**As an** operator  
**I want** the label to print automatically after processing  
**So that** I can attach it to the rim immediately

**Acceptance Criteria:**
- [ ] After successful processing
- [ ] Open print dialog automatically
- [ ] Print label with all details
- [ ] Return to processing page

---

## Story 6.5: MMEA Label Processing

**As an** operator  
**I want** to process MMEA labels  
**So that** I can track MMEA production

**Acceptance Criteria:**
- [ ] Enter PO number to load order
- [ ] Show MMEA-specific fields (lbr_kemas)
- [ ] Enter inspector NP(s)
- [ ] Enter rim range or specific rims
- [ ] Process and print selected labels
- [ ] No Kiri/Kanan selection (single label per rim)

### MMEA Process Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| po | number | required | PO to process |
| periksa1 | text | required | Inspector 1 NP |
| periksa2 | text | optional | Inspector 2 NP |
| start_rim | number | required | Starting rim |
| end_rim | number | required | Ending rim |
| print_mode | select | required | Both/Periksa1/Periksa2 |

### MMEA Specific Display

| Field | Description |
|-------|-------------|
| No OBC | OBC reference |
| Lbr Kemas | Pack sheets |
| Seri | Series |
| Total Rim | Total rims in order |

---

## Story 6.6: Order Status Auto-Update

**As a** system  
**I want** to update order status based on label progress  
**So that** users know completion state

**Acceptance Criteria:**
- [ ] Registered → In Progress: when first label processed
- [ ] In Progress → Completed: when all labels processed
- [ ] Update immediately after each label

---

## Routes Summary

```php
// Order Besar - Cetak Label
Route::get('/order-besar/cetak/{order}', [CetakLabelController::class, 'show'])->name('order-besar.cetak');
Route::post('/order-besar/cetak/{order}/process', [CetakLabelController::class, 'process'])->name('order-besar.process');
Route::get('/order-besar/cetak/{order}/next', [CetakLabelController::class, 'getNext'])->name('order-besar.next');

// MMEA - Print Label
Route::get('/mmea/print', [PrintLabelMmeaController::class, 'index'])->name('mmea.print');
Route::post('/mmea/print', [PrintLabelMmeaController::class, 'store'])->name('mmea.print.store');
Route::get('/mmea/print/{po}', [PrintLabelMmeaController::class, 'show'])->name('mmea.print.show');
```

---

## Definition of Done (Sprint 6)

- [ ] Can process Order Besar labels per rim
- [ ] Processing priority works correctly
- [ ] Auto-finish previous sessions
- [ ] Print after processing
- [ ] Can process MMEA labels
- [ ] Order status updates automatically
- [ ] Label detail modal working

---

## Sprint 6 Checklist

```
[ ] 6.1 Cetak Label - Order Besar
    [ ] Processing page
    [ ] Order/spec display
    [ ] Label table
    [ ] Process form
    [ ] Label detail modal

[ ] 6.2 Processing Priority
    [ ] Inschiet first logic
    [ ] Kiri before Kanan
    [ ] Ascending rim order
    [ ] getNextAvailable method

[ ] 6.3 Auto-Finish Session
    [ ] Find open sessions
    [ ] Set finish timestamp
    [ ] Before new assignment

[ ] 6.4 Print After Processing
    [ ] Print dialog trigger
    [ ] Label print view
    [ ] Return to page

[ ] 6.5 MMEA Label Processing
    [ ] MMEA print page
    [ ] PO lookup
    [ ] Rim range selection
    [ ] Batch processing
    [ ] Print mode selection

[ ] 6.6 Order Status Update
    [ ] Status transition logic
    [ ] Auto-update on process
    [ ] UI feedback
```

---

## Business Rules Reminder

### Processing Priority (Regular)
```
1. Rim 999 Kiri  ← FIRST (inschiet)
2. Rim 999 Kanan
3. Rim 1 Kiri    ← Then regular ascending
4. Rim 1 Kanan
5. Rim 2 Kiri
6. Rim 2 Kanan
... continue
```

### MMEA Processing
```
- No Kiri/Kanan
- No inschiet
- Process by rim range
- 1 label per rim
```

### NP Format
```
- Always UPPERCASE
- Max 5 characters
- Convert on submit
```
