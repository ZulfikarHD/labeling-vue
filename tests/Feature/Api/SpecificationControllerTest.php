<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Test class untuk SpecificationController
 * yang mencakup fetch specifications via API endpoints
 */
class SpecificationControllerTest extends TestCase
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
        'jml_order' => 3300,
        'rencet' => 86,
        'jml_cetak' => 86,
        'hcs_verif' => 80,
        'hcts_verif' => 6,
        'kemas' => 80,
        'kirim' => 0,
        'status' => '-',
        'mesin' => 'TGN-1002',
        'desain' => 2025,
    ];

    /**
     * Test guest tidak dapat akses specification endpoints
     */
    public function test_guest_cannot_access_specification_endpoints(): void
    {
        $response = $this->getJson('/api/specifications/3000277761');

        $response->assertUnauthorized();
    }

    /**
     * Test authenticated user dapat fetch specification
     */
    public function test_authenticated_user_can_fetch_specification(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $response = $this->actingAs($user, 'sanctum')
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
                    'order_date',
                    'due_date',
                    'total_order',
                    'total_sheets',
                    'machine',
                ],
            ]);
    }

    /**
     * Test return 404 untuk PO yang tidak ada
     */
    public function test_returns_404_for_non_existent_po(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/9999999999');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * Test dapat validate PO via API
     */
    public function test_can_validate_po_via_api(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/3000277761/validate?type=regular');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'valid' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'po_number',
                    'obc_number',
                ],
            ]);
    }

    /**
     * Test validate return invalid untuk PO yang tidak ada
     */
    public function test_validate_returns_invalid_for_non_existent_po(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/9999999999/validate');

        $response->assertOk()
            ->assertJson([
                'success' => false,
                'valid' => false,
            ]);
    }

    /**
     * Test dapat specify order type via query parameter
     */
    public function test_can_specify_order_type_via_query_parameter(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-mmea/3000276764' => Http::response([
                'no_po' => 3000276764,
                'no_obc' => 'DEN235380',
                'jenis' => 'HPTL',
            ]),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/3000276764?type=mmea');

        $response->assertOk();

        // Verify correct endpoint was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'detail-order-mmea');
        });
    }

    /**
     * Test dapat fetch raw specification data
     */
    public function test_can_fetch_raw_specification_data(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $response = $this->actingAs($user, 'sanctum')
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

    /**
     * Test raw endpoint return 404 untuk PO yang tidak ada
     */
    public function test_raw_returns_404_for_non_existent_po(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/9999999999' => Http::response([]),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/9999999999/raw');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * Test default order type adalah regular
     */
    public function test_default_order_type_is_regular(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/*' => Http::response($this->sampleRegularResponse),
        ]);

        // Request tanpa type parameter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/3000277761');

        $response->assertOk();

        // Verify endpoint PCHT (regular) yang dipanggil
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'detail-order-pcht');
        });
    }

    /**
     * Test validation endpoint dengan MMEA type
     */
    public function test_validation_with_mmea_type(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-mmea/3000276764' => Http::response([
                'no_po' => 3000276764,
                'no_obc' => 'DEN235380',
                'jenis' => 'HPTL',
            ]),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/3000276764/validate?type=mmea');

        $response->assertOk()
            ->assertJson([
                'valid' => true,
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'detail-order-mmea');
        });
    }

    /**
     * Test response structure dari fetch specification
     */
    public function test_fetch_specification_response_structure(): void
    {
        $user = User::factory()->create();

        Http::fake([
            '*/detail-order-pcht/3000277761' => Http::response($this->sampleRegularResponse),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/specifications/3000277761');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'po_number',
                    'obc_number',
                    'product_type',
                    'order_date',
                    'due_date',
                    'total_order',
                    'total_sheets',
                    'machine',
                    'design_year',
                    'status',
                    'print_count',
                    'verified_good',
                    'verified_defect',
                    'packed',
                    'shipped',
                    'raw',
                ],
            ]);
    }
}
