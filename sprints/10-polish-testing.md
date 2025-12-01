# Sprint 10: Polish & Testing

## Overview
Final sprint for UI polish, error handling, and testing to ensure production readiness.

---

## Story 10.1: Error Handling

**As a** user  
**I want** clear error messages when something goes wrong  
**So that** I know what happened and what to do

**Acceptance Criteria:**
- [ ] API errors show user-friendly messages
- [ ] Form validation errors display properly
- [ ] 404 page for not found
- [ ] 403 page for unauthorized
- [ ] 500 page for server errors
- [ ] SIRINE API unavailable handled gracefully

### Error Pages to Create
| Page | When Shown |
|------|------------|
| 404 | Route/resource not found |
| 403 | Unauthorized access |
| 500 | Server error |
| 503 | Maintenance mode |

### Error Handling in Services

```php
// In SirineApiService
public function getSpecification(int $poNumber, OrderType $type): ?array
{
    try {
        $response = Http::timeout(10)->get($this->getEndpoint($poNumber, $type));
        
        if ($response->failed()) {
            Log::warning("SIRINE API failed for PO {$poNumber}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }
        
        return $response->json();
    } catch (ConnectionException $e) {
        Log::error("SIRINE API connection failed", ['error' => $e->getMessage()]);
        return null;
    } catch (\Exception $e) {
        Log::error("SIRINE API unexpected error", ['error' => $e->getMessage()]);
        return null;
    }
}
```

### Vue Error Handling

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

// Global error flash
const flash = computed(() => usePage().props.flash)
</script>

<template>
    <!-- Error toast -->
    <div v-if="flash.error" class="toast toast-error">
        {{ flash.error }}
    </div>
    
    <!-- Success toast -->
    <div v-if="flash.success" class="toast toast-success">
        {{ flash.success }}
    </div>
</template>
```

---

## Story 10.2: Loading States

**As a** user  
**I want** to see loading indicators  
**So that** I know the system is working

**Acceptance Criteria:**
- [ ] Button loading state when submitting
- [ ] Page loading indicator
- [ ] Table skeleton while loading
- [ ] Disable buttons during submission

### Implementation

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({...})

const submit = () => {
    form.post(route('orders.store'))
}
</script>

<template>
    <button 
        type="submit" 
        :disabled="form.processing"
        :class="{ 'opacity-50 cursor-wait': form.processing }"
    >
        <span v-if="form.processing">Processing...</span>
        <span v-else>Submit</span>
    </button>
</template>
```

---

## Story 10.3: Toast Notifications

**As a** user  
**I want** to see success/error notifications  
**So that** I get feedback on my actions

**Acceptance Criteria:**
- [ ] Success toast for successful actions
- [ ] Error toast for failed actions
- [ ] Info toast for information
- [ ] Auto-dismiss after 5 seconds
- [ ] Manual dismiss option

### Flash Messages in Laravel

```php
// In controller
return redirect()->route('orders.show', $order)
    ->with('success', 'Order created successfully');

return back()
    ->with('error', 'Failed to process label');

return back()
    ->with('info', 'All labels have been processed');
```

### Toast Component

```vue
<!-- Components/Toast.vue -->
<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const show = ref(false)
const message = ref('')
const type = ref('success')

const flash = computed(() => usePage().props.flash)

watch(flash, (newFlash) => {
    if (newFlash.success) {
        showToast(newFlash.success, 'success')
    } else if (newFlash.error) {
        showToast(newFlash.error, 'error')
    } else if (newFlash.info) {
        showToast(newFlash.info, 'info')
    }
}, { immediate: true })

const showToast = (msg, t) => {
    message.value = msg
    type.value = t
    show.value = true
    
    setTimeout(() => {
        show.value = false
    }, 5000)
}

const dismiss = () => {
    show.value = false
}
</script>
```

---

## Story 10.4: Responsive Design

**As a** user  
**I want** the app to work on different screen sizes  
**So that** I can use it on various devices

**Acceptance Criteria:**
- [ ] Desktop layout (1024px+)
- [ ] Tablet layout (768px - 1023px)
- [ ] Mobile layout (< 768px)
- [ ] Tables scroll horizontally on mobile
- [ ] Navigation collapses on mobile
- [ ] Forms stack vertically on mobile

