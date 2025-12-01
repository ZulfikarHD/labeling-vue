# Sprint 7: Label Printing & Reprint

## Overview
Implement label printing for both Regular and MMEA orders, including reprint functionality.

---

## Story 7.1: Print Regular Label (Inspeksi)

**As an** operator  
**I want** to print a regular inspection label  
**So that** I can attach it to the production rim

**Acceptance Criteria:**
- [ ] Print after processing
- [ ] Show all label information
- [ ] Print-optimized layout
- [ ] Barcode with PO-RIM-SIDE format

### Regular Label Fields

| Field | Description |
|-------|-------------|
| Header | "LABEL PRODUKSI PERURI" |
| No PO | Production order number |
| No OBC | OBC reference |
| Type | Product type |
| Seri | Series |
| No Rim | Rim number (large display) |
| Potongan | KIRI or KANAN |
| Inschiet Badge | If rim 999 |
| Periksa 1 | Inspector 1 NP |
| Periksa 2 | Inspector 2 NP |
| Team | Workstation name |
| Waktu | Processing timestamp |
| Barcode | {PO}-{RIM}-{SIDE} |

### Print Specifications

| Property | Value |
|----------|-------|
| Size | 10cm × 7cm (adjustable) |
| Orientation | Portrait |
| Font | Arial/sans-serif |
| Border | 2px solid black |

---

## Story 7.2: Print MMEA Label

**As an** operator  
**I want** to print MMEA labels  
**So that** I can attach them to MMEA products

**Acceptance Criteria:**
- [ ] Different layout from regular
- [ ] Show lbr_kemas (pack sheets)
- [ ] No Kiri/Kanan
- [ ] Batch print multiple rims
- [ ] Print mode selection (Both/P1/P2)

### MMEA Label Fields

| Field | Description |
|-------|-------------|
| Header | "LABEL MMEA PERURI" |
| No PO | Production order number |
| No OBC | OBC reference |
| Type | Product type |
| Seri | Series |
| Lbr Kemas | Pack sheets |
| No Rim | Rim number |
| Periksa 1 | Inspector 1 NP |
| Periksa 2 | Inspector 2 NP |
| Waktu | Processing timestamp |
| Barcode | {PO}-{RIM}-M |

### Print Mode Options

| Mode | Description |
|------|-------------|
| Both | Print both inspector labels |
| Periksa 1 | Print only inspector 1 label |
| Periksa 2 | Print only inspector 2 label |

---

## Story 7.3: Batch Print Labels

**As an** operator  
**I want** to print multiple labels at once  
**So that** I can prepare all labels efficiently

**Acceptance Criteria:**
- [ ] Select rim range
- [ ] Print all selected in one job
- [ ] Page break between labels
- [ ] Correct order (inschiet first for regular)

### Batch Print Form Fields

| Field | Type | Description |
|-------|------|-------------|
| start_rim | number | Starting rim |
| end_rim | number | Ending rim |
| include_inschiet | checkbox | Include rim 999 (regular only) |
| Print Button | button | Print selected range |

---

## Story 7.4: Reprint Label

**As an** operator  
**I want** to reprint a specific label  
**So that** I can replace damaged labels

**Acceptance Criteria:**
- [ ] Search by PO number
- [ ] Select specific rim
- [ ] Select side (for regular)
- [ ] Show label preview
- [ ] Print button

### Reprint Search Form

| Field | Type | Description |
|-------|------|-------------|
| po_number | number | PO to search |
| rim_number | number | Specific rim |
| cut_side | select | Kiri/Kanan (regular only) |
| Search Button | button | Find label |

### Reprint Result Display

| Field | Description |
|-------|-------------|
| Found/Not Found | Search result status |
| Label Preview | Preview of found label |
| PO Number | Order number |
| Rim + Side | e.g., "Rim 3 Kiri" |
| Inspector | Who processed |
| Processed Date | When processed |
| Print Button | Reprint label |

---

## Story 7.5: Print Label from Order Detail

**As a** user  
**I want** to print labels from the order detail page  
**So that** I can easily access printing

**Acceptance Criteria:**
- [ ] Print single label button per row
- [ ] Print all labels button
- [ ] Only print processed labels

### Action Buttons

| Button | Location | Description |
|--------|----------|-------------|
| Print | Label row | Print single label |
| Print All | Page header | Print all processed labels |

---

## Routes Summary

```php
// Regular Label Printing
Route::get('/print/label/{label}', [PrintLabelController::class, 'show'])->name('print.label');
Route::get('/print/order/{order}', [PrintLabelController::class, 'printAll'])->name('print.order');

// MMEA Label Printing
Route::get('/print/mmea', [PrintLabelMmeaController::class, 'index'])->name('print.mmea');
Route::post('/print/mmea', [PrintLabelMmeaController::class, 'print'])->name('print.mmea.submit');

// Reprint
Route::get('/reprint', [ReprintController::class, 'index'])->name('reprint.index');
Route::post('/reprint/search', [ReprintController::class, 'search'])->name('reprint.search');
```

---

## Definition of Done (Sprint 7)

- [ ] Can print regular inspection labels
- [ ] Can print MMEA labels
- [ ] Can batch print multiple labels
- [ ] Can reprint specific labels
- [ ] Print from order detail working
- [ ] Print layout correct for both types

---

## Sprint 7 Checklist

```
[ ] 7.1 Print Regular Label
    [ ] Print view component
    [ ] Label layout
    [ ] Print CSS
    [ ] Barcode generation

[ ] 7.2 Print MMEA Label
    [ ] MMEA print view
    [ ] lbr_kemas display
    [ ] Print mode selection
    [ ] Different layout

[ ] 7.3 Batch Print
    [ ] Rim range selection
    [ ] Multiple labels
    [ ] Page breaks
    [ ] Correct order

[ ] 7.4 Reprint Label
    [ ] Search form
    [ ] Label lookup
    [ ] Preview display
    [ ] Print button

[ ] 7.5 Print from Order Detail
    [ ] Per-label print button
    [ ] Print all button
    [ ] Only processed labels
```

---

## Barcode Format

| Type | Format | Example |
|------|--------|---------|
| Regular Left | {PO}-{RIM}-L | 2024120100001-003-L |
| Regular Right | {PO}-{RIM}-R | 2024120100001-003-R |
| Inschiet Left | {PO}-999-L | 2024120100001-999-L |
| MMEA | {PO}-{RIM}-M | 2024120100001-003-M |
