<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useVibrate } from '@vueuse/core';
import { ArrowLeft, Building2, Save } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

/**
 * VueUse useVibrate untuk haptic feedback
 */
const { vibrate } = useVibrate({ pattern: [10] });

/**
 * State untuk entrance animation
 */
const isVisible = ref(false);

/**
 * Form untuk create workstation menggunakan Inertia useForm
 */
const form = useForm({
    name: '',
    is_active: true,
});

/**
 * Mount hook untuk trigger entrance animation
 */
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

/**
 * Handle form submission
 */
function handleSubmit(): void {
    vibrate();
    form.post('/admin/workstations', {
        preserveScroll: true,
    });
}

/**
 * Haptic feedback untuk button press
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <AppLayout title="Tambah Workstation">
        <div class="mx-auto max-w-2xl space-y-6">
            <!-- Header -->
            <div
                class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <Link
                    href="/admin/workstations"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 transition-all duration-200 hover:text-gray-700 active:scale-[0.97] dark:text-gray-400 dark:hover:text-gray-200"
                    @mousedown="onButtonPress"
                >
                    <ArrowLeft class="h-4 w-4" :stroke-width="2" />
                    Kembali ke Daftar Workstation
                </Link>
                <h1 class="mt-4 font-display text-2xl font-bold text-gray-900 dark:text-white">Tambah Workstation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat workstation baru untuk tim produksi</p>
            </div>

            <!-- Form Card -->
            <div
                class="rounded-2xl border border-gray-200/50 bg-white/80 p-6 shadow-xl backdrop-blur-xl transition-all delay-100 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Icon Header -->
                    <div class="flex justify-center">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25"
                        >
                            <Building2 class="h-8 w-8 text-white" :stroke-width="2" />
                        </div>
                    </div>

                    <!-- Name Field -->
                    <div
                        class="transition-all delay-150 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Workstation
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            maxlength="50"
                            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.name,
                            }"
                            placeholder="Contoh: Team 1, Shift Pagi, dll"
                            @focus="onButtonPress"
                        />
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Is Active Field -->
                    <div
                        class="transition-all delay-200 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-5 w-5 rounded border-gray-300 text-blue-500 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20 active:scale-90 dark:border-zinc-600 dark:bg-zinc-700"
                                @change="onButtonPress"
                            />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Aktifkan workstation ini
                            </span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Workstation yang tidak aktif tidak dapat dipilih saat assignment
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div
                        class="flex gap-3 pt-4 transition-all delay-250 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <Link
                            href="/admin/workstations"
                            class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-3 text-center text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-200 dark:hover:bg-zinc-600"
                            @mousedown="onButtonPress"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-linear-to-r from-blue-500 to-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                            @mousedown="onButtonPress"
                        >
                            <Transition
                                mode="out-in"
                                enter-active-class="transition-all duration-200"
                                enter-from-class="opacity-0 scale-75"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition-all duration-150"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                <Save v-else class="h-4 w-4" :stroke-width="2" />
                            </Transition>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

