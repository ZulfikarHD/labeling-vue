# API Integration Tests - SIRINE API

Dokumen ini merupakan dokumentasi test cases untuk integrasi SIRINE API yang bertujuan untuk memastikan service class dan controller berfungsi dengan benar, yaitu: fetch specifications, validate PO, dan error handling yang sesuai dengan expected behavior.

## Overview

Test cases untuk SIRINE API integration mencakup pengujian pada dua level:
1. **Service Level** - Testing `SirineApiService` class
2. **Controller Level** - Testing `SpecificationController` endpoints

## Test Environment

### Requirements

- PHPUnit 11.x
- Laravel 12.x
- HTTP mocking dengan `Http::fake()`

### Test Data

| Type | PO Number | OBC | Expected Result |
|------|-----------|-----|-----------------|
| Regular | 3000277761 | PST410127 | Valid response |
| MMEA | 3000276764 | DEN235380 | Valid response |
| Invalid | 9999999999 | - | null/error |

## Service Tests

### Test File

`tests/Feature/Services/SirineApiServiceTest.php`

### Test Cases

#### 1. Can Fetch Regular Specification

**Scenario:** Fetch specification untuk order regular (PCHT) dengan PO number valid

**Steps:**
1. Mock HTTP response dengan data regular order
2. Call `getRegularSpec()` dengan PO number
3. Verify response berisi data yang expected

**Expected Result:** Array dengan data spesifikasi lengkap

```php
/** @test */
public function test_can_fetch_regular_specification(): void
{
    Http::fake([
        '*/detail-order-pcht/3000277761' => Http::response([
            'no_po' => 3000277761,
            'no_obc' => 'PST410127',
            'jenis' => 'P',
            // ... other fields
        ]),
    ]);

    $service = app(SirineApiService::class);
    $result = $service->getRegularSpec(3000277761);

    $this->assertIsArray($result);
    $this->assertEquals(3000277761, $result['no_po']);
}
```

---

#### 2. Can Fetch MMEA Specification

**Scenario:** Fetch specification untuk order MMEA dengan PO number valid

**Steps:**
1. Mock HTTP response dengan data MMEA order
2. Call `getMmeaSpec()` dengan PO number
3. Verify response berisi data yang expected

**Expected Result:** Array dengan data spesifikasi MMEA

```php
/** @test */
public function test_can_fetch_mmea_specification(): void
{
    Http::fake([
        '*/detail-order-mmea/3000276764' => Http::response([
            'no_po' => 3000276764,
            'no_obc' => 'DEN235380',
            'jenis' => 'HPTL',
            // ... other fields
        ]),
    ]);

    $service = app(SirineApiService::class);
    $result = $service->getMmeaSpec(3000276764);

    $this->assertIsArray($result);
    $this->assertEquals('DEN235380', $result['no_obc']);
}
```

---

#### 3. Returns Null for Invalid PO Number

**Scenario:** Fetch specification dengan PO number yang tidak ada di SIRINE

**Steps:**
1. Mock HTTP response dengan empty atau error response
2. Call `getSpecification()` dengan PO number invalid
3. Verify response adalah null

**Expected Result:** null

```php
/** @test */
public function test_returns_null_for_invalid_po_number(): void
{
    Http::fake([
        '*/detail-order-pcht/9999999999' => Http::response([]),
    ]);

    $service = app(SirineApiService::class);
    $result = $service->getRegularSpec(9999999999);

    $this->assertNull($result);
}
```

---

#### 4. Can Validate PO Exists

**Scenario:** Validasi bahwa PO exists di SIRINE

**Steps:**
1. Mock HTTP response dengan data valid
2. Call `validatePo()` dengan PO number dan type
3. Verify return true

**Expected Result:** true

```php
/** @test */
public function test_can_validate_po_exists(): void
{
    Http::fake([
        '*/detail-order-pcht/3000277761' => Http::response([
            'no_po' => 3000277761,
            // ... other fields
        ]),
    ]);

    $service = app(SirineApiService::class);
    $result = $service->validatePo(3000277761, OrderType::Regular);

    $this->assertTrue($result);
}
```

---

#### 5. Validate Returns False for Invalid PO

**Scenario:** Validasi PO yang tidak ada di SIRINE

**Steps:**
1. Mock HTTP response dengan empty response
2. Call `validatePo()` dengan PO number invalid
3. Verify return false

**Expected Result:** false

```php
/** @test */
public function test_validate_returns_false_for_invalid_po(): void
{
    Http::fake([
        '*/detail-order-pcht/9999999999' => Http::response([]),
    ]);

    $service = app(SirineApiService::class);
    $result = $service->validatePo(9999999999, OrderType::Regular);

    $this->assertFalse($result);
}
```

---

#### 6. Handles API Timeout Gracefully

