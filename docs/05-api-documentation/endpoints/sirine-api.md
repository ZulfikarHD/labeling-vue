# SIRINE API Integration - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk integrasi SIRINE API yang bertujuan untuk menjelaskan cara fetch product specifications dari external system, yaitu: service class, controller endpoints, dan response handling yang terintegrasi dengan aplikasi Label Generator.

## Overview

SIRINE API merupakan external service yang menyimpan data spesifikasi produk untuk production orders. Aplikasi Label Generator tidak menyimpan data spesifikasi secara lokal - setiap kali membutuhkan data specs, aplikasi akan fetch dari SIRINE API untuk memastikan data selalu up-to-date.

**Penting:** Aplikasi hanya menyimpan reference data, antara lain:
- `po_number` - Nomor PO sebagai referensi
- `obc_number` - Nomor OBC sebagai referensi
- `product_type` - Tipe produk (disalin saat order creation untuk quick display)

## External API Endpoints

### Base URL

```
https://sirine.peruri.co.id/sirine/api
```

Konfigurasi base URL dapat diubah melalui environment variable:

```env
SIRINE_API_URL=https://sirine.peruri.co.id/sirine/api
```

### Available Endpoints

| Type | Endpoint | Method | Description |
|------|----------|--------|-------------|
| Regular (PCHT) | `/detail-order-pcht/{po_number}` | GET | Fetch spec untuk order reguler |
| MMEA | `/detail-order-mmea/{po_number}` | GET | Fetch spec untuk order MMEA |

## Internal API Endpoints

> **Note:** API routes menggunakan Laravel Sanctum untuk authentication dan berada di `routes/api.php` dengan prefix `/api` otomatis.

### GET /api/specifications/{poNumber}

Mendapatkan spesifikasi produk berdasarkan PO number dengan format response yang sudah di-parse untuk frontend.

**Parameters:**

| Parameter | Type | Location | Description |
|-----------|------|----------|-------------|
| poNumber | integer | path | Nomor PO yang akan dicari |
| type | string | query | Tipe order: `regular` (default) atau `mmea` |

**Success Response (200):**

```json
{
    "success": true,
    "message": "Spesifikasi berhasil ditemukan",
    "data": {
        "po_number": 3000277761,
        "obc_number": "PST410127",
        "product_type": "P",
        "order_date": "2025-11-10",
        "due_date": "2025-11-28",
        "total_order": 3300,
        "total_sheets": 86,
        "machine": "TGN-1002",
        "design_year": 2025,
        "status": "-",
        "print_count": 86,
        "verified_good": 80,
        "verified_defect": 6,
        "packed": 80,
        "shipped": 0,
        "raw": { ... }
    }
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "Spesifikasi tidak ditemukan untuk PO 3000277761",
    "data": null
}
```

### GET /api/specifications/{poNumber}/validate

Memvalidasi apakah PO exists di SIRINE system untuk digunakan sebelum membuat order baru.

**Parameters:**

| Parameter | Type | Location | Description |
|-----------|------|----------|-------------|
| poNumber | integer | path | Nomor PO yang akan divalidasi |
| type | string | query | Tipe order: `regular` (default) atau `mmea` |

**Valid Response:**

```json
{
    "success": true,
    "valid": true,
    "message": "PO 3000277761 valid dan ditemukan di SIRINE",
    "data": { ... }
}
```

**Invalid Response:**

```json
{
    "success": false,
    "valid": false,
    "message": "PO 3000277761 tidak ditemukan di SIRINE",
    "data": null
}
```

### GET /api/specifications/{poNumber}/raw

Mendapatkan raw specification data tanpa parsing untuk debugging atau kebutuhan khusus.

**Parameters:**

| Parameter | Type | Location | Description |
|-----------|------|----------|-------------|
| poNumber | integer | path | Nomor PO yang akan dicari |
| type | string | query | Tipe order: `regular` (default) atau `mmea` |

**Success Response (200):**