### Key Breakpoints
| Breakpoint | Width | Layout |
|------------|-------|--------|
| sm | 640px | Mobile |
| md | 768px | Tablet |
| lg | 1024px | Desktop |
| xl | 1280px | Large desktop |

### Responsive Patterns

```vue
<!-- Responsive table wrapper -->
<div class="overflow-x-auto">
    <table class="min-w-full">...</table>
</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    ...
</div>

<!-- Responsive navigation -->
<nav class="hidden md:flex">Desktop nav</nav>
<nav class="md:hidden">Mobile nav</nav>
```

---

## Story 10.5: Form Validation UX

**As a** user  
**I want** clear validation feedback  
**So that** I can fix my input errors

**Acceptance Criteria:**
- [ ] Inline error messages below fields
- [ ] Error field highlighting (red border)
- [ ] Clear error on input change
- [ ] Submit button disabled if form invalid
- [ ] Server-side validation errors displayed

### Validation Display

```vue
<template>
    <div class="form-group">
        <label for="po_number">PO Number</label>
        <input 
            id="po_number"
            v-model="form.po_number"
            :class="{ 'border-red-500': form.errors.po_number }"
            class="input"
        />
        <p v-if="form.errors.po_number" class="text-red-500 text-sm mt-1">
            {{ form.errors.po_number }}
        </p>
    </div>
</template>
```

---

## Story 10.6: Unit Tests

**As a** developer  
**I want** unit tests for business logic  
**So that** I can ensure code correctness

**Acceptance Criteria:**
- [ ] LabelService tests
- [ ] Rim/inschiet calculation tests
- [ ] Label generation tests
- [ ] Processing priority tests
- [ ] Order status tests

### Test Cases for LabelService

```php
// tests/Unit/LabelServiceTest.php

class LabelServiceTest extends TestCase
{
    private LabelService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LabelService();
    }
    
    /** @test */
    public function calculates_rims_correctly_for_exact_multiple()
    {
        $result = $this->service->calculateRimsAndInschiet(5000);
        
        $this->assertEquals(5, $result['total_rims']);
        $this->assertEquals(0, $result['inschiet_sheets']);
        $this->assertFalse($result['has_inschiet']);
    }
    
    /** @test */
    public function calculates_rims_with_inschiet()
    {
        $result = $this->service->calculateRimsAndInschiet(3500);
        
        $this->assertEquals(3, $result['total_rims']);
        $this->assertEquals(500, $result['inschiet_sheets']);
        $this->assertTrue($result['has_inschiet']);
    }
    
    /** @test */
    public function minimum_one_rim_for_small_orders()
    {
        $result = $this->service->calculateRimsAndInschiet(800);
        
        $this->assertEquals(1, $result['total_rims']);
        $this->assertEquals(0, $result['inschiet_sheets']);
        $this->assertFalse($result['has_inschiet']);
    }
    
    /** @test */
    public function generates_correct_labels_for_regular_order()
    {
        $order = ProductionOrder::factory()->create([
            'order_type' => OrderType::Regular,
            'total_rims' => 3,
            'start_rim' => 1,
            'end_rim' => 3,
            'inschiet_sheets' => 500,
        ]);
        
        $this->service->generateForOrder($order);
        
        // 3 rims × 2 sides + 1 inschiet × 2 sides = 8 labels
        $this->assertEquals(8, $order->labels()->count());
        
        // Check inschiet labels exist
        $this->assertEquals(2, $order->labels()->where('is_inschiet', true)->count());
    }
    
    /** @test */
    public function generates_correct_labels_for_mmea_order()
    {
        $order = ProductionOrder::factory()->create([
            'order_type' => OrderType::Mmea,
            'total_rims' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'inschiet_sheets' => 0,
        ]);
        
        $this->service->generateForOrder($order);
        
        // 5 rims × 1 (no sides) = 5 labels
        $this->assertEquals(5, $order->labels()->count());
        
        // All labels should have null cut_side
        $this->assertEquals(5, $order->labels()->whereNull('cut_side')->count());
    }
    
    /** @test */
    public function gets_inschiet_label_first()
    {
        $order = ProductionOrder::factory()->create([
            'order_type' => OrderType::Regular,
            'total_rims' => 2,
            'inschiet_sheets' => 500,
        ]);
        
        $this->service->generateForOrder($order);
        
        $nextLabel = $this->service->getNextAvailable($order);
        
        $this->assertTrue($nextLabel->is_inschiet);
        $this->assertEquals(CutSide::Left, $nextLabel->cut_side);
    }
    
    /** @test */
    public function processes_left_before_right()
    {
        $order = ProductionOrder::factory()->create([
            'order_type' => OrderType::Regular,
            'total_rims' => 1,
            'inschiet_sheets' => 0,
        ]);
        
        $this->service->generateForOrder($order);
        
        $firstLabel = $this->service->getNextAvailable($order);
        $this->assertEquals(CutSide::Left, $firstLabel->cut_side);
        
        // Process first label
        $this->service->processLabel($firstLabel, 'A1234');
        
        $secondLabel = $this->service->getNextAvailable($order);
        $this->assertEquals(CutSide::Right, $secondLabel->cut_side);
    }
}
```

