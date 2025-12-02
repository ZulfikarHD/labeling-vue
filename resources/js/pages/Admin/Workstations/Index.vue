<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useVibrate } from '@vueuse/core';
import { Building2, Edit, Plus, ToggleLeft, ToggleRight, Trash2, Users } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

/**
 * Interface untuk workstation data dari backend
 */
interface Workstation {
    id: number;
    name: string;
    is_active: boolean;
    users_count: number;
    created_at: string;
    updated_at: string;
}

/**
 * Interface untuk flash messages dari session
 */
interface FlashMessages {
    success?: string;
    error?: string;
}

/**
 * Props dari Inertia controller
 */
const props = defineProps<{
    workstations: Workstation[];
    flash?: FlashMessages;
}>();

/**
 * VueUse useVibrate untuk haptic feedback
 * memberikan tactile response seperti native iOS
 */
const { vibrate } = useVibrate({ pattern: [10] });

/**
 * State untuk entrance animation
 * dengan staggered timing seperti iOS
 */
const isVisible = ref(false);

/**
 * State untuk confirm delete modal
 */
const deleteConfirm = ref<Workstation | null>(null);

/**
 * Mount hook untuk trigger entrance animation
 */
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

/**
 * Computed untuk total workstations
 */
const totalWorkstations = computed(() => props.workstations.length);

/**
 * Computed untuk active workstations
 */
const activeWorkstations = computed(() => props.workstations.filter((w) => w.is_active).length);

/**
 * Handle toggle active dengan haptic feedback
 */
function handleToggleActive(workstation: Workstation): void {
    vibrate();
    router.patch(
        `/admin/workstations/${workstation.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
}

/**
 * Handle delete dengan confirmation
 */
function handleDelete(workstation: Workstation): void {
    vibrate();
    deleteConfirm.value = workstation;
}

/**
 * Confirm delete action
 */
function confirmDelete(): void {
    if (deleteConfirm.value) {
        vibrate();
        router.delete(`/admin/workstations/${deleteConfirm.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                deleteConfirm.value = null;
            },
        });
    }
}

/**
 * Cancel delete action
 */
function cancelDelete(): void {
    vibrate();
    deleteConfirm.value = null;
}

/**
 * Haptic feedback untuk button press
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <AppLayout title="Kelola Workstation">
        <div class="space-y-6">
            <!-- Header dengan Stats -->
            <div
                class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-white">Kelola Workstation</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Kelola tim dan stasiun kerja produksi
                        </p>
                    </div>
                    <Link
                        href="/admin/workstations/create"
                        class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40 active:scale-[0.97]"
                        @mousedown="onButtonPress"
                    >
                        <Plus class="h-4 w-4" :stroke-width="2.5" />
                        Tambah Workstation
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div
                        class="rounded-xl border border-gray-200/50 bg-white/80 p-4 backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30"
                            >
                                <Building2 class="h-5 w-5 text-blue-600 dark:text-blue-400" :stroke-width="2" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalWorkstations }}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-gray-200/50 bg-white/80 p-4 backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30"
                            >
                                <ToggleRight class="h-5 w-5 text-green-600 dark:text-green-400" :stroke-width="2" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Aktif</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ activeWorkstations }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <Transition
                enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flash?.success"
                    class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
                >
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ flash.success }}</p>
                </div>
            </Transition>
            <Transition
                enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flash?.error"
                    class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
                >
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ flash.error }}</p>
                </div>
            </Transition>

            <!-- Workstation List -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200/50 bg-white/80 shadow-xl backdrop-blur-xl transition-all delay-100 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200/50 dark:border-zinc-700/50">
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Nama
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Jumlah User
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/50 dark:divide-zinc-700/50">
                            <tr
                                v-for="(workstation, index) in workstations"
                                :key="workstation.id"
                                class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-50/50 dark:hover:bg-zinc-700/30"
                                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                                :style="{ transitionDelay: `${150 + index * 50}ms` }"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-zinc-700"
                                        >
                                            <Building2
                                                class="h-5 w-5 text-gray-600 dark:text-gray-300"
                                                :stroke-width="2"
                                            />
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ workstation.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                        <Users class="h-4 w-4" :stroke-width="2" />
                                        <span>{{ workstation.users_count }} user</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 active:scale-95"
                                        :class="[
                                            workstation.is_active
                                                ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50'
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-zinc-700 dark:text-gray-400 dark:hover:bg-zinc-600',
                                        ]"
                                        @click="handleToggleActive(workstation)"
                                    >
                                        <component
                                            :is="workstation.is_active ? ToggleRight : ToggleLeft"
                                            class="h-4 w-4"
                                            :stroke-width="2"
                                        />
                                        {{ workstation.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/workstations/${workstation.id}/edit`"
                                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-gray-100 hover:text-blue-600 active:scale-95 dark:text-gray-400 dark:hover:bg-zinc-700 dark:hover:text-blue-400"
                                            @mousedown="onButtonPress"
                                        >
                                            <Edit class="h-4 w-4" :stroke-width="2" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-red-50 hover:text-red-600 active:scale-95 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                            @click="handleDelete(workstation)"
                                        >
                                            <Trash2 class="h-4 w-4" :stroke-width="2" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="workstations.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <Building2 class="mx-auto h-12 w-12 text-gray-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada workstation. Klik tombol "Tambah Workstation" untuk membuat.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="deleteConfirm"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    @click.self="cancelDelete"
                >
                    <Transition
                        enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition-all duration-200"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="deleteConfirm"
                            class="w-full max-w-md rounded-2xl border border-gray-200/50 bg-white p-6 shadow-2xl dark:border-zinc-700/50 dark:bg-zinc-800"
                        >
                            <div class="text-center">
                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                                >
                                    <Trash2 class="h-7 w-7 text-red-600 dark:text-red-400" :stroke-width="2" />
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                                    Hapus Workstation?
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Apakah Anda yakin ingin menghapus workstation
                                    <strong class="text-gray-700 dark:text-gray-200">{{ deleteConfirm.name }}</strong
                                    >? Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                            <div class="mt-6 flex gap-3">
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-200 dark:hover:bg-zinc-600"
                                    @click="cancelDelete"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-red-700 active:scale-[0.98]"
                                    @click="confirmDelete"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

