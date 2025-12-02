# Glossary - Label Generator System

Dokumen ini merupakan kumpulan definisi terms dan terminology yang digunakan dalam Label Generator System yang bertujuan untuk memastikan consistent understanding across all stakeholders, yaitu: business terms, technical terms, dan domain-specific abbreviations yang frequently digunakan dalam dokumentasi dan aplikasi.

---

## Business Terms

### Production Order (PO)
**Definisi**: Production Order merupakan work order untuk memproduksi sejumlah tertentu produk yang mencakup informasi PO number, product type, total sheets atau rims, dan team assignment untuk tracking production workflow.

**Contoh**: PO 123456789 untuk produksi 5000 sheets pita cukai

**Related Terms**: PO Number, Order Besar, Order Kecil, MMEA Order

---

### PO Number
**Definisi**: Unique identifier untuk production order yang berupa numeric value dengan format yang ditentukan oleh SIRINE system untuk identification dan tracking purposes.

**Format**: Numeric (e.g., 123456789)

**Validation**: Must exist in SIRINE API sebelum registration

---

### OBC Number
**Definisi**: OBC (Order Billing Confirmation) number merupakan reference number dari SIRINE system yang digunakan untuk linking production order dengan billing information untuk administrative dan financial tracking.

**Format**: String dengan prefix OBC (e.g., "OBC/2024/001234")

**Source**: Auto-populated dari SIRINE API saat PO verification

---

### Nomor Pegawai (NP)
**Definisi**: Employee number yang merupakan unique 5-digit identifier untuk setiap user dalam system yang digunakan sebagai username untuk authentication dan tracking inspection work.

**Format**: 5 characters alphanumeric (e.g., "12345", "A1234")

**Usage**: Login credential, inspector assignment, tracking

---

### Workstation
**Definisi**: Physical atau logical work location dimana operators melakukan inspection work yang bertujuan untuk grouping operators dan tracking production by location atau team.

**Contoh**: "Team 1", "Team 2", "WS-05"

**Related Terms**: Team, Location

---

## Product Terms

### Sheets (Lembar)
**Definisi**: Unit of measurement untuk production quantity yang merupakan individual piece dari product yang diproduksi, dimana 1000 sheets = 1 rim sebagai standard conversion.

**Unit**: Lembar

**Conversion**: 1000 sheets = 1 rim

---

### Rim
**Definisi**: Bundled unit dari sheets dimana 1 rim contains exactly 1000 sheets sebagai standard packaging unit untuk production tracking dan label generation calculation.

**Composition**: 1 rim = 1000 sheets

**Usage**: Label generation basis, packaging unit

---

### Inschiet
**Definisi**: Remainder sheets yang tidak cukup membentuk 1 full rim (< 1000 sheets) yang merupakan hasil dari modulo operation dalam rim calculation untuk handling partial rims dalam production order.

**Calculation**: `inschiet = total_sheets % 1000`

**Special Handling**: Labeled as rim 999, processed first dengan priority

**Contoh**: 
- 3500 sheets: 3 rims + 500 inschiet
- 5000 sheets: 5 rims + 0 inschiet (no inschiet)

---

### Cut Side
**Definisi**: Orientation dari cut position pada label untuk regular orders yang indicates left atau right side cutting dengan purpose untuk distinguishing two labels per rim dalam production process.

**Values**: 
- `LEFT` (Kiri): Left side cut position
- `RIGHT` (Kanan): Right side cut position
- `NULL`: No cut side (MMEA orders only)

**Usage**: Regular orders require both left dan right labels per rim

---

### Pack Sheets
**Definisi**: Quantity of sheets per package untuk MMEA orders yang indicates how many sheets bundled dalam satu kemasan untuk packaging dan distribution purposes.

**Applicability**: MMEA orders only

**Input**: Manual entry saat PO registration

**Contoh**: 1000 sheets per pack

---

## Order Types

### Order Besar
**Definisi**: Regular production order dengan full workflow yang mencakup registration, verification, dan per-label processing untuk large production volumes yang require detailed tracking dengan inspector assignment per label.

**Characteristics**:
- Input: Total sheets
- Labels per rim: 2 (Left + Right)
- Inschiet handling: Yes
- Workflow: Register → Verify → Process per label