---

## Story 10.7: Feature Tests

**As a** developer  
**I want** feature tests for API endpoints  
**So that** I can ensure routes work correctly

**Acceptance Criteria:**
- [ ] Auth tests (login, logout)
- [ ] Order CRUD tests
- [ ] Label processing tests
- [ ] Admin access tests

### Test Cases

```php
// tests/Feature/OrderTest.php

class OrderTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function guest_cannot_access_orders()
    {
        $response = $this->get('/orders');
        $response->assertRedirect('/login');
    }
    
    /** @test */
    public function user_can_view_orders_list()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/orders');
        
        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Order/Index'));
    }
    
    /** @test */
    public function user_can_create_order()
    {
        $user = User::factory()->create();
        $workstation = Workstation::factory()->create();
        
        // Mock SIRINE API
        Http::fake([
            'sirine.peruri.co.id/*' => Http::response([
                'no_po' => 12345,
                'no_obc' => 'OBC-001',
                'type' => 'Pita Cukai',
            ]),
        ]);
        
        $response = $this->actingAs($user)->post('/orders', [
            'po_number' => 12345,
            'order_type' => 'regular',
            'total_sheets' => 3500,
            'team_id' => $workstation->id,
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('production_orders', [
            'po_number' => 12345,
            'total_rims' => 3,
            'inschiet_sheets' => 500,
        ]);
    }
    
    /** @test */
    public function only_admin_can_delete_orders()
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = ProductionOrder::factory()->create();
        
        // Operator cannot delete
        $response = $this->actingAs($operator)->delete("/orders/{$order->id}");
        $response->assertStatus(403);
        
        // Admin can delete
        $response = $this->actingAs($admin)->delete("/orders/{$order->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('production_orders', ['id' => $order->id]);
    }
}
```

---

## Story 10.8: Database Seeders

**As a** developer  
**I want** database seeders for testing  
**So that** I can quickly set up test data

**Acceptance Criteria:**
- [ ] Default admin user
- [ ] Sample workstations
- [ ] Sample orders (optional, for demo)

### Seeders

```php
// database/seeders/DatabaseSeeder.php

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WorkstationSeeder::class,
            UserSeeder::class,
        ]);
        
        // Optional: demo data
        if (app()->environment('local')) {
            $this->call([
                DemoOrderSeeder::class,
            ]);
        }
    }
}

// database/seeders/UserSeeder.php
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'np' => 'ADMIN',
            'name' => 'Administrator',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }
}

// database/seeders/WorkstationSeeder.php
class WorkstationSeeder extends Seeder
{
    public function run(): void
    {
        $teams = ['Team 1', 'Team 2', 'Team 3', 'Team 4'];
        
        foreach ($teams as $team) {
            Workstation::create([
                'name' => $team,
                'is_active' => true,
            ]);
        }
    }
}
```

---

## Routes Summary (Complete)

