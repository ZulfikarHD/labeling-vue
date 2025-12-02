<?php

namespace App\Services;

use App\Enums\OrderType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service class untuk komunikasi dengan SIRINE API
 * yang bertujuan untuk fetch product specifications dari external system
 *
 * SIRINE merupakan sistem eksternal yang menyimpan data spesifikasi produk,
 * antara lain: detail order, OBC number, jenis produk, dan informasi lainnya.
 * Aplikasi ini tidak menyimpan data spesifikasi - selalu fetch dari SIRINE API.
 */
class SirineApiService
{
    /**
     * Base URL untuk SIRINE API
     */
    private string $baseUrl;

    /**
     * Timeout untuk HTTP request dalam detik
     */
    private int $timeout = 10;

    public function __construct()
    {
        $this->baseUrl = config('services.sirine.url', 'https://sirine.peruri.co.id/sirine/api');
    }

    /**
     * Mendapatkan spesifikasi untuk order regular (PCHT)
     * dengan endpoint khusus untuk tipe order reguler
     *
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @return array|null Data spesifikasi atau null jika tidak ditemukan
     */
    public function getRegularSpec(int $poNumber): ?array
    {
        return $this->fetchSpec("/detail-order-pcht/{$poNumber}");
    }

    /**
     * Mendapatkan spesifikasi untuk order MMEA
     * dengan endpoint khusus untuk tipe order MMEA
     *
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @return array|null Data spesifikasi atau null jika tidak ditemukan
     */
    public function getMmeaSpec(int $poNumber): ?array
    {
        return $this->fetchSpec("/detail-order-mmea/{$poNumber}");
    }

    /**
     * Mendapatkan spesifikasi berdasarkan tipe order
     * yang secara otomatis memilih endpoint yang sesuai
     *
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @param  OrderType  $type  Tipe order (Regular atau Mmea)
     * @return array|null Data spesifikasi atau null jika tidak ditemukan
     */
    public function getSpecification(int $poNumber, OrderType $type): ?array
    {
        return match ($type) {
            OrderType::Regular => $this->getRegularSpec($poNumber),
            OrderType::Mmea => $this->getMmeaSpec($poNumber),
        };
    }

    /**
     * Memvalidasi apakah PO exists di external system
     * dengan melakukan fetch dan check response
     *
     * @param  int  $poNumber  Nomor PO yang akan divalidasi
     * @param  OrderType  $type  Tipe order untuk menentukan endpoint
     * @return bool True jika PO valid dan ditemukan
     */
    public function validatePo(int $poNumber, OrderType $type): bool
    {
        $spec = $this->getSpecification($poNumber, $type);

        return $spec !== null;
    }

    /**
     * Parse response dari API ke format yang konsisten
     * untuk memudahkan penggunaan di frontend
     *
     * @param  array  $rawResponse  Response asli dari SIRINE API
     * @return array Structured data dengan format konsisten
     */
    public function parseResponse(array $rawResponse): array
    {
        return [
            'po_number' => $rawResponse['no_po'] ?? null,
            'obc_number' => $rawResponse['no_obc'] ?? null,
            'product_type' => $rawResponse['jenis'] ?? null,
            'order_date' => $rawResponse['tgl_obc'] ?? null,
            'due_date' => $rawResponse['tgl_jt'] ?? null,
            'total_order' => $rawResponse['jml_order'] ?? 0,
            'total_sheets' => $rawResponse['rencet'] ?? 0,
            'machine' => $rawResponse['mesin'] ?? null,
            'design_year' => $rawResponse['desain'] ?? null,
            'status' => $rawResponse['status'] ?? null,
            'print_count' => $rawResponse['jml_cetak'] ?? 0,
            'verified_good' => $rawResponse['hcs_verif'] ?? 0,
            'verified_defect' => $rawResponse['hcts_verif'] ?? 0,
            'packed' => $rawResponse['kemas'] ?? 0,
            'shipped' => $rawResponse['kirim'] ?? 0,
            'raw' => $rawResponse, // Simpan response asli untuk referensi
        ];
    }

    /**
     * Fetch spesifikasi dari SIRINE API dengan error handling
     * yang mencakup SSL certificate issues dan timeout
     *
     * @param  string  $endpoint  Endpoint API yang akan dipanggil
     * @return array|null Data response atau null jika error
     */
    private function fetchSpec(string $endpoint): ?array
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification untuk internal network
                'timeout' => $this->timeout,
            ])->get($this->baseUrl.$endpoint);

            if ($response->successful()) {
                $data = $response->json();

                // API mungkin mengembalikan array dengan data atau error
                // jika response kosong atau ada error field, return null
                if (isset($data['error']) || empty($data)) {
                    return null;
                }

                return $data;
            }

            // Log unsuccessful response untuk debugging
            Log::warning('SIRINE API unsuccessful response', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            // Log error dengan detail untuk troubleshooting
            Log::error('SIRINE API Error', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Mendapatkan spesifikasi dengan format yang sudah di-parse
     * untuk langsung digunakan di frontend
     *
     * @param  int  $poNumber  Nomor PO yang akan dicari
     * @param  OrderType  $type  Tipe order
     * @return array|null Parsed specification atau null jika tidak ditemukan
     */
    public function getParsedSpecification(int $poNumber, OrderType $type): ?array
    {
        $rawSpec = $this->getSpecification($poNumber, $type);

        if ($rawSpec === null) {
            return null;
        }

        return $this->parseResponse($rawSpec);
    }
}
