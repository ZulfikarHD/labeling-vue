# Sprint 9: Reports & Export

## Overview
Implement reporting features with export capabilities for production data analysis.

---

## Story 9.1: Production Report

**As an** admin  
**I want** to generate production reports  
**So that** I can analyze production output

**Acceptance Criteria:**
- [ ] Filter by date range
- [ ] Filter by order type (regular/mmea)
- [ ] Filter by team
- [ ] Filter by status
- [ ] Show summary statistics
- [ ] Show detailed order list
- [ ] Export to Excel

### Filter Form
| Field | Type | Options |
|-------|------|---------|
| Start Date | date picker | required |
| End Date | date picker | required |
| Order Type | select | All, Regular, MMEA |
| Team | select | All, Team 1, Team 2... |
| Status | select | All, Registered, In Progress, Completed |

### Summary Display
- Total orders
- Total labels generated
- Total labels processed
- Completion rate (%)
- Breakdown by order type
- Breakdown by team

### Detail Table Columns
| Column | Description |
|--------|-------------|
| PO Number | Production order number |
| OBC Number | OBC reference |
| Order Type | Regular/MMEA |
| Team | Assigned team |
| Total Labels | Number of labels |
| Processed | Number processed |
| Progress % | Completion percentage |
| Status | Order status |
| Created Date | When order was created |
| Completed Date | When order was completed (if applicable) |

### Controller

```php
// app/Http/Controllers/ReportController.php

class ReportController extends Controller
{
    public function production(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'order_type' => 'nullable|string',
            'team_id' => 'nullable|exists:workstations,id',
            'status' => 'nullable|string',
        ]);
        
        $query = ProductionOrder::query()
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ])
            ->when($request->order_type, fn($q, $type) => $q->where('order_type', $type))
            ->when($request->team_id, fn($q, $team) => $q->where('team_id', $team))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->with('team')
            ->withCount(['labels', 'labels as processed_count' => fn($q) => 
                $q->whereNotNull('inspector_np')
            ]);
        
        $orders = $query->get();
        
        // Calculate summary
        $summary = [
            'total_orders' => $orders->count(),
            'total_labels' => $orders->sum('labels_count'),
            'processed_labels' => $orders->sum('processed_count'),
            'completion_rate' => $orders->sum('labels_count') > 0
                ? round(($orders->sum('processed_count') / $orders->sum('labels_count')) * 100, 1)
                : 0,
            'by_type' => $orders->groupBy('order_type')->map->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
            'by_team' => $orders->groupBy('team.name')->map->count(),
        ];
        
        return Inertia::render('Report/Production', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => $request->only(['start_date', 'end_date', 'order_type', 'team_id', 'status']),
            'workstations' => Workstation::active()->get(),
        ]);
    }
}
```

---

## Story 9.2: Export to Excel

**As an** admin  
**I want** to export reports to Excel  
**So that** I can analyze data offline or share with others

**Acceptance Criteria:**
- [ ] Export production report to Excel
- [ ] Export performance report to Excel
- [ ] Include all filtered data
- [ ] Proper column formatting
- [ ] Download as .xlsx file

### Implementation (using Laravel Excel)

```bash
composer require maatwebsite/excel
```

```php
// app/Exports/ProductionReportExport.php

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductionReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;
    
    public function __construct($orders)
    {
        $this->orders = $orders;
    }
    
    public function collection()
    {
        return $this->orders;
    }
    
    public function headings(): array
    {
        return [
            'PO Number',
            'OBC Number',
            'Order Type',
            'Product Type',
            'Team',
            'Total Labels',
            'Processed',
            'Progress %',
            'Status',
            'Created Date',
        ];
    }
    
    public function map($order): array
    {
        return [
            $order->po_number,
            $order->obc_number,
            $order->order_type->value,
            $order->product_type,
            $order->team?->name ?? '-',
            $order->labels_count,
            $order->processed_count,
            $order->labels_count > 0 
                ? round(($order->processed_count / $order->labels_count) * 100, 1) . '%'
                : '0%',
            $order->status->value,
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}
```

```php
// In ReportController
public function exportProduction(Request $request)
{
    // Same query as production report
    $orders = $this->getFilteredOrders($request);
    
    $filename = 'production-report-' . now()->format('Y-m-d') . '.xlsx';
    
    return Excel::download(new ProductionReportExport($orders), $filename);
}
```

---

## Story 9.3: Label History Report

**As an** admin  
**I want** to see detailed label processing history  
**So that** I can track individual label activities

**Acceptance Criteria:**
- [ ] Filter by date range
- [ ] Filter by PO number
- [ ] Filter by inspector
- [ ] Filter by team
- [ ] Show processing times
- [ ] Export capability

### Filter Form
| Field | Type | Options |
|-------|------|---------|
| Start Date | date picker | required |
| End Date | date picker | required |
| PO Number | text input | optional, search |
| Inspector NP | text input | optional |
| Team | select | All, Team 1, Team 2... |

### Detail Table Columns
| Column | Description |
|--------|-------------|
| PO Number | Production order number |
| Rim | Rim number |
| Side | Left/Right (or - for MMEA) |
| Inschiet | Yes/No indicator |
| Inspector 1 | Primary inspector NP |
| Inspector 2 | Secondary inspector NP |
| Started At | Start timestamp |
| Finished At | Finish timestamp |
| Duration | Processing time |
| Team | Workstation name |

### Controller