```php
// routes/web.php

// Public
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Orders
    Route::resource('orders', ProductionOrderController::class)
        ->except(['edit', 'update']);
    Route::post('/orders/complete', [ProductionOrderController::class, 'storeAndComplete'])
        ->name('orders.store-complete');
    
    // Labels
    Route::get('/orders/{order}/process', [LabelController::class, 'showProcessing'])
        ->name('orders.process');
    Route::post('/orders/{order}/labels/process-next', [LabelController::class, 'processNext'])
        ->name('orders.labels.process-next');
    Route::post('/labels/{label}/process', [LabelController::class, 'process'])
        ->name('labels.process');
    Route::post('/labels/{label}/finish', [LabelController::class, 'finish'])
        ->name('labels.finish');
    
    // Printing
    Route::get('/labels/{label}/print', [LabelPrintController::class, 'show'])
        ->name('labels.print');
    Route::get('/orders/{order}/labels/print', [LabelPrintController::class, 'printAll'])
        ->name('orders.labels.print');
    Route::get('/labels/reprint', [LabelPrintController::class, 'reprintSearch'])
        ->name('labels.reprint');
    Route::post('/labels/reprint/find', [LabelPrintController::class, 'reprintFind'])
        ->name('labels.reprint.find');
    
    // Monitoring
    Route::get('/monitoring/teams', [MonitoringController::class, 'teams'])
        ->name('monitoring.teams');
    Route::get('/monitoring/performance', [MonitoringController::class, 'performance'])
        ->name('monitoring.performance');
    
    // Admin only
    Route::middleware('admin')->group(function () {
        // Users
        Route::resource('admin/users', UserController::class)
            ->names('admin.users');
        Route::patch('/admin/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('admin.users.toggle');
        
        // Workstations
        Route::resource('admin/workstations', WorkstationController::class)
            ->names('admin.workstations')
            ->except(['show']);
        Route::patch('/admin/workstations/{workstation}/toggle-active', [WorkstationController::class, 'toggleActive'])
            ->name('admin.workstations.toggle');
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/production', [ReportController::class, 'production'])
                ->name('reports.production');
            Route::get('/production/export', [ReportController::class, 'exportProduction'])
                ->name('reports.production.export');
            Route::get('/label-history', [ReportController::class, 'labelHistory'])
                ->name('reports.label-history');
            Route::get('/daily-summary', [ReportController::class, 'dailySummary'])
                ->name('reports.daily-summary');
        });
    });
});
```

---

## Definition of Done (Sprint 10)

- [ ] Error pages created
- [ ] Loading states implemented
- [ ] Toast notifications working
- [ ] Responsive on mobile/tablet
- [ ] Form validation UX improved
- [ ] Unit tests passing
- [ ] Feature tests passing
- [ ] Seeders working

---

## Sprint 10 Checklist

```
[ ] 10.1 Error Handling
    [ ] 404 page
    [ ] 403 page
    [ ] 500 page
    [ ] API error handling
    [ ] User-friendly messages

[ ] 10.2 Loading States
    [ ] Button loading
    [ ] Page transitions
    [ ] Table skeletons

[ ] 10.3 Toast Notifications
    [ ] Toast component
    [ ] Flash message handling
    [ ] Auto-dismiss
    [ ] Manual dismiss

[ ] 10.4 Responsive Design
    [ ] Mobile layout
    [ ] Tablet layout
    [ ] Responsive tables
    [ ] Mobile navigation

[ ] 10.5 Form Validation UX
    [ ] Inline errors
    [ ] Error highlighting
    [ ] Clear on change

[ ] 10.6 Unit Tests
    [ ] LabelService tests
    [ ] Calculation tests
    [ ] Priority tests

[ ] 10.7 Feature Tests
    [ ] Auth tests
    [ ] Order tests
    [ ] Label tests
    [ ] Admin tests

[ ] 10.8 Database Seeders
    [ ] UserSeeder
    [ ] WorkstationSeeder
    [ ] DemoOrderSeeder (optional)
```

---

## Final Deployment Checklist

```
[ ] All tests passing
[ ] .env.production configured
[ ] Database migrated
[ ] Seeders run (admin user)
[ ] SIRINE API URL configured
[ ] SSL certificate (if needed)
[ ] Error logging configured
[ ] Backup strategy in place
[ ] Performance tested
[ ] User acceptance testing done
```

