# Sprint 4: Label Service (Core Business Logic)

## Overview
Implement the core label generation and processing logic. This is the **heart of the application**.

---

## ⚠️ CRITICAL BUSINESS RULES

### Constants
```php
const SHEETS_PER_RIM = 1000;    // 1 rim = 1000 sheets
const INSCHIET_RIM = 999;       // Special rim number for remainder
```

### Formulas
```php
// Calculate total rims (minimum 1)
$totalRims = max(floor($totalSheets / SHEETS_PER_RIM), 1);

// Calculate inschiet (remainder sheets)
$inschiet = $totalSheets % SHEETS_PER_RIM;

// Has inschiet?
$hasInschiet = $inschiet > 0;
```

### Label Generation Rules

**Regular Orders:**
- Each rim = 2 labels (LEFT + RIGHT cut)
- If inschiet > 0: create rim 999 with LEFT + RIGHT
- Total labels = (totalRims × 2) + (hasInschiet ? 2 : 0)

**MMEA Orders:**
- Each rim = 1 label (NO cut_side)
- NO inschiet handling
- cut_side = NULL
- Total labels = totalRims

### Processing Priority
```
1. INSCHIET FIRST (rim 999)
   - Left before Right
2. THEN REGULAR RIMS (ascending)
   - Rim 1 Left → Rim 1 Right
   - Rim 2 Left → Rim 2 Right
   - ...
```

**Why inschiet first?**
- Smaller quantity = faster to complete
- Reduces Work In Progress (WIP)
- Business requirement

---

## Story 4.1: LabelService Class

**As a** developer  
**I want** a service class for all label operations  
**So that** business logic is centralized and testable

**Acceptance Criteria:**
- [ ] `LabelService` class created in `app/Services/`
- [ ] All label logic in this service
- [ ] Controllers stay thin
- [ ] Service is injectable

### Service Structure

```php
// app/Services/LabelService.php

class LabelService
{
    private const SHEETS_PER_RIM = 1000;
    private const INSCHIET_RIM = 999;
    
    /**
     * Calculate rims and inschiet from total sheets
     */
    public function calculateRimsAndInschiet(int $totalSheets): array
    
    /**
     * Generate all labels for a production order
     */
    public function generateForOrder(ProductionOrder $order): void
    
    /**
     * Get next available label for processing
     */
    public function getNextAvailable(ProductionOrder $order): ?Label
    
    /**
     * Assign inspector to label and start processing
     */
    public function processLabel(Label $label, string $inspectorNp, ?string $inspector2Np = null): void
    
    /**
     * Finish all open sessions for a user
     */
    public function finishUserSessions(string $np): void
    
    /**
     * Check if order is completed (all labels processed)
     */
    public function isOrderCompleted(ProductionOrder $order): bool
    
    /**
     * Update order status based on label progress
     */
    public function updateOrderStatus(ProductionOrder $order): void
    
    /**
     * Get order progress statistics
     */
    public function getOrderProgress(ProductionOrder $order): array
}
```

---

## Story 4.2: Calculate Rims and Inschiet

**As a** system  
**I want** to calculate rims and inschiet from total sheets  
**So that** I know how many labels to generate

**Acceptance Criteria:**
- [ ] Returns total rims (minimum 1)
- [ ] Returns inschiet sheets
- [ ] Returns whether has inschiet

### Implementation

```php
public function calculateRimsAndInschiet(int $totalSheets): array
{
    $totalRims = max(floor($totalSheets / self::SHEETS_PER_RIM), 1);
    $inschiet = $totalSheets % self::SHEETS_PER_RIM;
    
    return [
        'total_rims' => $totalRims,
        'inschiet_sheets' => $inschiet,
        'has_inschiet' => $inschiet > 0,
    ];
}
```

### Test Cases