**Use Case**: Large production orders requiring detailed tracking

---

### Order Kecil
**Definisi**: Small production order dengan simplified batch processing workflow yang bertujuan untuk fast completion tanpa per-label tracking detail untuk orders dengan small volume yang tidak require detailed inspection tracking.

**Characteristics**:
- Input: Total sheets
- Labels per rim: 2 (Left + Right)
- Inschiet handling: Yes
- Workflow: Direct input → Batch process all labels
- No verification step

**Use Case**: Rush orders, small volumes (< 2000 sheets)

---

### MMEA Order
**Definisi**: Special production order type untuk MMEA (Malaysian Medical Equipment Association) products yang memiliki special handling requirements dengan different label format dan no cut side specification untuk compliance dengan MMEA regulations.

**Characteristics**:
- Input: Total rims (not sheets)
- Labels per rim: 1 (no cut side)
- Cut side: NULL
- Inschiet handling: No
- Pack sheets: Required manual input

**Difference from Regular**: No cut side, input rims directly, no inschiet

---

## Label Terms

### Label
**Definisi**: Individual inspection record untuk satu unit production (rim + cut side) yang contains information about rim number, cut side, inspector assignment, dan processing timestamps untuk tracking inspection progress dan printing purposes.

**Components**:
- Rim number
- Cut side (for regular) atau NULL (for MMEA)
- Inspector NP (primary)
- Inspector 2 NP (secondary, optional)
- Started at timestamp
- Finished at timestamp

---

### Rim Number
**Definisi**: Sequential number yang identifies specific rim dalam production order mulai dari start_rim hingga end_rim dengan special number 999 reserved untuk inschiet labels.

**Range**: 1 to end_rim, plus 999 for inschiet

**Special Value**: 999 = Inschiet (remainder sheets)

**Example**: Order 5 rims: Rim 1, 2, 3, 4, 5 (plus 999 if inschiet)

---

### Inspector
**Definisi**: Operator yang assigned untuk inspect dan verify production quality pada specific label yang responsible untuk quality control dan tracked untuk performance monitoring purposes.

**Types**:
- **Primary Inspector** (`inspector_np`): Main inspector (required)
- **Secondary Inspector** (`inspector_2_np`): Assistant atau trainee (optional)

**Tracking**: NP, timestamp, workstation

---

### Processing Priority
**Definisi**: Predetermined order untuk processing labels yang bertujuan untuk optimize Work In Progress (WIP) dengan prioritizing inschiet first untuk fast completion kemudian regular rims dalam ascending order.

**Rules**:
1. **Inschiet first**: Rim 999 (both left dan right)
2. **Regular rims**: Ascending order (1, 2, 3, ...)
3. **Within same rim**: Left before right

**Reason**: Smaller quantities (inschiet) processed first untuk reduce WIP

---

## Status Terms

### Order Status

#### Registered
**Definisi**: Production order sudah terdaftar dalam system namun belum ada label yang diproses dengan state ready untuk verification dan processing.

**Condition**: `processedLabels = 0`

**Next Action**: Verification atau start processing

---

#### In Progress
**Definisi**: Production order sedang dalam proses inspection dimana sebagian labels sudah assigned ke inspectors namun order belum fully completed.

**Condition**: `0 < processedLabels < totalLabels`

**Next Action**: Continue processing remaining labels

---

#### Completed
**Definisi**: Production order sudah selesai diproses dimana semua labels sudah assigned dan finished dengan status ready untuk printing atau reprint.

**Condition**: `processedLabels = totalLabels`

**Next Action**: Print labels atau archive

---

### Label Status

#### Pending
**Definisi**: Label belum diproses dan belum assigned ke inspector dengan state available untuk next processing.

**Condition**: `inspector_np IS NULL`

**UI Display**: Available untuk assign

---

#### In Progress
**Definisi**: Label sudah assigned ke inspector dan inspection started namun belum finished.

**Condition**: `started_at IS NOT NULL AND finished_at IS NULL`

**UI Display**: Currently being processed by [Inspector NP]

---

