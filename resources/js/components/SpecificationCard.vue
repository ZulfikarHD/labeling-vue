<script setup lang="ts">
import { useVibrate } from '@vueuse/core';
import {
    AlertCircle,
    Calendar,
    CheckCircle,
    ClipboardList,
    Factory,
    FileText,
    Hash,
    Loader2,
    Package,
    RefreshCw,
    Tag,
} from 'lucide-vue-next';
import { computed } from 'vue';

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
 * Props untuk SpecificationCard component
 * yang menerima data specification dari SIRINE API
 */
const props = withDefaults(
    defineProps<{
        specification?: SpecificationData | null;
        loading?: boolean;
        error?: string | null;
        showRetry?: boolean;
        compact?: boolean;
    }>(),
    {
        specification: null,
        loading: false,
        error: null,
        showRetry: true,
        compact: false,
    },
);

/**
 * Emits untuk retry action
 */
const emit = defineEmits<{
    retry: [];
}>();

/**
 * VueUse useVibrate untuk haptic feedback
 * memberikan tactile response seperti native iOS
 */
const { vibrate } = useVibrate({ pattern: [10] });

/**
 * Handle retry button click dengan haptic feedback
 */
function handleRetry(): void {
    vibrate();
    emit('retry');
}

/**
 * Format tanggal ke format Indonesia
 */
function formatDate(dateString: string | null): string {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
}

/**
 * Format angka dengan separator Indonesia
 */
function formatNumber(value: number | null | undefined): string {
    if (value === null || value === undefined) return '-';
    return value.toLocaleString('id-ID');
}

/**
 * Computed untuk product type label yang lebih readable
 */
const productTypeLabel = computed(() => {
    const type = props.specification?.product_type;
    if (!type) return '-';

    // Map common product types ke label yang lebih deskriptif
    const typeMap: Record<string, string> = {
        P: 'PCHT (Regular)',
        HPTL: 'HPTL (MMEA)',
        MMEA: 'MMEA',
    };

    return typeMap[type] || type;
});

/**
 * Computed untuk status badge styling
 */
const statusBadgeClass = computed(() => {
    const status = props.specification?.status;
    if (!status || status === '-') {
        return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
    }

    // Map status ke warna badge
    if (status.startsWith('ZP')) {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
    }

    return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
});

/**
 * Computed untuk progress percentage
 */
const progressPercentage = computed(() => {
    const spec = props.specification;
    if (!spec || !spec.total_sheets || spec.total_sheets === 0) return 0;

    const completed = spec.verified_good + spec.verified_defect;
    return Math.round((completed / spec.total_sheets) * 100);
});
</script>