```json
{
    "success": true,
    "message": "Raw data berhasil ditemukan",
    "data": {
        "no_po": 3000277761,
        "no_obc": "PST410127",
        "jenis": "P",
        "tgl_obc": "2025-11-10",
        "tgl_jt": "2025-11-28",
        "tgl_bb": "2025-11-28",
        "tgl_cetak": "2025-12-01",
        "tgl_verif": "2025-12-01",
        "tgl_kemas": "2025-12-01",
        "jml_order": 3300,
        "rencet": 86,
        "jml_bb": 0,
        "jml_cd": 86,
        "total_cd": 86,
        "jml_cetak": 86,
        "hcs_verif": 80,
        "hcts_verif": 6,
        "hcs_sisa": 0,
        "total_hcts": 6,
        "kemas": 80,
        "kirim": 0,
        "status": "-",
        "mesin": "TGN-1002",
        "desain": 2025,
        "created_at": "2025-12-02T01:04:58.000000Z",
        "updated_at": "2025-12-02T01:04:58.000000Z",
        "gilir_cetak": null
    }
}
```

## Service Class

### SirineApiService

Service class untuk komunikasi dengan SIRINE API yang mencakup methods untuk fetch dan validate specifications.

**Location:** `app/Services/SirineApiService.php`

**Methods:**

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `getRegularSpec` | int $poNumber | ?array | Fetch spec untuk order regular |
| `getMmeaSpec` | int $poNumber | ?array | Fetch spec untuk order MMEA |
| `getSpecification` | int $poNumber, OrderType $type | ?array | Fetch spec berdasarkan tipe |
| `validatePo` | int $poNumber, OrderType $type | bool | Validasi PO exists |
| `parseResponse` | array $rawResponse | array | Parse raw response ke format konsisten |
| `getParsedSpecification` | int $poNumber, OrderType $type | ?array | Fetch spec dengan format parsed |

**Usage Example:**

```php
use App\Services\SirineApiService;
use App\Enums\OrderType;

$service = app(SirineApiService::class);

// Fetch specification
$spec = $service->getSpecification(3000277761, OrderType::Regular);

// Validate PO
$isValid = $service->validatePo(3000277761, OrderType::Mmea);

// Get parsed specification
$parsed = $service->getParsedSpecification(3000277761, OrderType::Regular);
```

## Data Mapping

### Raw Response → Parsed Response

Mapping field dari raw SIRINE API response ke format internal:

| Raw Field | Parsed Field | Description |
|-----------|--------------|-------------|
| no_po | po_number | Nomor PO |
| no_obc | obc_number | Nomor OBC |
| jenis | product_type | Jenis produk (P, HPTL, dll) |
| tgl_obc | order_date | Tanggal order |
| tgl_jt | due_date | Tanggal jatuh tempo |
| jml_order | total_order | Jumlah order |
| rencet | total_sheets | Jumlah rencet/sheets |
| mesin | machine | Mesin yang digunakan |
| desain | design_year | Tahun desain |
| status | status | Status order |
| jml_cetak | print_count | Jumlah cetak |
| hcs_verif | verified_good | HCS verified (baik) |
| hcts_verif | verified_defect | HCTS verified (cacat) |
| kemas | packed | Jumlah dikemas |
| kirim | shipped | Jumlah dikirim |

### Product Type Mapping

| Raw Value | Display Label | Order Type |
|-----------|---------------|------------|
| P | PCHT (Regular) | Regular |
| HPTL | HPTL (MMEA) | MMEA |
| MMEA | MMEA | MMEA |

## Frontend Integration

### Vue Component

**SpecificationCard Component**

Komponen untuk menampilkan specification data dengan states loading, error, dan success.

**Location:** `resources/js/components/SpecificationCard.vue`

**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| specification | SpecificationData | null | Data spesifikasi |
| loading | boolean | false | Loading state |
| error | string | null | Error message |
| showRetry | boolean | true | Tampilkan tombol retry |
| compact | boolean | false | Tampilan compact |

**Usage:**