| Total Sheets | Total Rims | Inschiet | Has Inschiet |
|--------------|------------|----------|--------------|
| 5000 | 5 | 0 | false |
| 5500 | 5 | 500 | true |
| 3250 | 3 | 250 | true |
| 800 | 1 | 0 | false* |
| 1200 | 1 | 200 | true |
| 1000 | 1 | 0 | false |
| 2000 | 2 | 0 | false |

*Note: 800 sheets = 1 rim minimum, 800 % 1000 = 800, but since total < 1000, we treat it as 1 full rim with no inschiet (business rule)

**Special Case:** If sheets < 1000, still 1 rim, no inschiet.

```php
// Handle special case
if ($totalSheets < self::SHEETS_PER_RIM) {
    return [
        'total_rims' => 1,
        'inschiet_sheets' => 0,
        'has_inschiet' => false,
    ];
}
```

---

## Story 4.3: Generate Labels for Order

**As a** system  
**I want** to generate all labels when order is created  
**So that** labels are ready for processing

**Acceptance Criteria:**
- [ ] Generate correct number of labels
- [ ] Regular orders: LEFT + RIGHT per rim
- [ ] MMEA orders: 1 label per rim, no cut_side
- [ ] Inschiet labels marked with is_inschiet = true
- [ ] Use database transaction
- [ ] Labels created with empty inspector (pending)

### Implementation

```php
public function generateForOrder(ProductionOrder $order): void
{
    DB::transaction(function () use ($order) {
        $labels = [];
        
        if ($order->order_type === OrderType::Regular) {
            $labels = $this->generateRegularLabels($order);
        } else {
            $labels = $this->generateMmeaLabels($order);
        }
        
        Label::insert($labels);
    });
}

private function generateRegularLabels(ProductionOrder $order): array
{
    $labels = [];
    
    // Generate regular rim labels
    for ($rim = $order->start_rim; $rim <= $order->end_rim; $rim++) {
        foreach ([CutSide::Left, CutSide::Right] as $cutSide) {
            $labels[] = [
                'production_order_id' => $order->id,
                'rim_number' => $rim,
                'cut_side' => $cutSide->value,
                'is_inschiet' => false,
                'workstation_id' => $order->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }
    
    // Generate inschiet labels if applicable
    if ($order->inschiet_sheets > 0) {
        foreach ([CutSide::Left, CutSide::Right] as $cutSide) {
            $labels[] = [
                'production_order_id' => $order->id,
                'rim_number' => self::INSCHIET_RIM, // 999
                'cut_side' => $cutSide->value,
                'is_inschiet' => true,
                'workstation_id' => $order->team_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }
    
    return $labels;
}

private function generateMmeaLabels(ProductionOrder $order): array
{
    $labels = [];
    
    for ($rim = $order->start_rim; $rim <= $order->end_rim; $rim++) {
        $labels[] = [
            'production_order_id' => $order->id,
            'rim_number' => $rim,
            'cut_side' => null, // MMEA has no cut side
            'is_inschiet' => false,
            'pack_sheets' => $order->pack_sheets ?? null,
            'workstation_id' => $order->team_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    return $labels;
}
```

### Example: Regular Order with 3500 sheets

```
Input: 3500 sheets
Calculated: 3 rims + 500 inschiet

Generated Labels:
├── Rim 1, Left,  is_inschiet: false
├── Rim 1, Right, is_inschiet: false
├── Rim 2, Left,  is_inschiet: false
├── Rim 2, Right, is_inschiet: false
├── Rim 3, Left,  is_inschiet: false
├── Rim 3, Right, is_inschiet: false
├── Rim 999, Left,  is_inschiet: true  ← Inschiet
└── Rim 999, Right, is_inschiet: true  ← Inschiet

Total: 8 labels
```

### Example: MMEA Order with 3 rims

```
Input: 3 rims (MMEA)

Generated Labels:
├── Rim 1, cut_side: null
├── Rim 2, cut_side: null
└── Rim 3, cut_side: null

Total: 3 labels
```

---

## Story 4.4: Get Next Available Label