<template>
    <!-- Loading State dengan Skeleton Animation -->
    <div
        v-if="loading"
        class="rounded-2xl border border-gray-200/50 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-zinc-700/50 dark:bg-zinc-800/80"
    >
        <div class="animate-pulse space-y-4">
            <!-- Header Skeleton -->
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gray-200 dark:bg-zinc-700"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-32 rounded bg-gray-200 dark:bg-zinc-700"></div>
                    <div class="h-3 w-24 rounded bg-gray-200 dark:bg-zinc-700"></div>
                </div>
            </div>
            <!-- Content Skeleton -->
            <div class="grid grid-cols-2 gap-4">
                <div v-for="i in 6" :key="i" class="space-y-2">
                    <div class="h-3 w-20 rounded bg-gray-200 dark:bg-zinc-700"></div>
                    <div class="h-4 w-28 rounded bg-gray-200 dark:bg-zinc-700"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div
        v-else-if="error"
        class="rounded-2xl border border-red-200/50 bg-red-50/80 p-6 backdrop-blur-sm dark:border-red-900/50 dark:bg-red-900/20"
    >
        <div class="flex flex-col items-center gap-4 text-center">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
            >
                <AlertCircle class="h-6 w-6 text-red-600 dark:text-red-400" :stroke-width="2" />
            </div>
            <div>
                <h3 class="font-medium text-red-900 dark:text-red-300">
                    Gagal Memuat Spesifikasi
                </h3>
                <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                    {{ error }}
                </p>
            </div>
            <button
                v-if="showRetry"
                type="button"
                class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-red-700 active:scale-[0.97]"
                @click="handleRetry"
            >
                <RefreshCw class="h-4 w-4" :stroke-width="2" />
                Coba Lagi
            </button>
        </div>
    </div>

    <!-- Empty State -->
    <div
        v-else-if="!specification"
        class="rounded-2xl border border-gray-200/50 bg-white/80 p-6 backdrop-blur-sm dark:border-zinc-700/50 dark:bg-zinc-800/80"
    >
        <div class="flex flex-col items-center gap-4 py-8 text-center">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-700"
            >
                <FileText class="h-6 w-6 text-gray-400 dark:text-gray-500" :stroke-width="2" />
            </div>
            <div>
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Tidak Ada Data Spesifikasi
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Masukkan nomor PO untuk melihat spesifikasi produk.
                </p>
            </div>
        </div>
    </div>

    <!-- Specification Card - Full View -->
    <div
        v-else-if="!compact"
        class="overflow-hidden rounded-2xl border border-gray-200/50 bg-white/80 shadow-sm backdrop-blur-sm dark:border-zinc-700/50 dark:bg-zinc-800/80"
    >
        <!-- Card Header -->
        <div
            class="flex items-center justify-between border-b border-gray-200/50 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 px-6 py-4 dark:border-zinc-700/50 dark:from-blue-500/10 dark:to-indigo-500/10"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/25"
                >
                    <ClipboardList class="h-5 w-5 text-white" :stroke-width="2" />
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        Spesifikasi Produk
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Data dari SIRINE API
                    </p>
                </div>
            </div>
            <span
                class="rounded-full px-3 py-1 text-xs font-medium"
                :class="statusBadgeClass"
            >
                {{ specification.status || 'Aktif' }}
            </span>
        </div>

        <!-- Card Content -->
        <div class="p-6">
            <!-- Primary Info -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- PO Number -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Hash class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor PO</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ formatNumber(specification.po_number) }}
                        </p>
                    </div>
                </div>

                <!-- OBC Number -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Tag class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nomor OBC</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ specification.obc_number || '-' }}
                        </p>
                    </div>
                </div>

                <!-- Product Type -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Package class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Jenis Produk</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ productTypeLabel }}
                        </p>
                    </div>
                </div>

                <!-- Machine -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Factory class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Mesin</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ specification.machine || '-' }}
                        </p>
                    </div>
                </div>

                <!-- Order Date -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Calendar class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Order</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ formatDate(specification.order_date) }}
                        </p>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-zinc-700"
                    >
                        <Calendar class="h-4 w-4 text-gray-600 dark:text-gray-400" :stroke-width="2" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Jatuh Tempo</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ formatDate(specification.due_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quantities Section -->
            <div class="mb-6 rounded-xl bg-gray-50 p-4 dark:bg-zinc-900/50">
                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Informasi Kuantitas
                </h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatNumber(specification.total_order) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Order</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatNumber(specification.total_sheets) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rencet</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ formatNumber(specification.verified_good) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">HCS Verif</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ formatNumber(specification.verified_defect) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">HCTS Verif</p>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Progress Verifikasi
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ progressPercentage }}%
                    </span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-zinc-700">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-500 ease-out"
                        :style="{ width: `${progressPercentage}%` }"
                    ></div>
                </div>
            </div>

            <!-- Footer Info -->
            <div
                class="flex items-center justify-between border-t border-gray-200/50 pt-4 text-xs text-gray-500 dark:border-zinc-700/50 dark:text-gray-400"
            >
                <div class="flex items-center gap-1">
                    <CheckCircle class="h-3.5 w-3.5 text-green-500" :stroke-width="2" />
                    <span>Data dari SIRINE API</span>
                </div>
                <span>Desain {{ specification.design_year || '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Specification Card - Compact View -->
    <div
        v-else
        class="rounded-xl border border-gray-200/50 bg-white/80 p-4 shadow-sm backdrop-blur-sm dark:border-zinc-700/50 dark:bg-zinc-800/80"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"
                >
                    <ClipboardList class="h-4 w-4 text-blue-600 dark:text-blue-400" :stroke-width="2" />
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        PO {{ formatNumber(specification.po_number) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ specification.obc_number }} • {{ productTypeLabel }}
                    </p>
                </div>
            </div>
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="statusBadgeClass"
            >
                {{ specification.status || 'Aktif' }}
            </span>
        </div>
    </div>
</template>

