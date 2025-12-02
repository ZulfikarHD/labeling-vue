# Sprint 3: External API Integration (SIRINE)

## Overview
Integrate with external SIRINE API to fetch product specifications. This app does NOT store specifications - they come from the external API.

---

## Business Context

**Why external API?**
- Specifications are managed in another system (SIRINE)
- Avoid data duplication
- Single source of truth for product specs
- This app only tracks label generation and inspection

**What we get from API:**
- Product details (type, series, etc.)
- OBC number
- Other specification fields for display

**What we store locally:**
- PO number (reference)
- OBC number (reference)
- Order type (regular/mmea)
- Label tracking data

---

## Story 3.1: SIRINE API Service

**As a** developer  
**I want** a service class to communicate with SIRINE API  
**So that** I can fetch product specifications

**Acceptance Criteria:**
- [ ] `SirineApiService` class created
- [ ] Can fetch regular order specs
- [ ] Can fetch MMEA order specs
- [ ] Handles API errors gracefully
- [ ] Handles SSL certificate issues
- [ ] Returns structured data or null

### API Endpoints

**Base URL:** `https://sirine.peruri.co.id/sirine/api`

| Type | Endpoint | Method |
|------|----------|--------|
| Regular (PCHT) | `/detail-order-pcht/{po_number}` | GET |
| MMEA | `/detail-order-mmea/{po_number}` | GET |

### Service Implementation

```php
// app/Services/SirineApiService.php

class SirineApiService
{
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = config('services.sirine.url', 'https://sirine.peruri.co.id/sirine/api');
    }
    
    /**
     * Get specification for regular (PCHT) order
     */
    public function getRegularSpec(int $poNumber): ?array
    {
        return $this->fetchSpec("/detail-order-pcht/{$poNumber}");
    }
    
    /**
     * Get specification for MMEA order
     */
    public function getMmeaSpec(int $poNumber): ?array
    {
        return $this->fetchSpec("/detail-order-mmea/{$poNumber}");
    }
    
    /**
     * Get specification by order type
     */
    public function getSpecification(int $poNumber, OrderType $type): ?array
    {
        return match($type) {
            OrderType::Regular => $this->getRegularSpec($poNumber),
            OrderType::Mmea => $this->getMmeaSpec($poNumber),
        };
    }
    
    /**
     * Validate PO exists in external system
     */
    public function validatePo(int $poNumber, OrderType $type): bool
    {
        $spec = $this->getSpecification($poNumber, $type);
        return $spec !== null;
    }
    
    private function fetchSpec(string $endpoint): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Handle SSL certificate issues
                'timeout' => 10,
            ])->get($this->baseUrl . $endpoint);
            
            if ($response->successful()) {
                $data = $response->json();
                // API might return array with data or error
                if (isset($data['error']) || empty($data)) {
                    return null;
                }
                return $data;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('SIRINE API Error: ' . $e->getMessage());
            return null;
        }
    }
}
```

### Configuration

```php
// config/services.php
'sirine' => [
    'url' => env('SIRINE_API_URL', 'https://sirine.peruri.co.id/sirine/api'),
],

// .env
SIRINE_API_URL=https://sirine.peruri.co.id/sirine/api
```

**Edge Cases:**
- API timeout → return null, show error to user
- API returns error → return null
- Invalid PO number → return null
- SSL certificate error → disable verification (internal network)

---

## Story 3.2: API Response Handling

**As a** developer  
**I want** to parse API responses into usable format  
**So that** I can display specs and use data consistently

**Acceptance Criteria:**
- [ ] Parse regular order response
- [ ] Parse MMEA order response
- [ ] Handle missing/null fields
- [ ] Return consistent structure

### Expected API Response (Regular/PCHT)

```json
{"no_po":3000277761,"no_obc":"PST410127","jenis":"P","tgl_obc":"2025-11-10","tgl_jt":"2025-11-28","tgl_bb":"2025-11-28","tgl_cetak":"2025-12-01","tgl_verif":"2025-12-01","tgl_kemas":"2025-12-01","jml_order":3300,"rencet":86,"jml_bb":0,"jml_cd":86,"total_cd":86,"jml_cetak":86,"hcs_verif":80,"hcts_verif":6,"hcs_sisa":0,"total_hcts":6,"kemas":80,"kirim":0,"status":"-","mesin":"TGN-1002","desain":2025,"created_at":"2025-12-02T01:04:58.000000Z","updated_at":"2025-12-02T01:04:58.000000Z","gilir_cetak":null}
```

### Expected API Response (MMEA)