```vue
<script setup lang="ts">
import SpecificationCard from '@/components/SpecificationCard.vue';
import { useSpecification } from '@/composables/useSpecification';

const { specification, loading, error, fetchSpec } = useSpecification();

onMounted(() => {
    fetchSpec(3000277761, 'regular');
});
</script>

<template>
    <SpecificationCard
        :specification="specification"
        :loading="loading"
        :error="error"
        @retry="fetchSpec(3000277761, 'regular')"
    />
</template>
```

### Composable

**useSpecification**

Composable untuk mengelola fetch dan state specification data.

**Location:** `resources/js/composables/useSpecification.ts`

**Return Values:**

| Property | Type | Description |
|----------|------|-------------|
| specification | Ref<SpecificationData \| null> | Data spesifikasi |
| loading | Ref<boolean> | Loading state |
| error | Ref<string \| null> | Error message |
| hasSpecification | ComputedRef<boolean> | Has specification data |
| hasError | ComputedRef<boolean> | Has error |
| reset | () => void | Reset state |
| fetchSpec | (poNumber, type) => Promise | Fetch specification |
| validatePo | (poNumber, type) => Promise | Validate PO |
| fetchRawSpec | (poNumber, type) => Promise | Fetch raw data |

**Usage:**

```typescript
import { useSpecification } from '@/composables/useSpecification';

const { 
    specification, 
    loading, 
    error, 
    fetchSpec, 
    validatePo,
    reset 
} = useSpecification();

// Fetch specification
await fetchSpec(3000277761, 'regular');

// Validate PO before creating order
const { valid, data } = await validatePo(3000277761, 'mmea');
if (valid) {
    // Proceed with order creation
}

// Reset state
reset();
```

## Error Handling

### API Error Scenarios

| Scenario | HTTP Status | Error Message |
|----------|-------------|---------------|
| PO tidak ditemukan | 404 | "Spesifikasi tidak ditemukan untuk PO {number}" |
| API timeout | - | "Gagal memuat spesifikasi. Silakan coba lagi." |
| SSL certificate error | - | Handled dengan verify: false |
| Network error | - | "Terjadi kesalahan tidak terduga" |

### Error Logging

Errors dicatat ke Laravel log untuk troubleshooting:

```php
Log::error('SIRINE API Error', [
    'endpoint' => $endpoint,
    'message' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
]);
```

## Configuration

### Environment Variables

```env
# SIRINE API Configuration
SIRINE_API_URL=https://sirine.peruri.co.id/sirine/api
```

### Config File

```php
// config/services.php
'sirine' => [
    'url' => env('SIRINE_API_URL', 'https://sirine.peruri.co.id/sirine/api'),
],
```

## Security Notes

### SSL Certificate

SIRINE API menggunakan SSL certificate internal yang mungkin tidak diverifikasi oleh sistem. Untuk mengatasi hal ini, SSL verification di-disable untuk koneksi ke SIRINE:

```php
Http::withOptions([
    'verify' => false, // Disable SSL verification untuk internal network
    'timeout' => 10,
])->get($url);
```

**Note:** Pengaturan ini aman karena API berada dalam internal network Peruri.

### Authentication

Endpoint internal API (`/api/specifications/*`) dilindungi dengan middleware `auth:sanctum` sehingga hanya user yang authenticated yang dapat mengakses. Aplikasi menggunakan Laravel Sanctum untuk API authentication.

## Testing

### Manual Testing

Test cases untuk verifikasi API integration:

1. **Valid PO Number**
   - Input: PO number yang valid dari SIRINE
   - Expected: Response dengan data spesifikasi

2. **Invalid PO Number**
   - Input: PO number yang tidak exists
   - Expected: Response dengan error message

3. **Wrong Order Type**
   - Input: PO regular dengan type MMEA
   - Expected: Response null/error

4. **API Timeout**
   - Simulate: Network delay > 10 detik
   - Expected: Error handling dengan message yang sesuai

### Test PO Numbers

| Type | PO Number | OBC | Status |
|------|-----------|-----|--------|
| Regular | 3000277761 | PST410127 | Active |
| MMEA | 3000276764 | DEN235380 | ZP03 |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