**Scenario:** API timeout saat fetch specification

**Steps:**
1. Mock HTTP response dengan timeout exception
2. Call `getSpecification()`
3. Verify return null tanpa exception thrown

**Expected Result:** null, error logged

```php
/** @test */
public function test_handles_api_timeout_gracefully(): void
{
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
    });

    $service = app(SirineApiService::class);
    $result = $service->getRegularSpec(3000277761);

    $this->assertNull($result);
}
```

---

#### 7. Can Parse Response Correctly

**Scenario:** Parse raw API response ke format internal

**Steps:**
1. Provide raw response array
2. Call `parseResponse()`
3. Verify parsed fields sesuai mapping

**Expected Result:** Array dengan field names yang di-mapped

```php
/** @test */
public function test_can_parse_response_correctly(): void
{
    $raw = [
        'no_po' => 3000277761,
        'no_obc' => 'PST410127',
        'jenis' => 'P',
        'tgl_obc' => '2025-11-10',
        'jml_order' => 3300,
        'rencet' => 86,
        'mesin' => 'TGN-1002',
    ];

    $service = app(SirineApiService::class);
    $parsed = $service->parseResponse($raw);

    $this->assertEquals(3000277761, $parsed['po_number']);
    $this->assertEquals('PST410127', $parsed['obc_number']);
    $this->assertEquals('P', $parsed['product_type']);
    $this->assertEquals(3300, $parsed['total_order']);
    $this->assertEquals(86, $parsed['total_sheets']);
    $this->assertEquals('TGN-1002', $parsed['machine']);
    $this->assertArrayHasKey('raw', $parsed);
}
```

---

#### 8. Uses Correct Endpoint Based on Order Type

**Scenario:** Verify correct endpoint dipanggil berdasarkan order type

**Steps:**
1. Mock both endpoints
2. Call `getSpecification()` dengan different types
3. Verify correct endpoint was called

**Expected Result:** Regular → PCHT endpoint, MMEA → MMEA endpoint

```php
/** @test */
public function test_uses_correct_endpoint_based_on_order_type(): void
{
    Http::fake([
        '*/detail-order-pcht/*' => Http::response(['type' => 'pcht']),
        '*/detail-order-mmea/*' => Http::response(['type' => 'mmea']),
    ]);

    $service = app(SirineApiService::class);

    // Test regular
    $result = $service->getSpecification(123, OrderType::Regular);
    $this->assertEquals('pcht', $result['type']);

    // Test MMEA
    $result = $service->getSpecification(456, OrderType::Mmea);
    $this->assertEquals('mmea', $result['type']);
}
```

## Controller Tests

### Test File

`tests/Feature/Api/SpecificationControllerTest.php`

### Test Cases

#### 9. Guest Cannot Access Specification Endpoints

**Scenario:** User yang tidak authenticated mencoba akses API

**Steps:**
1. Make GET request tanpa authentication
2. Verify redirect ke login

**Expected Result:** 302 redirect ke login

```php
/** @test */
public function test_guest_cannot_access_specification_endpoints(): void
{
    $response = $this->get('/api/specifications/3000277761');

    $response->assertRedirect('/login');
}
```

---

#### 10. Authenticated User Can Fetch Specification

**Scenario:** User authenticated dapat fetch specification

**Steps:**
1. Create dan login user
2. Mock SIRINE API response
3. Make GET request ke specification endpoint
4. Verify response JSON structure

**Expected Result:** 200 dengan JSON response

```php
/** @test */
public function test_authenticated_user_can_fetch_specification(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-pcht/3000277761' => Http::response([
            'no_po' => 3000277761,
            'no_obc' => 'PST410127',
            // ... fields
        ]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/3000277761');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Spesifikasi berhasil ditemukan',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'po_number',
                'obc_number',
                'product_type',
            ],
        ]);
}
```

---

#### 11. Returns 404 for Non-Existent PO

**Scenario:** Fetch specification untuk PO yang tidak ada

**Steps:**
1. Create dan login user
2. Mock empty SIRINE response
3. Make GET request
4. Verify 404 response

**Expected Result:** 404 dengan error message

```php
/** @test */
public function test_returns_404_for_non_existent_po(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-pcht/9999999999' => Http::response([]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/9999999999');

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'data' => null,
        ]);
}
```

---

#### 12. Can Validate PO via API

**Scenario:** Validate PO number melalui API endpoint

**Steps:**
1. Create dan login user
2. Mock valid SIRINE response
3. Make GET request ke validate endpoint
4. Verify validation response

**Expected Result:** 200 dengan valid: true dan preview data

