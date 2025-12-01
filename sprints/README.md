# Sprint Backlog

## How to Use

1. Work through sprints **in order** (1 → 10)
2. Open the sprint file when starting
3. Complete all stories in a sprint before moving to next
4. Check off items as you complete them

---

## Sprint Overview

| Sprint | Focus | Est. Days |
|--------|-------|-----------|
| 01 | Foundation (DB, Models, Enums) | 3-4 |
| 02 | Authentication & User Management | 4-5 |
| 03 | External API (SIRINE) | 2-3 |
| 04 | Label Service (Core Logic) | 4-5 |
| 05 | Order Management (Regular) | 4-5 |
| 05B | MMEA Orders | 3-4 |
| 06 | Label Processing (Cetak Label) | 4-5 |
| 07 | Printing & Reprint | 3-4 |
| 08 | Monitoring & Dashboard | 3-4 |
| 09 | Reports & Export | 3-4 |
| 10 | Polish & Testing | 3-4 |
| **Total** | | **~40 days** |

---

## Quick Links

- [Sprint 1: Foundation](01-foundation.md) - Database, Models, Enums
- [Sprint 2: Authentication](02-authentication.md) - Login, Users, Profile, Workstations
- [Sprint 3: External API](03-external-api.md) - SIRINE API Integration
- [Sprint 4: Label Service](04-label-service.md) ⭐ **Core Business Logic**
- [Sprint 5: Order Management](05-order-management.md) - Order Besar, Order Kecil
- [Sprint 5B: MMEA Orders](05b-mmea-orders.md) - MMEA-specific workflow
- [Sprint 6: Label Processing](06-label-processing.md) - Cetak Label, Processing
- [Sprint 7: Printing](07-printing.md) - Print Labels, Reprint
- [Sprint 8: Monitoring](08-monitoring-dashboard.md) - Dashboard, Team Status, Employee Report
- [Sprint 9: Reports](09-reports-export.md) - Reports, Excel Export
- [Sprint 10: Polish](10-polish-testing.md) - Error Handling, Testing

---

## Feature Mapping (Old → New)

| Old Page/Feature | New Sprint |
|------------------|------------|
| Auth/Login | Sprint 2 |
| UserManagement/CreateUser | Sprint 2 |
| UserManagement/ChangePassword | Sprint 2 |
| Profile/Edit | Sprint 2 |
| OrderBesar/RegisterNomorPo | Sprint 5 |
| OrderBesar/PoSiapVerif | Sprint 5 |
| OrderBesar/CetakLabel | Sprint 6 |
| OrderKecil/CetakLabel | Sprint 5 |
| RegisteredPo/RegisteredPoMmea | Sprint 5B |
| PrintLabel/PrintLabelInspeksi | Sprint 7 |
| PrintLabel/PrintLabelMmea | Sprint 6, 7 |
| MonitoringProduksi/StatusVerifikasiTeam | Sprint 8 |
| MonitoringProduksi/PilihStatusVerifikasiTeam | Sprint 8 |
| MonitoringProduksi/ProduksiPegawai | Sprint 8 |
| EditProductionOrder | Sprint 5 |
| ProductionOrderList | Sprint 5 |

---

## Critical Business Rules

```
SHEETS_PER_RIM = 1000
INSCHIET_RIM = 999

Formulas:
- total_rims = max(floor(sheets / 1000), 1)
- inschiet = sheets % 1000

Regular Order Labels:
- 2 per rim (Kiri + Kanan)
- Inschiet: rim 999 with Kiri + Kanan

MMEA Order Labels:
- 1 per rim (no cut_side)
- No inschiet

Processing Priority:
1. Inschiet first (rim 999)
2. Kiri before Kanan
3. Ascending rim numbers
```

---

## Order Types

| Type | Input | Cut Side | Inschiet | Labels/Rim |
|------|-------|----------|----------|------------|
| Regular (Order Besar) | Total sheets | Kiri/Kanan | Yes (rim 999) | 2 |
| Regular (Order Kecil) | Total sheets | Kiri/Kanan | Yes (rim 999) | 2 |
| MMEA | Total rims | NULL | No | 1 |

---

## Starting a Sprint with Cursor

```
"Let's work on Sprint X. Open sprints/0X-name.md and let's 
start with Story X.1. Follow .cursorrules for conventions."
```

Example:
```
"Let's work on Sprint 5. Open sprints/05-order-management.md 
and let's start with Story 5.1: Register PO - Order Besar."
```