**As a** system  
**I want** to get the next label to process following priority rules  
**So that** operators process labels in correct order

**Acceptance Criteria:**
- [ ] Returns inschiet labels first (rim 999)
- [ ] Within same rim: left before right
- [ ] Returns null if all processed
- [ ] Only returns labels without inspector

### Implementation

```php
public function getNextAvailable(ProductionOrder $order): ?Label
{
    // Priority 1: Check inschiet first
    $inschietLabel = $this->getNextInschietLabel($order);
    if ($inschietLabel) {
        return $inschietLabel;
    }
    
    // Priority 2: Regular rims ascending, left before right
    return Label::where('production_order_id', $order->id)
        ->whereNull('inspector_np')
        ->where('is_inschiet', false)
        ->orderBy('rim_number')
        ->orderByRaw("FIELD(cut_side, 'left', 'right')")
        ->first();
}

private function getNextInschietLabel(ProductionOrder $order): ?Label
{
    // Check left first, then right
    foreach ([CutSide::Left, CutSide::Right] as $cutSide) {
        $label = Label::where('production_order_id', $order->id)
            ->where('is_inschiet', true)
            ->where('cut_side', $cutSide->value)
            ->whereNull('inspector_np')
            ->first();
            
        if ($label) {
            return $label;
        }
    }
    
    return null;
}
```

### Processing Order Example

```
Order has labels:
- Rim 999 Left  (inschiet)  ← 1st
- Rim 999 Right (inschiet)  ← 2nd
- Rim 1 Left                ← 3rd
- Rim 1 Right               ← 4th
- Rim 2 Left                ← 5th
- Rim 2 Right               ← 6th
- Rim 3 Left                ← 7th
- Rim 3 Right               ← 8th (last)
```

---

## Story 4.5: Process Label

**As an** operator  
**I want** to assign myself to a label  
**So that** I can track my inspection work

**Acceptance Criteria:**
- [ ] Assigns inspector NP to label
- [ ] Sets started_at timestamp
- [ ] Finishes user's previous open sessions
- [ ] Updates order status
- [ ] NP converted to uppercase

### Implementation

```php
public function processLabel(Label $label, string $inspectorNp, ?string $inspector2Np = null): void
{
    DB::transaction(function () use ($label, $inspectorNp, $inspector2Np) {
        // Finish any open sessions for this user
        $this->finishUserSessions($inspectorNp);
        
        // Assign inspector to label
        $label->update([
            'inspector_np' => strtoupper($inspectorNp),
            'inspector_2_np' => $inspector2Np ? strtoupper($inspector2Np) : null,
            'started_at' => now(),
        ]);
        
        // Update order status
        $this->updateOrderStatus($label->order);
    });
}

public function finishUserSessions(string $np): void
{
    $np = strtoupper($np);
    
    Label::where('inspector_np', $np)
        ->whereNotNull('started_at')
        ->whereNull('finished_at')
        ->update(['finished_at' => now()]);
}
```

**Why finish previous sessions?**
- User can only work on 1 label at a time
- Prevents "orphan" sessions (started but never finished)
- Accurate time tracking

---

## Story 4.6: Order Status Management

**As a** system  
**I want** to track order status based on label progress  
**So that** users know order completion state

**Acceptance Criteria:**
- [ ] Status = registered: no labels processed
- [ ] Status = in_progress: some labels processed
- [ ] Status = completed: all labels processed

### Implementation

```php
public function isOrderCompleted(ProductionOrder $order): bool
{
    return Label::where('production_order_id', $order->id)
        ->whereNull('inspector_np')
        ->count() === 0;
}

public function updateOrderStatus(ProductionOrder $order): void
{
    $totalLabels = $order->labels()->count();
    $processedLabels = $order->labels()->whereNotNull('inspector_np')->count();
    
    $newStatus = match(true) {
        $processedLabels === 0 => OrderStatus::Registered,
        $processedLabels === $totalLabels => OrderStatus::Completed,
        default => OrderStatus::InProgress,
    };
    
    if ($order->status !== $newStatus) {
        $order->update(['status' => $newStatus]);
    }
}

public function getOrderProgress(ProductionOrder $order): array
{
    $total = $order->labels()->count();
    $processed = $order->labels()->whereNotNull('inspector_np')->count();
    $pending = $total - $processed;
    
    return [
        'total' => $total,
        'processed' => $processed,
        'pending' => $pending,
        'percentage' => $total > 0 ? round(($processed / $total) * 100, 1) : 0,
    ];
}
```