```php
/** @test */
public function test_can_validate_po_via_api(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-pcht/3000277761' => Http::response([
            'no_po' => 3000277761,
            'no_obc' => 'PST410127',
            // ... fields
        ]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/3000277761/validate?type=regular');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'valid' => true,
        ])
        ->assertJsonStructure([
            'data' => ['po_number', 'obc_number'],
        ]);
}
```

---

#### 13. Validate Returns Invalid for Non-Existent PO

**Scenario:** Validate PO yang tidak ada

**Steps:**
1. Create dan login user
2. Mock empty SIRINE response
3. Make GET request ke validate endpoint
4. Verify valid: false

**Expected Result:** 200 dengan valid: false

```php
/** @test */
public function test_validate_returns_invalid_for_non_existent_po(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-pcht/9999999999' => Http::response([]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/9999999999/validate');

    $response->assertOk()
        ->assertJson([
            'success' => false,
            'valid' => false,
        ]);
}
```

---

#### 14. Can Specify Order Type via Query Parameter

**Scenario:** Specify order type (regular/mmea) via query parameter

**Steps:**
1. Create dan login user
2. Mock both SIRINE endpoints
3. Make requests dengan different type parameters
4. Verify correct endpoint dipanggil

**Expected Result:** Request dengan type=mmea calls MMEA endpoint

```php
/** @test */
public function test_can_specify_order_type_via_query_parameter(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-mmea/3000276764' => Http::response([
            'no_po' => 3000276764,
            'jenis' => 'HPTL',
        ]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/3000276764?type=mmea');

    $response->assertOk();
    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'detail-order-mmea');
    });
}
```

---

#### 15. Can Fetch Raw Specification Data

**Scenario:** Fetch raw specification tanpa parsing

**Steps:**
1. Create dan login user
2. Mock SIRINE response
3. Make GET request ke raw endpoint
4. Verify raw data returned

**Expected Result:** 200 dengan raw data fields

```php
/** @test */
public function test_can_fetch_raw_specification_data(): void
{
    $user = User::factory()->create();

    Http::fake([
        '*/detail-order-pcht/3000277761' => Http::response([
            'no_po' => 3000277761,
            'no_obc' => 'PST410127',
            'jenis' => 'P',
            'tgl_obc' => '2025-11-10',
        ]),
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/specifications/3000277761/raw');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'no_po' => 3000277761,
                'no_obc' => 'PST410127',
            ],
        ]);
}
```

## Test Summary

### Service Level Tests (12 tests)

| # | Test | Status |
|---|------|--------|
| 1 | Can fetch regular specification | ✅ |
| 2 | Can fetch MMEA specification | ✅ |
| 3 | Returns null for invalid PO number | ✅ |
| 4 | Can validate PO exists | ✅ |
| 5 | Validate returns false for invalid PO | ✅ |
| 6 | Handles API timeout gracefully | ✅ |
| 7 | Handles API error response | ✅ |
| 8 | Can parse response correctly | ✅ |
| 9 | Uses correct endpoint based on order type | ✅ |
| 10 | Can fetch parsed specification | ✅ |
| 11 | Returns null for parsed spec when PO not found | ✅ |
| 12 | Returns null when response has error field | ✅ |

### Controller Level Tests (11 tests)

| # | Test | Status |
|---|------|--------|
| 1 | Guest cannot access specification endpoints | ✅ |
| 2 | Authenticated user can fetch specification | ✅ |
| 3 | Returns 404 for non-existent PO | ✅ |
| 4 | Can validate PO via API | ✅ |
| 5 | Validate returns invalid for non-existent PO | ✅ |
| 6 | Can specify order type via query parameter | ✅ |
| 7 | Can fetch raw specification data | ✅ |
| 8 | Raw returns 404 for non-existent PO | ✅ |
| 9 | Default order type is regular | ✅ |
| 10 | Validation with MMEA type | ✅ |
| 11 | Fetch specification response structure | ✅ |

**Legend:**
- ✅ Passing
- ❌ Failing
- 📝 To be implemented

## Running Tests

```bash
# Run semua API integration tests sekaligus
php artisan test tests/Feature/Services/SirineApiServiceTest.php tests/Feature/Api/SpecificationControllerTest.php

# Run service tests saja
php artisan test tests/Feature/Services/SirineApiServiceTest.php

# Run controller tests saja
php artisan test tests/Feature/Api/SpecificationControllerTest.php

# Run specific test
php artisan test --filter=test_can_fetch_regular_specification

# Run dengan verbose output
php artisan test tests/Feature/Services/SirineApiServiceTest.php --verbose
```

### Latest Test Results

```
   PASS  Tests\Feature\Services\SirineApiServiceTest (12 tests)
   PASS  Tests\Feature\Api\SpecificationControllerTest (11 tests)

  Tests:    23 passed (88 assertions)
  Duration: 0.41s
```

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Total Test Cases**: 23 (all passing)
