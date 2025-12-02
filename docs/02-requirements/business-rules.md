# Business Rules - Label Generator System

Dokumen ini merupakan kompilasi critical business rules untuk Label Generator System yang bertujuan untuk mendokumentasikan logic requirements, formulas, dan constraints yang harus diikuti dalam implementasi, yaitu: label generation rules, processing priority, dan data validation rules yang menjadi core business logic aplikasi.

## Constants

Konstanta yang digunakan dalam kalkulasi label, antara lain:

```php
SHEETS_PER_RIM = 1000    // 1 rim = 1000 lembar (sheets)
INSCHIET_RIM = 999       // Special rim number untuk remainder sheets
```

## Formulas

### Calculate Total Rims

Formula untuk menghitung total rims dari total sheets dengan minimum 1 rim:

```php
$totalRims = max(floor($totalSheets / SHEETS_PER_RIM), 1);
```

**Examples:**

| Total Sheets | Calculation | Total Rims |
|--------------|-------------|------------|
| 5000 | floor(5000/1000) | 5 |
| 3500 | floor(3500/1000) | 3 |
| 800 | max(floor(800/1000), 1) | 1 |
| 1200 | floor(1200/1000) | 1 |

### Calculate Inschiet (Remainder Sheets)

Formula untuk menghitung sisa sheets yang tidak cukup membentuk 1 rim penuh:

```php
$inschiet = $totalSheets % SHEETS_PER_RIM;
```

**Special Case:**
- Jika `totalSheets < 1000`, maka `inschiet = 0` (dianggap 1 rim penuh)
- Inschiet hanya berlaku untuk `totalSheets >= 1000`

**Examples:**

| Total Sheets | Calculation | Inschiet | Has Inschiet |
|--------------|-------------|----------|--------------|
| 5000 | 5000 % 1000 | 0 | false |
| 3500 | 3500 % 1000 | 500 | true |
| 1200 | 1200 % 1000 | 200 | true |
| 800 | Special case | 0 | false |

## Order Types

### Regular Order (Order Besar & Order Kecil)

Regular order merupakan production order untuk produk standar yang bertujuan untuk generate labels dengan left dan right cut sides, yaitu: 2 labels per rim dengan support untuk inschiet handling.

**Characteristics:**
- Input: Total sheets (lembar)
- 2 labels per rim: **LEFT** dan **RIGHT** cut side
- Inschiet handling: **YA** (rim 999 jika ada remainder)
- Total labels = `(totalRims × 2) + (hasInschiet ? 2 : 0)`

**Example: 3500 sheets**

```
Input: 3500 sheets
Calculated:
- Total rims: 3 (floor(3500/1000))
- Inschiet: 500 sheets (3500 % 1000)

Generated Labels:
├── Rim 1, Cut Side: LEFT
├── Rim 1, Cut Side: RIGHT
├── Rim 2, Cut Side: LEFT
├── Rim 2, Cut Side: RIGHT
├── Rim 3, Cut Side: LEFT
├── Rim 3, Cut Side: RIGHT
├── Rim 999, Cut Side: LEFT   (inschiet)
└── Rim 999, Cut Side: RIGHT  (inschiet)

Total: 8 labels
```

### MMEA Order

MMEA order merupakan special production order untuk MMEA products yang bertujuan untuk generate labels tanpa cut side specification, yaitu: 1 label per rim dengan input total rims directly.

**Characteristics:**
- Input: Total rims (bukan sheets)
- 1 label per rim: **NO** cut side (cut_side = NULL)
- Inschiet handling: **TIDAK**
- Pack sheets: Lembar per kemasan (input manual)
- Total labels = `totalRims`

**Example: 5 rims**

```
Input: 5 rims
pack_sheets: 1000 (per kemasan)

Generated Labels:
├── Rim 1, Cut Side: NULL, Pack Sheets: 1000
├── Rim 2, Cut Side: NULL, Pack Sheets: 1000
├── Rim 3, Cut Side: NULL, Pack Sheets: 1000
├── Rim 4, Cut Side: NULL, Pack Sheets: 1000
└── Rim 5, Cut Side: NULL, Pack Sheets: 1000

Total: 5 labels
```

## Label Processing Priority

Processing priority merupakan urutan pengerjaan labels yang bertujuan untuk optimize WIP (Work In Progress) dan memastikan inschiet diselesaikan terlebih dahulu, yaitu: inschiet first, then ascending rim numbers dengan left before right untuk regular orders.

### Priority Rules

1. **INSCHIET FIRST** (Rim 999)
   - Rim 999 LEFT → Rim 999 RIGHT
   - Reason: Jumlah kecil, cepat selesai, reduce WIP

2. **THEN REGULAR RIMS** (Ascending order)
   - Rim 1 LEFT → Rim 1 RIGHT
   - Rim 2 LEFT → Rim 2 RIGHT
   - Rim 3 LEFT → Rim 3 RIGHT
   - ...

3. **MMEA ORDERS** (No cut side)
   - Rim 1 → Rim 2 → Rim 3 → ...
   - Ascending rim numbers only

### Example Processing Sequence

**Regular Order: 2500 sheets**

```
Rims: 2 full rims + 500 inschiet

Processing Order:
1. Rim 999, LEFT   (inschiet)
2. Rim 999, RIGHT  (inschiet)
3. Rim 1, LEFT
4. Rim 1, RIGHT
5. Rim 2, LEFT
6. Rim 2, RIGHT
```

## Order Status Management

Order status merupakan state tracking untuk production order yang bertujuan untuk monitoring progress completion, yaitu: registered, in_progress, dan completed berdasarkan label processing status.