```php
public function labelHistory(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'po_number' => 'nullable|integer',
        'inspector_np' => 'nullable|string|max:5',
        'team_id' => 'nullable|exists:workstations,id',
    ]);
    
    $labels = Label::query()
        ->whereNotNull('inspector_np')
        ->whereBetween('started_at', [
            Carbon::parse($request->start_date)->startOfDay(),
            Carbon::parse($request->end_date)->endOfDay(),
        ])
        ->when($request->po_number, function ($q, $po) {
            $q->whereHas('order', fn($q) => $q->where('po_number', $po));
        })
        ->when($request->inspector_np, fn($q, $np) => 
            $q->where('inspector_np', strtoupper($np))
        )
        ->when($request->team_id, fn($q, $team) => 
            $q->where('workstation_id', $team)
        )
        ->with(['order', 'workstation'])
        ->orderBy('started_at', 'desc')
        ->paginate(50);
    
    return Inertia::render('Report/LabelHistory', [
        'labels' => $labels,
        'filters' => $request->only(['start_date', 'end_date', 'po_number', 'inspector_np', 'team_id']),
        'workstations' => Workstation::active()->get(),
    ]);
}
```

---

## Story 9.4: Daily Summary Report

**As an** admin  
**I want** to see daily production summaries  
**So that** I can track daily output trends

**Acceptance Criteria:**
- [ ] Show summary per day
- [ ] Date range selection
- [ ] Orders created per day
- [ ] Labels processed per day
- [ ] Chart visualization (optional)

### Filter Form
| Field | Type | Options |
|-------|------|---------|
| Start Date | date picker | required |
| End Date | date picker | required |
| Team | select | All, Team 1, Team 2... |

### Summary Table Columns
| Column | Description |
|--------|-------------|
| Date | The date |
| Orders Created | Number of new orders |
| Orders Completed | Number completed |
| Labels Generated | New labels created |
| Labels Processed | Labels with inspector assigned |
| Avg Processing Time | Average time per label |

### Controller

```php
public function dailySummary(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'team_id' => 'nullable|exists:workstations,id',
    ]);
    
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    
    $summary = collect();
    
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();
        
        $ordersQuery = ProductionOrder::whereBetween('created_at', [$dayStart, $dayEnd]);
        $labelsQuery = Label::whereBetween('started_at', [$dayStart, $dayEnd]);
        
        if ($request->team_id) {
            $ordersQuery->where('team_id', $request->team_id);
            $labelsQuery->where('workstation_id', $request->team_id);
        }
        
        $summary->push([
            'date' => $date->format('Y-m-d'),
            'date_display' => $date->format('d M Y'),
            'orders_created' => $ordersQuery->count(),
            'orders_completed' => $ordersQuery->where('status', OrderStatus::Completed)->count(),
            'labels_generated' => Label::whereBetween('created_at', [$dayStart, $dayEnd])
                ->when($request->team_id, fn($q) => $q->where('workstation_id', $request->team_id))
                ->count(),
            'labels_processed' => $labelsQuery->whereNotNull('inspector_np')->count(),
        ]);
    }
    
    return Inertia::render('Report/DailySummary', [
        'summary' => $summary,
        'filters' => $request->only(['start_date', 'end_date', 'team_id']),
        'workstations' => Workstation::active()->get(),
        'totals' => [
            'orders_created' => $summary->sum('orders_created'),
            'orders_completed' => $summary->sum('orders_completed'),
            'labels_generated' => $summary->sum('labels_generated'),
            'labels_processed' => $summary->sum('labels_processed'),
        ],
    ]);
}
```

---

## Routes Summary

```php
Route::middleware(['auth', 'admin'])->prefix('reports')->group(function () {
    // Production Report
    Route::get('/production', [ReportController::class, 'production'])
        ->name('reports.production');
    Route::get('/production/export', [ReportController::class, 'exportProduction'])
        ->name('reports.production.export');
    
    // Label History
    Route::get('/label-history', [ReportController::class, 'labelHistory'])
        ->name('reports.label-history');
    Route::get('/label-history/export', [ReportController::class, 'exportLabelHistory'])
        ->name('reports.label-history.export');
    
    // Daily Summary
    Route::get('/daily-summary', [ReportController::class, 'dailySummary'])
        ->name('reports.daily-summary');
    Route::get('/daily-summary/export', [ReportController::class, 'exportDailySummary'])
        ->name('reports.daily-summary.export');
    
    // Performance (from Sprint 8)
    Route::get('/performance/export', [ReportController::class, 'exportPerformance'])
        ->name('reports.performance.export');
});
```

---

## Definition of Done (Sprint 9)

- [ ] Production report with filters
- [ ] Label history report
- [ ] Daily summary report
- [ ] Export to Excel working
- [ ] All filters functional
- [ ] Pagination on large datasets

---

## Sprint 9 Checklist

```
[ ] 9.1 Production Report
    [ ] ReportController
    [ ] Filter form
    [ ] Summary statistics
    [ ] Detail table
    [ ] Production report page

[ ] 9.2 Export to Excel
    [ ] Install Laravel Excel
    [ ] ProductionReportExport class
    [ ] PerformanceReportExport class
    [ ] LabelHistoryExport class
    [ ] Download endpoints

[ ] 9.3 Label History Report
    [ ] labelHistory method
    [ ] Filter by PO/inspector/team
    [ ] Processing time calculation
    [ ] Label history page

[ ] 9.4 Daily Summary Report
    [ ] dailySummary method
    [ ] Date range iteration
    [ ] Daily totals
    [ ] Daily summary page
```

