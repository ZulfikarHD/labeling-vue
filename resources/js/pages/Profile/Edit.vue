<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { useToggle, useVibrate } from '@vueuse/core';
import { AlertTriangle, Building2, Eye, EyeOff, Key, Lock, Save, Shield, Trash2, User } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

/**
 * Interface untuk user data
 */
interface UserData {
    id: number;
    np: string;
    name: string | null;
    role: string;
    is_active: boolean;
    workstation: {
        id: number;
        name: string;
    } | null;
}

/**
 * Interface untuk flash messages
 */
interface FlashMessages {
    success?: string;
    error?: string;
}

/**
 * Props dari Inertia controller
 */
const props = defineProps<{
    user: UserData;
    flash?: FlashMessages;
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
const [showCurrent, toggleCurrent] = useToggle(false);
const [showNew, toggleNew] = useToggle(false);
const [showConfirm, toggleConfirm] = useToggle(false);

/**
 * State untuk delete confirmation modal
 */
const showDeleteModal = ref(false);

/**
 * Form untuk update profile
 */
const profileForm = useForm({
    name: props.user.name || '',
});

/**
 * Form untuk update password
 */
const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

/**
 * Form untuk delete account
 */
const deleteForm = useForm({
    password: '',
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
 * Handle profile form submission
 */
function handleProfileSubmit(): void {
    vibrate();
    profileForm.patch('/profile', {
        preserveScroll: true,
    });
}

/**
 * Handle password form submission
 */
function handlePasswordSubmit(): void {
    vibrate();
    passwordForm.put('/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
}

/**
 * Handle delete account
 */
function handleDeleteAccount(): void {
    vibrate();
    deleteForm.delete('/profile', {
        preserveScroll: true,
    });
}

/**
 * Open delete modal
 */
function openDeleteModal(): void {
    vibrate();
    showDeleteModal.value = true;
}

/**
 * Close delete modal
 */
function closeDeleteModal(): void {
    vibrate();
    showDeleteModal.value = false;
    deleteForm.reset();
}

/**
 * Haptic feedback untuk button press
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <AppLayout title="Pengaturan Profile">
        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Header -->
            <div
                class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Profile</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola informasi profile dan keamanan akun Anda
                </p>
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

            <!-- Profile Information Card -->
            <div
                class="rounded-2xl border border-gray-200/50 bg-white/80 p-6 shadow-xl backdrop-blur-xl transition-all delay-100 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex items-center gap-4 border-b border-gray-200/50 pb-6 dark:border-zinc-700/50">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25"
                    >
                        <User class="h-7 w-7 text-white" :stroke-width="2" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Profile</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Data dasar akun Anda</p>
                    </div>
                </div>

                <form @submit.prevent="handleProfileSubmit" class="mt-6 space-y-6">
                    <!-- NP (Readonly) -->
                    <div>
                        <label for="np" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> NP </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Shield class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="np"
                                :value="user.np"
                                type="text"
                                disabled
                                class="block w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 py-3 pr-4 pl-10 text-gray-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-gray-400"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">NP tidak dapat diubah</p>
                    </div>

                    <!-- Role (Readonly) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Role </label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <Key class="h-5 w-5 text-gray-400" :stroke-width="2" />
                                </div>
                                <input
                                    :value="user.role === 'admin' ? 'Administrator' : 'Operator'"
                                    type="text"
                                    disabled
                                    class="block w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 py-3 pr-4 pl-10 capitalize text-gray-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-gray-400"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Workstation
                            </label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <Building2 class="h-5 w-5 text-gray-400" :stroke-width="2" />
                                </div>
                                <input
                                    :value="user.workstation?.name || '-'"
                                    type="text"
                                    disabled
                                    class="block w-full cursor-not-allowed rounded-xl border border-gray-300 bg-gray-50 py-3 pr-4 pl-10 text-gray-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-gray-400"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Name (Editable) -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama
                        </label>
                        <input
                            id="name"
                            v-model="profileForm.name"
                            type="text"
                            maxlength="255"
                            class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                            placeholder="Nama lengkap (opsional)"
                            @focus="onButtonPress"
                        />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="profileForm.processing || !profileForm.isDirty"
                            class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-500 to-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                            @mousedown="onButtonPress"
                        >
                            <Save class="h-4 w-4" :stroke-width="2" />
                            {{ profileForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Update Password Card -->
            <div
                class="rounded-2xl border border-gray-200/50 bg-white/80 p-6 shadow-xl backdrop-blur-xl transition-all delay-150 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex items-center gap-4 border-b border-gray-200/50 pb-6 dark:border-zinc-700/50">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/25"
                    >
                        <Lock class="h-7 w-7 text-white" :stroke-width="2" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ubah Password</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pastikan menggunakan password yang kuat dan unik
                        </p>
                    </div>
                </div>

                <form @submit.prevent="handlePasswordSubmit" class="mt-6 space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label
                            for="current_password"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Password Saat Ini
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                :type="showCurrent ? 'text' : 'password'"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-12 pl-10 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                        passwordForm.errors.current_password,
                                }"
                                placeholder="Masukkan password saat ini"
                                @focus="onButtonPress"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-all duration-200 hover:text-gray-600 active:scale-90 dark:hover:text-gray-300"
                                @click="toggleCurrent()"
                            >
                                <component :is="showCurrent ? EyeOff : Eye" class="h-5 w-5" :stroke-width="2" />
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
                            <p
                                v-if="passwordForm.errors.current_password"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </Transition>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Baru
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="new_password"
                                v-model="passwordForm.new_password"
                                :type="showNew ? 'text' : 'password'"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-12 pl-10 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                        passwordForm.errors.new_password,
                                }"
                                placeholder="Minimal 6 karakter"
                                @focus="onButtonPress"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-all duration-200 hover:text-gray-600 active:scale-90 dark:hover:text-gray-300"
                                @click="toggleNew()"
                            >
                                <component :is="showNew ? EyeOff : Eye" class="h-5 w-5" :stroke-width="2" />
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
                            <p
                                v-if="passwordForm.errors.new_password"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ passwordForm.errors.new_password }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label
                            for="new_password_confirmation"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Lock class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="new_password_confirmation"
                                v-model="passwordForm.new_password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-12 pl-10 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                placeholder="Ulangi password baru"
                                @focus="onButtonPress"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-all duration-200 hover:text-gray-600 active:scale-90 dark:hover:text-gray-300"
                                @click="toggleConfirm()"
                            >
                                <component :is="showConfirm ? EyeOff : Eye" class="h-5 w-5" :stroke-width="2" />
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-amber-500 to-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-amber-600 hover:to-amber-700 hover:shadow-amber-500/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                            @mousedown="onButtonPress"
                        >
                            <Save class="h-4 w-4" :stroke-width="2" />
                            {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Delete Account Card -->
            <div
                class="rounded-2xl border border-red-200/50 bg-red-50/50 p-6 backdrop-blur-xl transition-all delay-200 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-red-900/50 dark:bg-red-900/10"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/30"
                    >
                        <AlertTriangle class="h-7 w-7 text-red-600 dark:text-red-400" :stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-red-900 dark:text-red-200">Hapus Akun</h2>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Menghapus akun akan menghilangkan semua data Anda secara permanen
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition-all duration-200 hover:bg-red-50 active:scale-[0.98] dark:border-red-800 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                        @click="openDeleteModal"
                    >
                        <Trash2 class="h-4 w-4" :stroke-width="2" />
                        Hapus Akun
                    </button>
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
                    v-if="showDeleteModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    @click.self="closeDeleteModal"
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
                            v-if="showDeleteModal"
                            class="w-full max-w-md rounded-2xl border border-gray-200/50 bg-white p-6 shadow-2xl dark:border-zinc-700/50 dark:bg-zinc-800"
                        >
                            <div class="text-center">
                                <div
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"
                                >
                                    <AlertTriangle class="h-7 w-7 text-red-600 dark:text-red-400" :stroke-width="2" />
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                                    Hapus Akun Anda?
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Tindakan ini tidak dapat dibatalkan. Masukkan password Anda untuk mengkonfirmasi.
                                </p>
                            </div>

                            <form @submit.prevent="handleDeleteAccount" class="mt-6 space-y-4">
                                <div>
                                    <label
                                        for="delete_password"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Password
                                    </label>
                                    <input
                                        id="delete_password"
                                        v-model="deleteForm.password"
                                        type="password"
                                        class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 transition-all duration-200 placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-red-500"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                                deleteForm.errors.password,
                                        }"
                                        placeholder="Masukkan password untuk konfirmasi"
                                    />
                                    <Transition
                                        enter-active-class="transition-all duration-300"
                                        enter-from-class="opacity-0 -translate-y-2"
                                        enter-to-class="opacity-100 translate-y-0"
                                        leave-active-class="transition-all duration-200"
                                        leave-from-class="opacity-100"
                                        leave-to-class="opacity-0"
                                    >
                                        <p
                                            v-if="deleteForm.errors.password"
                                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ deleteForm.errors.password }}
                                        </p>
                                    </Transition>
                                </div>

                                <div class="flex gap-3">
                                    <button
                                        type="button"
                                        class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-200 dark:hover:bg-zinc-600"
                                        @click="closeDeleteModal"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="deleteForm.processing || !deleteForm.password"
                                        class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-red-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus Akun' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