---

## Story 4.7: Batch Process (Small Orders)

**As an** operator  
**I want** to process all labels at once for small orders  
**So that** I can quickly complete small jobs

**Acceptance Criteria:**
- [ ] All labels assigned to same inspector
- [ ] All timestamps set at once
- [ ] Order marked as completed
- [ ] Single transaction

### Implementation

```php
public function processAllLabels(ProductionOrder $order, string $inspectorNp, ?string $inspector2Np = null): void
{
    DB::transaction(function () use ($order, $inspectorNp, $inspector2Np) {
        $this->finishUserSessions($inspectorNp);
        
        $order->labels()->update([
            'inspector_np' => strtoupper($inspectorNp),
            'inspector_2_np' => $inspector2Np ? strtoupper($inspector2Np) : null,
            'started_at' => now(),
            'finished_at' => now(),
        ]);
        
        $order->update(['status' => OrderStatus::Completed]);
    });
}
```

---

## Definition of Done (Sprint 4)

- [ ] LabelService class created
- [ ] All methods implemented
- [ ] Business rules followed correctly
- [ ] Unit tests pass
- [ ] Can generate labels for regular order
- [ ] Can generate labels for MMEA order
- [ ] Processing priority works correctly
- [ ] Order status updates automatically

---

## Sprint 4 Checklist

```
[ ] 4.1 LabelService Class
    [ ] Create service class
    [ ] Define constants
    [ ] Inject in controllers

[ ] 4.2 Calculate Rims
    [ ] calculateRimsAndInschiet method
    [ ] Handle edge cases
    [ ] Test with various inputs

[ ] 4.3 Generate Labels
    [ ] generateForOrder method
    [ ] generateRegularLabels method
    [ ] generateMmeaLabels method
    [ ] Database transaction

[ ] 4.4 Get Next Available
    [ ] getNextAvailable method
    [ ] Inschiet priority
    [ ] Left before right
    [ ] Return null when done

[ ] 4.5 Process Label
    [ ] processLabel method
    [ ] finishUserSessions method
    [ ] Uppercase NP
    [ ] Timestamps

[ ] 4.6 Order Status
    [ ] isOrderCompleted method
    [ ] updateOrderStatus method
    [ ] getOrderProgress method

[ ] 4.7 Batch Process
    [ ] processAllLabels method
    [ ] Single transaction
```

---

## Test Scenarios

### Scenario 1: Regular Order 5000 sheets
```
Input: 5000 sheets, regular
Expected:
- 5 rims × 2 = 10 labels
- No inschiet
- Processing: 1L, 1R, 2L, 2R, 3L, 3R, 4L, 4R, 5L, 5R
```

### Scenario 2: Regular Order 3500 sheets
```
Input: 3500 sheets, regular
Expected:
- 3 rims × 2 = 6 labels
- Inschiet 500 sheets = 2 labels (rim 999)
- Total: 8 labels
- Processing: 999L, 999R, 1L, 1R, 2L, 2R, 3L, 3R
```

### Scenario 3: MMEA Order 5 rims
```
Input: 5 rims, mmea
Expected:
- 5 labels (no cut_side)
- No inschiet
- Processing: 1, 2, 3, 4, 5
```

### Scenario 4: Small Order 800 sheets
```
Input: 800 sheets, regular
Expected:
- 1 rim × 2 = 2 labels
- No inschiet (special case)
- Processing: 1L, 1R
```

