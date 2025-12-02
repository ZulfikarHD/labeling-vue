<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Services\SirineApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola specification requests
 * yang bertujuan untuk fetch dan validate product specifications dari SIRINE API
 *
 * Controller ini berfungsi sebagai bridge antara frontend dan SIRINE API,
 * dengan response format yang konsisten untuk consumption oleh Vue components.
 */
class SpecificationController extends Controller
{
    public function __construct(
        private SirineApiService $sirineService
    ) {}

    /**
     * Mendapatkan spesifikasi produk berdasarkan PO number
     * dengan format response yang sudah di-parse untuk frontend
     *
     * @param  Request  $request  HTTP request dengan query parameter 'type'
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @return JsonResponse Response dengan data spesifikasi atau error message
     */
    public function show(Request $request, int $poNumber): JsonResponse
    {
        $type = $this->getOrderType($request);

        $specification = $this->sirineService->getParsedSpecification($poNumber, $type);

        if ($specification === null) {
            return response()->json([
                'success' => false,
                'message' => 'Spesifikasi tidak ditemukan untuk PO '.$poNumber,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Spesifikasi berhasil ditemukan',
            'data' => $specification,
        ]);
    }

    /**
     * Memvalidasi apakah PO exists di SIRINE system
     * untuk digunakan sebelum membuat order baru
     *
     * @param  Request  $request  HTTP request dengan query parameter 'type'
     * @param  int  $poNumber  Nomor PO yang akan divalidasi
     * @return JsonResponse Response dengan status validasi dan preview data
     */
    public function validate(Request $request, int $poNumber): JsonResponse
    {
        $type = $this->getOrderType($request);

        $isValid = $this->sirineService->validatePo($poNumber, $type);

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'PO '.$poNumber.' tidak ditemukan di SIRINE',
                'data' => null,
            ]);
        }

        // Jika valid, sertakan preview data untuk confirmation
        $specification = $this->sirineService->getParsedSpecification($poNumber, $type);

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'PO '.$poNumber.' valid dan ditemukan di SIRINE',
            'data' => $specification,
        ]);
    }

    /**
     * Mendapatkan raw specification tanpa parsing
     * untuk debugging atau kebutuhan khusus
     *
     * @param  Request  $request  HTTP request dengan query parameter 'type'
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @return JsonResponse Response dengan raw data dari SIRINE API
     */
    public function raw(Request $request, int $poNumber): JsonResponse
    {
        $type = $this->getOrderType($request);

        $specification = $this->sirineService->getSpecification($poNumber, $type);

        if ($specification === null) {
            return response()->json([
                'success' => false,
                'message' => 'Spesifikasi tidak ditemukan untuk PO '.$poNumber,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Raw data berhasil ditemukan',
            'data' => $specification,
        ]);
    }

    /**
     * Helper untuk mendapatkan OrderType dari request
     * dengan default ke Regular jika tidak specified
     *
     * @param  Request  $request  HTTP request
     * @return OrderType Tipe order yang dipilih
     */
    private function getOrderType(Request $request): OrderType
    {
        $typeString = $request->query('type', 'regular');

        return match (strtolower($typeString)) {
            'mmea' => OrderType::Mmea,
            default => OrderType::Regular,
        };
    }
}
