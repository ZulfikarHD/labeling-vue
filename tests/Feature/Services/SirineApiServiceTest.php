<?php

namespace Tests\Feature\Services;

use App\Enums\OrderType;
use App\Services\SirineApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test class untuk SirineApiService
 * yang mencakup fetch specifications, validation, dan error handling
 */
class SirineApiServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sample regular order response dari SIRINE API
     */
    private array $sampleRegularResponse = [
        'no_po' => 3000277761,
        'no_obc' => 'PST410127',
        'jenis' => 'P',
        'tgl_obc' => '2025-11-10',
        'tgl_jt' => '2025-11-28',
        'tgl_bb' => '2025-11-28',
        'tgl_cetak' => '2025-12-01',
        'tgl_verif' => '2025-12-01',
        'tgl_kemas' => '2025-12-01',
        'jml_order' => 3300,
        'rencet' => 86,
        'jml_bb' => 0,
        'jml_cd' => 86,
        'total_cd' => 86,
        'jml_cetak' => 86,
        'hcs_verif' => 80,
        'hcts_verif' => 6,
        'hcs_sisa' => 0,
        'total_hcts' => 6,
        'kemas' => 80,
        'kirim' => 0,
        'status' => '-',
        'mesin' => 'TGN-1002',
        'desain' => 2025,
    ];

    /**
     * Sample MMEA order response dari SIRINE API
     */
    private array $sampleMmeaResponse = [
        'no_po' => 3000276764,
        'no_obc' => 'DEN235380',
        'jenis' => 'HPTL',
        'tgl_obc' => '2025-11-11',
        'tgl_jt' => '2025-12-01',
        'jml_order' => 500,
        'rencet' => 520,
        'mesin' => 'TGN-1001',
        'status' => 'ZP03',
    ];

    /**
     * Test dapat fetch specification untuk order regular (PCHT)
     */
    public function test_can_fetch_regular_specification(): void
    {
        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getRegularSpec(3000277761);

        $this->assertIsArray($result);
        $this->assertEquals(3000277761, $result['no_po']);
        $this->assertEquals('PST410127', $result['no_obc']);
        $this->assertEquals('P', $result['jenis']);
    }

    /**
     * Test dapat fetch specification untuk order MMEA
     */
    public function test_can_fetch_mmea_specification(): void
    {
        Http::fake([
            '*/detail-order-mmea/3000276764' => Http::response($this->sampleMmeaResponse),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getMmeaSpec(3000276764);

        $this->assertIsArray($result);
        $this->assertEquals(3000276764, $result['no_po']);
        $this->assertEquals('DEN235380', $result['no_obc']);
        $this->assertEquals('HPTL', $result['jenis']);
    }

    /**
     * Test return null untuk PO number yang tidak ada
     */
    public function test_returns_null_for_invalid_po_number(): void
    {
        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getRegularSpec(9999999999);

        $this->assertNull($result);
    }

    /**
     * Test dapat validate PO exists
     */
    public function test_can_validate_po_exists(): void
    {
        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->validatePo(3000277761, OrderType::Regular);

        $this->assertTrue($result);
    }

    /**
     * Test validate return false untuk PO yang tidak ada
     */
    public function test_validate_returns_false_for_invalid_po(): void
    {
        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->validatePo(9999999999, OrderType::Regular);

        $this->assertFalse($result);
    }

    /**
     * Test handle API timeout dengan graceful
     */
    public function test_handles_api_timeout_gracefully(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        });

        $service = app(SirineApiService::class);
        $result = $service->getRegularSpec(3000277761);

        $this->assertNull($result);
    }

    /**
     * Test handle API error response
     */
    public function test_handles_api_error_response(): void
    {
        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response(['error' => 'Not found'], 404),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getRegularSpec(3000277761);

        $this->assertNull($result);
    }

    /**
     * Test dapat parse response dengan benar
     */
    public function test_can_parse_response_correctly(): void
    {
        $service = app(SirineApiService::class);
        $parsed = $service->parseResponse($this->sampleRegularResponse);

        $this->assertEquals(3000277761, $parsed['po_number']);
        $this->assertEquals('PST410127', $parsed['obc_number']);
        $this->assertEquals('P', $parsed['product_type']);
        $this->assertEquals('2025-11-10', $parsed['order_date']);
        $this->assertEquals('2025-11-28', $parsed['due_date']);
        $this->assertEquals(3300, $parsed['total_order']);
        $this->assertEquals(86, $parsed['total_sheets']);
        $this->assertEquals('TGN-1002', $parsed['machine']);
        $this->assertEquals(2025, $parsed['design_year']);
        $this->assertEquals('-', $parsed['status']);
        $this->assertEquals(80, $parsed['verified_good']);
        $this->assertEquals(6, $parsed['verified_defect']);
        $this->assertArrayHasKey('raw', $parsed);
    }

    /**
     * Test menggunakan endpoint yang benar berdasarkan order type
     */
    public function test_uses_correct_endpoint_based_on_order_type(): void
    {
        Http::fake([
            '*/detail-order-pcht/*' => Http::response(['type' => 'pcht']),
            '*/detail-order-mmea/*' => Http::response(['type' => 'mmea']),
        ]);

        $service = app(SirineApiService::class);

        // Test regular - harus call endpoint PCHT
        $result = $service->getSpecification(123, OrderType::Regular);
        $this->assertEquals('pcht', $result['type']);

        // Test MMEA - harus call endpoint MMEA
        $result = $service->getSpecification(456, OrderType::Mmea);
        $this->assertEquals('mmea', $result['type']);
    }

    /**
     * Test dapat fetch parsed specification
     */
    public function test_can_fetch_parsed_specification(): void
    {
        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getParsedSpecification(3000277761, OrderType::Regular);

        $this->assertIsArray($result);
        $this->assertEquals(3000277761, $result['po_number']);
        $this->assertEquals('PST410127', $result['obc_number']);
        $this->assertArrayHasKey('raw', $result);
    }

    /**
     * Test return null untuk parsed specification jika PO tidak ditemukan
     */
    public function test_returns_null_for_parsed_specification_when_po_not_found(): void
    {
        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getParsedSpecification(9999999999, OrderType::Regular);

        $this->assertNull($result);
    }

    /**
     * Test handle response dengan error field
     */
    public function test_returns_null_when_response_has_error_field(): void
    {
        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response([
                'error' => 'PO not found',
            ]),
        ]);

        $service = app(SirineApiService::class);
        $result = $service->getRegularSpec(3000277761);

        $this->assertNull($result);
    }
}