#### Completed
**Definisi**: Label sudah fully processed dan finished oleh inspector dengan state ready untuk printing.

**Condition**: `finished_at IS NOT NULL`

**UI Display**: Completed by [Inspector NP] at [timestamp]

---

## Technical Terms

### Session
**Definisi**: Active inspection work period untuk operator pada specific label yang tracks start time, end time, dan inspector assignment untuk accurate time tracking dan preventing multiple concurrent sessions per operator.

**Lifecycle**:
1. **Start**: Inspector assigned, `started_at` set
2. **Active**: Inspection in progress
3. **Finish**: `finished_at` set (manual atau auto)
4. **Auto-finish**: Triggered when new label assigned

**Rule**: Operator dapat have only 1 active session at a time

---

### Reprint
**Definisi**: Action untuk print ulang labels yang sudah pernah dicetak sebelumnya yang maintain original inspector data dan timestamps untuk replacement atau duplicate purposes.

**Characteristics**:
- Original inspector data preserved
- Original timestamps preserved
- No status change pada order
- Permission-based access

**Use Case**: Lost labels, damaged labels, duplicate needs

---

### SIRINE API
**Definisi**: External API service yang provides PO validation dan OBC data retrieval functionality untuk ensuring data accuracy sebelum production order registration dalam system.

**Purpose**: 
- Verify PO number exists
- Fetch OBC number
- Fetch product type
- Validate customer data

**Integration**: Called during PO registration untuk auto-populate fields

---

### Wayfinder
**Definisi**: Laravel package untuk generating type-safe TypeScript functions dari Laravel routes yang provides IDE autocomplete dan type checking untuk Inertia.js navigation untuk improved developer experience dan reduced errors.

**Benefits**:
- Type safety untuk route parameters
- IDE autocomplete
- Compile-time error checking
- Automatic route sync

**Usage**: Import controller methods atau named routes dalam Vue components

---

## User Roles

### Admin
**Definisi**: User role dengan full system access yang responsible untuk user management, system configuration, dan oversight dengan elevated permissions untuk administrative tasks.

**Permissions**:
- Create, update, delete users
- Manage workstations
- View all orders dan labels
- Reprint any labels
- Access all reports
- System configuration

---

### Operator
**Definisi**: User role untuk production floor workers yang perform daily tasks including PO registration, label processing, dan printing dengan limited permissions focused pada operational tasks.

**Permissions**:
- Register PO (Order Besar, Order Kecil, MMEA)
- Process labels (assign self as inspector)
- Print labels
- Reprint own labels only
- View own statistics
- View team status

---

## Abbreviations

| Abbreviation | Full Form | Definition |
|--------------|-----------|------------|
| **PO** | Production Order | Work order untuk produksi |
| **OBC** | Order Billing Confirmation | Reference number dari SIRINE |
| **NP** | Nomor Pegawai | Employee number (5 digits) |
| **MMEA** | Malaysian Medical Equipment Association | Special order type |
| **WIP** | Work In Progress | Active ongoing work |
| **API** | Application Programming Interface | External service interface |
| **UI** | User Interface | Frontend application interface |
| **UX** | User Experience | User interaction experience |
| **CRUD** | Create, Read, Update, Delete | Basic operations |
| **FK** | Foreign Key | Database relationship |
| **PK** | Primary Key | Database unique identifier |

---

## Business Rules Quick Reference

```
CONSTANTS:
- SHEETS_PER_RIM = 1000
- INSCHIET_RIM = 999

FORMULAS:
- total_rims = max(floor(sheets / 1000), 1)
- inschiet = sheets % 1000
- has_inschiet = inschiet > 0

REGULAR ORDER:
- Labels per rim: 2 (Left + Right)
- Inschiet: Yes (rim 999)
- Total labels: (rims × 2) + (inschiet ? 2 : 0)

MMEA ORDER:
- Labels per rim: 1 (no cut side)
- Inschiet: No
- Total labels: rims

PROCESSING PRIORITY:
1. Inschiet (999L → 999R)
2. Regular rims ascending
3. Left before right
```

---

**Document Version**: 1.0  
**Last Updated**: 2025-12-02  
**Author**: Zulfikar Hidayatullah  
**Status**: Active

