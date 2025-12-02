<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useToggle, useVibrate } from '@vueuse/core';
import { Eye, EyeOff, Lock, Tag, User } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

/**
 * Form login menggunakan Inertia useForm helper
 * dengan NP sebagai identifier dan password
 */
const form = useForm({
    np: '',
    password: '',
    remember: false,
});

/**
 * VueUse useToggle untuk password visibility
 * dengan cleaner API dibanding manual ref toggle
 */
const [showPassword, togglePassword] = useToggle(false);

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
 * Computed property untuk current year
 * yang digunakan pada footer copyright
 */
const currentYear = computed(() => new Date().getFullYear());

/**
 * Mount hook untuk trigger entrance animation
 * dengan slight delay untuk spring physics effect
 */
onMounted(() => {
    // Staggered animation delay seperti iOS
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

/**
 * Handle form submission dengan validasi
 * dan redirect ke dashboard saat sukses
 */
function handleSubmit(): void {
    // Haptic feedback saat submit
    vibrate();

    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
}

/**
 * Toggle password visibility dengan haptic feedback
 * untuk memberikan tactile response seperti native iOS
 */
function togglePasswordVisibility(): void {
    togglePassword();
    vibrate();
}

/**
 * Haptic feedback untuk button press
 * mengikuti iOS press feedback pattern
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <Head title="Masuk" />

    <div class="flex min-h-screen flex-col bg-gray-50 font-sans antialiased dark:bg-zinc-900">
        <!-- Main Content -->
        <main class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <!-- Login Card dengan Spring Animation -->
            <div
                class="w-full max-w-md space-y-8 rounded-2xl border border-gray-200/50 bg-white/80 p-8 shadow-xl backdrop-blur-xl transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[
                    isVisible
                        ? 'translate-y-0 scale-100 opacity-100'
                        : 'translate-y-8 scale-95 opacity-0',
                ]"
            >
                <!-- Logo dan Header dengan Staggered Animation -->
                <div class="text-center">
                    <div class="flex justify-center">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:scale-105 active:scale-95"
                        >
                            <Tag class="h-8 w-8 text-white" :stroke-width="2" />
                        </div>
                    </div>
                    <h1
                        class="mt-6 font-display text-2xl font-bold tracking-tight text-gray-900 transition-all delay-100 duration-500 dark:text-white"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        Label Generator
                    </h1>
                    <p
                        class="mt-2 text-sm text-gray-500 transition-all delay-150 duration-500 dark:text-gray-400"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        Masukkan NP dan password untuk melanjutkan
                    </p>
                </div>

                <!-- Login Form dengan Staggered Field Animation -->
                <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                    <!-- NP Input -->
                    <div
                        class="transition-all delay-200 duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <label for="np" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            NP
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <User class="h-5 w-5 text-gray-400" :stroke-width="2" />
                            </div>
                            <input
                                id="np"
                                v-model="form.np"
                                name="np"
                                type="text"
                                maxlength="5"
                                autocomplete="username"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-3 pl-10 uppercase transition-all duration-200 placeholder:normal-case placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.np,
                                }"
                                placeholder="Nomor Pegawai"
                                @focus="onButtonPress"
                            />
                        </div>
                        <!-- Error Message dengan Slide Animation -->
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <p v-if="form.errors.np" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.np }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Password Input -->
                    <div
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
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="block w-full rounded-xl border border-gray-300 bg-white py-3 pr-12 pl-10 transition-all duration-200 placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.password,
                                }"
                                placeholder="Masukkan password"
                                @focus="onButtonPress"
                            />
                            <!-- Password Toggle dengan Press Feedback -->
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-all duration-200 hover:text-gray-600 active:scale-90 dark:hover:text-gray-300"
                                @click="togglePasswordVisibility"
                            >
                                <Transition
                                    mode="out-in"
                                    enter-active-class="transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                                    enter-from-class="opacity-0 scale-75 rotate-12"
                                    enter-to-class="opacity-100 scale-100 rotate-0"
                                    leave-active-class="transition-all duration-150"
                                    leave-from-class="opacity-100 scale-100 rotate-0"
                                    leave-to-class="opacity-0 scale-75 -rotate-12"
                                >
                                    <Eye v-if="!showPassword" class="h-5 w-5" :stroke-width="2" />
                                    <EyeOff v-else class="h-5 w-5" :stroke-width="2" />
                                </Transition>
                            </button>
                        </div>
                        <!-- Error Message dengan Slide Animation -->
                        <Transition
                            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password }}
                            </p>
                        </Transition>
                    </div>

                    <!-- Remember Me dengan Press Feedback -->
                    <div
                        class="flex items-center transition-all delay-[350ms] duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <input
                            id="remember"
                            v-model="form.remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-500 transition-all duration-200 focus:ring-2 focus:ring-blue-500/20 active:scale-90 dark:border-zinc-600 dark:bg-zinc-700"
                            @change="onButtonPress"
                        />
                        <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit Button dengan Press Feedback -->
                    <div
                        class="transition-all delay-[400ms] duration-500"
                        :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full justify-center rounded-xl bg-linear-to-r from-blue-500 to-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.97] disabled:cursor-not-allowed disabled:opacity-70 dark:focus:ring-offset-zinc-800"
                            @mousedown="onButtonPress"
                        >
                            <!-- Loading Spinner dengan Spring Animation -->
                            <Transition
                                enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                                enter-from-class="opacity-0 scale-75"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition-all duration-200"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-75"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="mr-2 h-5 w-5 animate-spin text-white"
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
                            </Transition>
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer dengan Glass Effect -->
        <footer
            class="border-t border-gray-200/50 bg-white/80 backdrop-blur-xl transition-all delay-500 duration-500 dark:border-zinc-700/50 dark:bg-zinc-900/80"
            :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ currentYear }} Label Generator. Developed by Zulfikar Hidayatullah.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
