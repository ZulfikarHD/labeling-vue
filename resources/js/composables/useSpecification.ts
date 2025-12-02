import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Interface untuk parsed specification data dari SIRINE API
 * yang berisi informasi lengkap produk dan status produksi
 */
export interface SpecificationData {
    po_number: number | null;
    obc_number: string | null;
    product_type: string | null;
    order_date: string | null;
    due_date: string | null;
    total_order: number;
    total_sheets: number;
    machine: string | null;
    design_year: number | null;
    status: string | null;
    print_count: number;
    verified_good: number;
    verified_defect: number;
    packed: number;
    shipped: number;
    raw: Record<string, unknown>;
}

/**
 * Interface untuk API response dari SpecificationController
 */
interface ApiResponse<T> {
    success: boolean;
    message: string;
    data: T | null;
    valid?: boolean;
}

/**
 * Order type untuk menentukan endpoint API yang digunakan
 */
export type OrderType = 'regular' | 'mmea';

/**
 * Composable untuk mengelola fetch dan state specification data
 * dari SIRINE API melalui internal API endpoints
 *
 * Composable ini menyediakan reactive state untuk loading, error, dan data,
 * serta methods untuk fetch dan validate specifications.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useSpecification } from '@/composables/useSpecification';
 *
 * const { specification, loading, error, fetchSpec, validatePo } = useSpecification();
 *
 * // Fetch specification
 * await fetchSpec(3000277761, 'regular');
 *
 * // Validate PO
 * const { valid, data } = await validatePo(3000277761, 'mmea');
 * </script>
 * ```
 */
export function useSpecification() {
    /**
     * Reactive ref untuk menyimpan specification data
     */
    const specification = ref<SpecificationData | null>(null);

    /**
     * Reactive ref untuk loading state
     */
    const loading = ref(false);

    /**
     * Reactive ref untuk error message
     */
    const error = ref<string | null>(null);

    /**
     * Computed untuk check apakah ada data specification
     */
    const hasSpecification = computed(() => specification.value !== null);

    /**
     * Computed untuk check apakah sedang dalam error state
     */
    const hasError = computed(() => error.value !== null);

    /**
     * Reset state ke initial values
     */
    function reset(): void {
        specification.value = null;
        loading.value = false;
        error.value = null;
    }

    /**
     * Fetch specification dari API berdasarkan PO number dan type
     *
     * @param poNumber - Nomor PO yang akan dicari
     * @param type - Tipe order (regular atau mmea)
     * @returns Promise dengan specification data atau null
     */
    async function fetchSpec(
        poNumber: number,
        type: OrderType = 'regular'
    ): Promise<SpecificationData | null> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<SpecificationData>>(
                `/api/specifications/${poNumber}`,
                {
                    params: { type },
                }
            );

            if (response.data.success && response.data.data) {
                specification.value = response.data.data;
                return response.data.data;
            }

            error.value = response.data.message || 'Spesifikasi tidak ditemukan';
            specification.value = null;
            return null;
        } catch (err) {
            // Handle axios error
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 404) {
                    error.value = `PO ${poNumber} tidak ditemukan di SIRINE`;
                } else if (err.response?.data?.message) {
                    error.value = err.response.data.message;
                } else {
                    error.value = 'Gagal memuat spesifikasi. Silakan coba lagi.';
                }
            } else {
                error.value = 'Terjadi kesalahan tidak terduga';
            }

            specification.value = null;
            return null;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Validate PO number dan mendapatkan preview data
     * untuk digunakan sebelum membuat order baru
     *
     * @param poNumber - Nomor PO yang akan divalidasi
     * @param type - Tipe order (regular atau mmea)
     * @returns Promise dengan result validasi dan preview data
     */
    async function validatePo(
        poNumber: number,
        type: OrderType = 'regular'
    ): Promise<{ valid: boolean; data: SpecificationData | null; message: string }> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<SpecificationData>>(
                `/api/specifications/${poNumber}/validate`,
                {
                    params: { type },
                }
            );

            if (response.data.valid && response.data.data) {
                specification.value = response.data.data;
                return {
                    valid: true,
                    data: response.data.data,
                    message: response.data.message,
                };
            }

            error.value = response.data.message;
            specification.value = null;
            return {
                valid: false,
                data: null,
                message: response.data.message,
            };
        } catch (err) {
            // Handle axios error
            let message = 'Gagal memvalidasi PO';

            if (axios.isAxiosError(err)) {
                if (err.response?.data?.message) {
                    message = err.response.data.message;
                }
            }

            error.value = message;
            specification.value = null;
            return {
                valid: false,
                data: null,
                message,
            };
        } finally {
            loading.value = false;
        }
    }

    /**
     * Fetch raw specification data tanpa parsing
     * untuk debugging atau kebutuhan khusus
     *
     * @param poNumber - Nomor PO yang akan dicari
     * @param type - Tipe order (regular atau mmea)
     * @returns Promise dengan raw data dari SIRINE API
     */
    async function fetchRawSpec(
        poNumber: number,
        type: OrderType = 'regular'
    ): Promise<Record<string, unknown> | null> {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Record<string, unknown>>>(
                `/api/specifications/${poNumber}/raw`,
                {
                    params: { type },
                }
            );

            if (response.data.success && response.data.data) {
                return response.data.data;
            }

            error.value = response.data.message || 'Data tidak ditemukan';
            return null;
        } catch (err) {
            if (axios.isAxiosError(err)) {
                error.value = err.response?.data?.message || 'Gagal memuat data';
            } else {
                error.value = 'Terjadi kesalahan tidak terduga';
            }
            return null;
        } finally {
            loading.value = false;
        }
    }

    return {
        // State
        specification,
        loading,
        error,

        // Computed
        hasSpecification,
        hasError,

        // Methods
        reset,
        fetchSpec,
        validatePo,
        fetchRawSpec,
    };
}

/**
 * Type untuk return value dari useSpecification
 * untuk memudahkan type inference di component
 */
export type UseSpecificationReturn = ReturnType<typeof useSpecification>;