```json
{"no_po":3000276764,"no_obc":"DEN235380","jenis":"HPTL","tgl_obc":"2025-11-11","tgl_jt":"2025-12-01","tgl_bb":"2025-11-28","tgl_cetak":"2025-11-28","tgl_verif":"2025-12-01","tgl_kemas":null,"jml_order":500,"rencet":520,"jml_bb":0,"jml_cd":520,"total_cd":520,"jml_cetak":520,"hcs_verif":511,"hcts_verif":9,"hcs_sisa":0,"total_hcts":9,"kemas":0,"kirim":0,"status":"ZP03","mesin":"TGN-1001","desain":2025,"created_at":"2025-11-11T07:49:51.000000Z","updated_at":"2025-12-01T15:13:55.000000Z","gilir_cetak":null}
```

### Parsed Structure

```php
// What we extract and use:
[
    'po_number' => int,
    'obc_number' => string|null,
    'product_type' => string,
    'total_sheets' => int,      // jml_lembar for regular
    'total_rims' => int|null,   // jml_rim for MMEA, calculated for regular
    'pack_sheets' => int|null,  // lbr_kemas for MMEA only
    'raw' => array,             // Original response for display
]
```

---

## Story 3.3: Spec Display Component

**As a** user  
**I want** to see product specifications when viewing an order  
**So that** I know what product I'm working with

**Acceptance Criteria:**
- [ ] Spec card component created
- [ ] Shows key spec fields
- [ ] Fetches from API when viewing order
- [ ] Handles loading state
- [ ] Handles error state (API unavailable)
- [ ] Caches response during page session (optional)

### Spec Card Component Fields

| Field | Description |
|-------|-------------|
| Card Title | "Product Specification" |
| PO Number | From API response |
| OBC | OBC number |
| Type | Product type |
| Series | Product series |
| Machine | Assigned machine |
| Total Sheets | Number of sheets |
| API Indicator | Note: "Data from SIRINE API" |

### Component States

| State | Display |
|-------|---------|
| Loading | Spinner/skeleton |
| Success | Spec card with fields |
| Error | "Unable to fetch specifications" + Retry button |

---

## Story 3.4: PO Validation on Order Create

**As a** user  
**I want** the system to validate PO number against SIRINE  
**So that** I don't create orders for non-existent POs

**Acceptance Criteria:**
- [ ] When creating order, validate PO exists in SIRINE
- [ ] Show spec preview before confirming
- [ ] Block creation if PO not found
- [ ] Show appropriate error message

**Workflow:**
```
1. User enters PO number
2. User selects order type (regular/mmea)
3. User clicks "Check PO" or auto-fetch on blur
4. System calls SIRINE API
5. If found → show spec preview, enable "Create Order"
6. If not found → show error "PO not found in SIRINE"
7. User can proceed to create order with spec data
```

---

## Routes Summary

```php
// API routes for frontend to fetch specs
Route::middleware('auth')->group(function () {
    Route::get('/api/specifications/{poNumber}', [SpecificationController::class, 'show']);
    Route::get('/api/specifications/{poNumber}/validate', [SpecificationController::class, 'validate']);
});
```

---

## Testing

**Manual Testing:**
1. Test with valid PO number → should return spec
2. Test with invalid PO number → should return null/error
3. Test with API down → should handle gracefully
4. Test both regular and MMEA endpoints

**Test PO Numbers:** (get from your SIRINE system)
- Regular: [need actual test PO]
- MMEA: [need actual test PO]

---

## Definition of Done (Sprint 3)

- [ ] SirineApiService created and working
- [ ] Can fetch regular specs
- [ ] Can fetch MMEA specs
- [ ] Error handling implemented
- [ ] Spec display component created
- [ ] PO validation working
- [ ] Configuration in .env

---

## Sprint 3 Checklist

```
[ ] 3.1 SIRINE API Service
    [ ] SirineApiService class
    [ ] getRegularSpec method
    [ ] getMmeaSpec method
    [ ] getSpecification method
    [ ] validatePo method
    [ ] Error handling
    [ ] SSL handling
    [ ] Configuration

[ ] 3.2 Response Handling
    [ ] Parse regular response
    [ ] Parse MMEA response
    [ ] Consistent structure

[ ] 3.3 Spec Display
    [ ] Vue component
    [ ] Loading state
    [ ] Error state
    [ ] Display fields

[ ] 3.4 PO Validation
    [ ] Validate on order create
    [ ] Show preview
    [ ] Block invalid PO
    [ ] Error messages
```

---

## Notes

**Important:** This app does NOT store specification data. Every time you need specs:
1. Call SIRINE API
2. Display the data
3. Don't persist it

The only spec-related data we store:
- `po_number` - reference to external system
- `obc_number` - reference to external system  
- `product_type` - copied at order creation for quick display

