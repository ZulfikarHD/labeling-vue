# Sprint 8: Monitoring & Dashboard

## Overview
Implement monitoring features for tracking production status by team and by employee.

---

## Story 8.1: Dashboard / Home

**As a** user  
**I want** to see a dashboard when I login  
**So that** I can quickly access main features

**Acceptance Criteria:**
- [ ] Welcome message with user info
- [ ] Quick action buttons
- [ ] Today's summary (optional)
- [ ] Navigation to main features

### Page Components

| Component | Description |
|-----------|-------------|
| WelcomeCard | User greeting + role |
| QuickActions | Main action buttons |
| TodaySummary | Today's stats (optional) |

### Quick Action Buttons

| Button | Route | Role |
|--------|-------|------|
| Register PO | /order-besar/register | All |
| Order Kecil | /order-kecil/cetak | All |
| MMEA | /mmea/register | All |
| Monitoring | /monitoring | All |
| User Management | /users | Admin |

---

## Story 8.2: Team Verification Status (Status Verifikasi Team)

**As a** supervisor  
**I want** to see verification status per team  
**So that** I can monitor team progress

**Acceptance Criteria:**
- [ ] Select date to view
- [ ] Show all active teams for that date
- [ ] Show orders and progress per team
- [ ] Click team to see details

### Page Components

| Component | Description |
|-----------|-------------|
| DatePicker | Select date to view |
| TeamList | List of active teams |
| TeamCard | Team summary card |

### Team Card Fields

| Field | Description |
|-------|-------------|
| Team Name | Workstation name |
| Active Orders | Count of orders |
| Labels Progress | X/Y completed |
| Progress Bar | Visual progress |

---

## Story 8.3: Team Verification Detail (Pilih Status Verifikasi)

**As a** supervisor  
**I want** to see detailed verification for a specific team  
**So that** I can track individual orders

**Acceptance Criteria:**
- [ ] Show selected team info
- [ ] List all orders for team on selected date
- [ ] Show label progress per order
- [ ] Show inspector assignments

### Page Components

| Component | Description |
|-----------|-------------|
| TeamHeader | Team name + date |
| OrderTable | Orders for this team |
| ProgressColumn | Per-order progress |

### Order Table Columns

| Column | Description |
|--------|-------------|
| No PO | Production order |
| No OBC | OBC reference |
| Type | Regular/MMEA |
| Total Labels | Label count |
| Processed | Completed count |
| Progress | Progress bar |
| Status | Status badge |

---

## Story 8.4: Employee Production Report (Produksi Pegawai)

**As a** supervisor  
**I want** to see production by employee  
**So that** I can evaluate individual performance

**Acceptance Criteria:**
- [ ] Select date range
- [ ] Show labels processed per employee
- [ ] Show orders worked on
- [ ] Calculate average time (optional)
- [ ] Filter by team

### Filter Form Fields

| Field | Type | Description |
|-------|------|-------------|
| start_date | date | Start of range |
| end_date | date | End of range |
| team | select | Filter by team (optional) |

### Employee Table Columns

| Column | Description |
|--------|-------------|
| NP | Employee number |
| Name | Employee name |
| Team | Assigned team |
| Labels | Total labels processed |
| Orders | Distinct orders |
| Avg Time | Average processing time |

---

## Story 8.5: MMEA Verification Table

**As a** supervisor  
**I want** to see MMEA verification status  
**So that** I can track MMEA production separately

**Acceptance Criteria:**
- [ ] Separate table for MMEA
- [ ] Show MMEA-specific fields
- [ ] Filter by date
- [ ] Show progress

### MMEA Table Columns

| Column | Description |
|--------|-------------|
| No PO | PO number |
| No OBC | OBC reference |
| Lbr Kemas | Pack sheets |
| Total Rim | Rim count |
| Processed | Completed count |
| Status | Status badge |

---

## Story 8.6: Real-time Refresh

**As a** supervisor  
**I want** to refresh monitoring data  
**So that** I see current status

**Acceptance Criteria:**
- [ ] Refresh button on monitoring pages
- [ ] Auto-refresh option (optional)
- [ ] Loading indicator during refresh

---

## Routes Summary

```php
// Monitoring
Route::prefix('monitoring')->group(function () {
    Route::get('/', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/teams', [MonitoringController::class, 'teams'])->name('monitoring.teams');
    Route::get('/teams/{team}', [MonitoringController::class, 'teamDetail'])->name('monitoring.team.detail');
    Route::get('/employees', [MonitoringController::class, 'employees'])->name('monitoring.employees');
});

// API for dynamic data
Route::get('/api/active-teams', [MonitoringController::class, 'getActiveTeams']);
Route::get('/api/team-progress/{team}', [MonitoringController::class, 'getTeamProgress']);
```

---

## Definition of Done (Sprint 8)

- [ ] Dashboard with quick actions
- [ ] Team verification status page
- [ ] Team detail view
- [ ] Employee production report
- [ ] MMEA verification table
- [ ] Refresh functionality

---

## Sprint 8 Checklist

```
[ ] 8.1 Dashboard
    [ ] Welcome card
    [ ] Quick action buttons
    [ ] Navigation working

[ ] 8.2 Team Verification Status
    [ ] Date picker
    [ ] Team list
    [ ] Progress display
    [ ] Active teams filter

[ ] 8.3 Team Verification Detail
    [ ] Team header
    [ ] Order table
    [ ] Progress per order
    [ ] Inspector info

[ ] 8.4 Employee Production
    [ ] Date range filter
    [ ] Employee table
    [ ] Labels count
    [ ] Orders count
    [ ] Team filter

[ ] 8.5 MMEA Verification
    [ ] Separate table
    [ ] MMEA fields
    [ ] Progress tracking

[ ] 8.6 Refresh
    [ ] Refresh button
    [ ] Loading state
    [ ] Data reload
```
