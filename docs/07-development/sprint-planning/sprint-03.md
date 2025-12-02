# Sprint 03: External API Integration (SIRINE)

Dokumentasi sprint untuk integrasi SIRINE API yang bertujuan untuk menghubungkan aplikasi Label Generator dengan external system untuk fetch product specifications.

## Overview

Sprint ini merupakan implementasi integrasi dengan SIRINE API yang menyimpan data spesifikasi produk. Aplikasi Label Generator tidak menyimpan data spesifikasi secara lokal - setiap kali membutuhkan data, aplikasi akan fetch dari SIRINE API.

**Sprint Document:** [sprints/03-external-api.md](../../../sprints/03-external-api.md)

## Sprint Goals

1. Membuat service class untuk komunikasi dengan SIRINE API
2. Implementasi controller untuk internal API endpoints
3. Membuat Vue components untuk display specifications
4. Dokumentasi lengkap untuk API integration

## User Stories

| Story | Title | Status |
|-------|-------|--------|
| 3.1 | SIRINE API Service | ✅ Completed |
| 3.2 | API Response Handling | ✅ Completed |
| 3.3 | Spec Display Component | ✅ Completed |
| 3.4 | PO Validation on Order Create | ✅ Completed |

## Deliverables

### Backend

| File | Description | Status |
|------|-------------|--------|
| `app/Services/SirineApiService.php` | Service class untuk SIRINE API | ✅ |
| `app/Http/Controllers/Api/SpecificationController.php` | Controller untuk specification endpoints | ✅ |
| `config/services.php` | Konfigurasi SIRINE URL | ✅ |
| `.env.example` | SIRINE_API_URL variable | ✅ |

### Frontend

| File | Description | Status |
|------|-------------|--------|
| `resources/js/components/SpecificationCard.vue` | Component untuk display specs | ✅ |
| `resources/js/composables/useSpecification.ts` | Composable untuk fetch specs | ✅ |

### Documentation

| File | Description | Status |
|------|-------------|--------|
| `docs/05-api-documentation/endpoints/sirine-api.md` | API documentation | ✅ |
| `docs/08-testing/test-cases/api-integration-tests.md` | Test cases | ✅ |

## API Endpoints Created

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/specifications/{poNumber}` | Fetch parsed specification |
| GET | `/api/specifications/{poNumber}/validate` | Validate PO exists |
| GET | `/api/specifications/{poNumber}/raw` | Fetch raw data |

## Technical Notes

### External API Endpoints

- Regular orders: `GET /detail-order-pcht/{po_number}`
- MMEA orders: `GET /detail-order-mmea/{po_number}`

### Key Implementation Details

- SSL verification disabled untuk internal network
- Timeout 10 detik
- Error logging ke Laravel log
- Response parsing ke format konsisten

## Definition of Done

- [x] SirineApiService created dan working
- [x] Can fetch regular specs
- [x] Can fetch MMEA specs
- [x] Error handling implemented
- [x] Spec display component created
- [x] PO validation working
- [x] Configuration in .env
- [x] Documentation complete

## Related Documentation

- [SIRINE API Documentation](../../05-api-documentation/endpoints/sirine-api.md)
- [API Integration Test Cases](../../08-testing/test-cases/api-integration-tests.md)
- [Business Rules](../../02-requirements/business-rules.md)

---

**Sprint Start**: 2025-12-02  
**Sprint End**: TBD  
**Status**: ✅ Completed  
**Author**: Zulfikar Hidayatullah
