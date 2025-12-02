<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useToggle, useVibrate } from '@vueuse/core';
import { ArrowLeft, Eye, EyeOff, Lock, Save, User, UserPlus } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

/**
 * Interface untuk workstation option
 */
interface WorkstationOption {
    id: number;
    name: string;
}

/**
 * Interface untuk role option
 */
interface RoleOption {
    value: string;
    label: string;
}

/**
 * Props dari Inertia controller
 */
defineProps<{
    workstations: WorkstationOption[];
    roles: RoleOption[];
}>();

/**
 * VueUse useVibrate untuk haptic feedback
 */
const { vibrate } = useVibrate({ pattern: [10] });

/**
 * State untuk entrance animation
 */
const isVisible = ref(false);

/**
 * VueUse useToggle untuk password visibility
 */
const [showPassword, togglePassword] = useToggle(false);

/**
 * Form untuk create user menggunakan Inertia useForm
 */
const form = useForm({
    np: '',
    name: '',
    password: '',
    use_default: true,
    role: 'operator',
    workstation_id: '',
    is_active: true,
});

/**
 * Computed untuk default password preview
 */
const defaultPasswordPreview = computed(() => {
    if (form.np) {
        return 'Peruri' + form.np.toUpperCase();
    }
    return 'Peruri[NP]';
});

/**
 * Watch use_default untuk clear password when toggled
 */
watch(
    () => form.use_default,
    (useDefault) => {
        if (useDefault) {
            form.password = '';
        }
    },
);

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
    form.post('/admin/users', {
        preserveScroll: true,
    });
}

/**
 * Toggle password visibility dengan haptic feedback
 */
function togglePasswordVisibility(): void {
    togglePassword();
    vibrate();
}

/**
 * Haptic feedback untuk button press
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <AppLayout title="Tambah User">
        <div class="mx-auto max-w-2xl space-y-6">
            <!-- Header -->
            <div
                class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <Link
                    href="/admin/users"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 transition-all duration-200 hover:text-gray-700 active:scale-[0.97] dark:text-gray-400 dark:hover:text-gray-200"
                    @mousedown="onButtonPress"
                >
                    <ArrowLeft class="h-4 w-4" :stroke-width="2" />
                    Kembali ke Daftar User
                </Link>
                <h1 class="mt-4 font-display text-2xl font-bold text-gray-900 dark:text-white">Tambah User</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat akun pengguna baru untuk sistem</p>
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
                            <UserPlus class="h-8 w-8 text-white" :stroke-width="2" />
                        </div>
                    </div>

                    <!-- NP Field -->
                    <div
                        class="transition-all delay-150 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="np" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            NP (Nomor Pegawai)
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <User class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="np"
                                v-model="form.np"
                                type="text"
                                maxlength="5"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-4 pl-10 uppercase transition-all duration-200 placeholder:normal-case placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.np,
                                }"
                                placeholder="Contoh: 12345"
                                @focus="onButtonPress"
                            />
                        </div>
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.errors.np" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.np }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Name Field -->
                    <div
                        class="transition-all delay-200 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama (Opsional)
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            maxlength="255"
                            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                            placeholder="Nama lengkap"
                            @focus="onButtonPress"
                        />
                    </div>

                    <!-- Default Password Checkbox -->
                    <div
                        class="transition-all delay-250 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.use_default"
                                type="checkbox"
                                class="h-5 w-5 rounded border-gray-300 text-blue-500 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20 active:scale-90 dark:border-zinc-600 dark:bg-zinc-700"
                                @change="onButtonPress"
                            />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Gunakan password default
                            </span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Password default:
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono dark:bg-zinc-700">{{
                                defaultPasswordPreview
                            }}</code>
                        </p>
                    </div>

                    <!-- Password Field (jika tidak default) -->
                    <div
                        v-if="!form.use_default"
                        class="transition-all delay-300 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-12 pl-10 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.password,
                                }"
                                placeholder="Minimal 6 karakter"
                                @focus="onButtonPress"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-all duration-200 hover:text-gray-600 active:scale-90 dark:hover:text-gray-300"
                                @click="togglePasswordVisibility"
                            >
                                <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5" :stroke-width="2" />
                            </button>
                        </div>
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Role Field -->
                    <div
                        class="transition-all delay-350 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Role
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.role,
                            }"
                            @change="onButtonPress"
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.errors.role" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.role }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Workstation Field -->
                    <div
                        class="transition-all delay-[400ms] duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="workstation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Workstation
                        </label>
                        <select
                            id="workstation_id"
                            v-model="form.workstation_id"
                            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.workstation_id,
                            }"
                            @change="onButtonPress"
                        >
                            <option value="">Pilih workstation</option>
                            <option v-for="ws in workstations" :key="ws.id" :value="ws.id">
                                {{ ws.name }}
                            </option>
                        </select>
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.errors.workstation_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.workstation_id }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Is Active Field -->
                    <div
                        class="transition-all delay-[450ms] duration-500"
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
                                Aktifkan user ini
                            </span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            User yang tidak aktif tidak dapat login ke sistem
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div
                        class="flex gap-3 pt-4 transition-all delay-500 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <Link
                            href="/admin/users"
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