### Status Definitions

| Status | Condition | Description |
|--------|-----------|-------------|
| `registered` | All labels belum diproses | PO baru terdaftar, siap diverifikasi |
| `in_progress` | Sebagian labels sudah diproses | Sedang dalam proses inspection |
| `completed` | Semua labels sudah diproses | PO selesai, siap reprint jika perlu |

### Status Transition Logic

```php
$totalLabels = $order->labels()->count();
$processedLabels = $order->labels()->whereNotNull('inspector_np')->count();

$status = match(true) {
    $processedLabels === 0 => OrderStatus::Registered,
    $processedLabels === $totalLabels => OrderStatus::Completed,
    default => OrderStatus::InProgress,
};
```

## Label Assignment Rules

### Inspector Assignment

Inspector assignment merupakan proses assign operator ke label untuk tracking inspection work, yaitu: primary inspector (wajib) dan secondary inspector (optional) dengan automatic session management.

**Rules:**
- **inspector_np**: Primary inspector (REQUIRED)
- **inspector_2_np**: Secondary inspector (OPTIONAL)
- NP format: 5 digit uppercase (e.g., "12345")
- NP auto-converted to uppercase saat save
- Operator hanya bisa work on **1 label at a time**
- Assign label baru akan **auto-finish** previous open sessions

### Session Management

```php
// When assigning new label to operator
1. Finish all open sessions for this operator (set finished_at)
2. Assign operator to new label
3. Set started_at timestamp
4. Update order status

// Prevent orphan sessions
Label::where('inspector_np', $np)
    ->whereNotNull('started_at')
    ->whereNull('finished_at')
    ->update(['finished_at' => now()]);
```

## Validation Rules

### Production Order Validation

Validation rules untuk production order input dengan strict constraints:

**Regular Order:**
- `po_number`: REQUIRED, numeric, unique, positive integer
- `total_sheets`: REQUIRED, integer, minimum 1, maximum 999999
- `order_type`: REQUIRED, enum('regular', 'mmea')
- `product_type`: REQUIRED, string, max 50 chars
- `team_id`: OPTIONAL, exists in workstations table

**MMEA Order:**
- `po_number`: REQUIRED, numeric, unique
- `total_rims`: REQUIRED, integer, minimum 1
- `pack_sheets`: REQUIRED, integer, minimum 1
- `order_type`: 'mmea'
- `product_type`: REQUIRED
- `team_id`: OPTIONAL

### Label Processing Validation

Validation rules untuk label processing dengan inspector assignment:

- `inspector_np`: REQUIRED, string length 5, exists in users table
- `inspector_2_np`: OPTIONAL, string length 5, exists in users table
- Label must belong to valid production order
- Label must not be already processed (inspector_np = NULL)
- Workstation must be active

## SIRINE API Integration Rules

SIRINE API integration merupakan external validation service yang bertujuan untuk verify PO number dan fetch OBC data, yaitu: pre-validation sebelum registrasi PO dengan automatic data population.

### Validation Flow

1. **User input PO number** pada form
2. **Call SIRINE API** untuk verify PO exists
3. **If valid**: Auto-populate OBC number, product type
4. **If invalid**: Show error, prevent registration
5. **User confirm** dan submit form

### API Response Handling

```php
// Success response
{
    "success": true,
    "data": {
        "po_number": 123456789,
        "obc_number": "OBC/2024/001234",
        "product_type": "pita cukai",
        "customer": "PT Example"
    }
}

// Error response
{
    "success": false,
    "error": {
        "code": "PO_NOT_FOUND",
        "message": "Production Order tidak ditemukan"
    }
}
```

## Reprint Rules

Reprint rules merupakan business logic untuk reprint labels yang sudah pernah dicetak, yaitu: reprint individual label atau reprint all labels dengan maintain original inspector data.

**Characteristics:**
- Reprint mempertahankan inspector original (tidak berubah)
- Reprint dapat dilakukan untuk label dengan status completed
- Reprint tidak mengubah status order
- Reprint tracking dengan print count (future enhancement)

**Permission:**
- Admin: Dapat reprint semua labels
- Operator: Dapat reprint labels miliknya sendiri

## Performance Considerations

Performance optimization rules yang harus diperhatikan dalam implementation:

1. **Batch Label Generation**: Generate all labels dalam single transaction
2. **Eager Loading**: Load relationships untuk prevent N+1 queries
3. **Database Indexes**: Index pada columns yang frequently queried
4. **Query Optimization**: Use whereHas dengan proper joins
5. **Caching**: Cache workstation list, user list untuk reduce DB hits

## Data Integrity Rules

Data integrity constraints untuk maintain consistency:

1. **Foreign Key Constraints**: CASCADE delete untuk labels saat PO dihapus
2. **Unique Constraints**: (production_order_id, rim_number, cut_side)
3. **Soft Deletes**: Tidak digunakan (hard delete OK untuk cleanup)
4. **Timestamps**: Automatic created_at dan updated_at untuk audit trail
5. **Enum Values**: Strict enum validation untuk order_type, status, cut_side

## Security Rules

Security considerations untuk protect data integrity:

1. **Authentication**: Semua routes require authentication (except login)
2. **Authorization**: Role-based access (admin vs operator)
3. **Input Sanitization**: Validate dan sanitize all user inputs
4. **SQL Injection**: Use Eloquent query builder, never raw queries
5. **XSS Prevention**: Vue escapes output automatically
6. **CSRF Protection**: Laravel CSRF token untuk all POST requests

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0

