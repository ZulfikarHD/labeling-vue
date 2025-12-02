<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { onClickOutside, useToggle, useVibrate } from '@vueuse/core';
import { LogOut, Menu, Tag, User, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

/**
 * Interface untuk user data dari shared props
 */
interface AuthUser {
    id: number;
    np: string;
    name: string | null;
    role: string;
}

/**
 * Props untuk AppLayout component
 * yang menyediakan konfigurasi layout dasar aplikasi
 */
withDefaults(
    defineProps<{
        title?: string;
        showHeader?: boolean;
        showFooter?: boolean;
    }>(),
    {
        title: 'Label Generator',
        showHeader: true,
        showFooter: true,
    },
);

/**
 * VueUse useToggle untuk mobile menu visibility
 * dengan cleaner API dibanding manual ref toggle
 */
const [isMobileMenuOpen, toggleMobileMenu] = useToggle(false);

/**
 * VueUse useToggle untuk user dropdown visibility
 */
const [isUserMenuOpen, toggleUserMenu] = useToggle(false);

/**
 * Ref untuk user menu dropdown element
 * digunakan untuk onClickOutside detection
 */
const userMenuRef = ref<HTMLElement | null>(null);

/**
 * VueUse onClickOutside untuk close user menu
 * saat klik di luar dropdown
 */
onClickOutside(userMenuRef, () => {
    if (isUserMenuOpen.value) {
        isUserMenuOpen.value = false;
    }
});

/**
 * VueUse useVibrate untuk haptic feedback
 * memberikan tactile response seperti native iOS
 */
const { vibrate } = useVibrate({ pattern: [10] });

/**
 * Haptic feedback pattern untuk logout confirmation
 */
const { vibrate: vibrateConfirm } = useVibrate({ pattern: [10, 50, 10] });

/**
 * Computed property untuk user data dari Inertia shared props
 * yang berisi informasi user yang sedang login
 */
const user = computed(() => {
    const page = usePage();
    return page.props.auth?.user as AuthUser | null;
});

/**
 * Form untuk logout request
 * menggunakan POST method untuk security
 */
const logoutForm = useForm({});

/**
 * Handle mobile menu toggle dengan haptic feedback
 * untuk memberikan tactile response seperti native iOS
 */
function handleMobileMenuToggle(): void {
    toggleMobileMenu();
    vibrate();
}

/**
 * Handle user menu toggle dengan haptic feedback
 */
function handleUserMenuToggle(): void {
    toggleUserMenu();
    vibrate();
}

/**
 * Handle logout dengan POST request
 * dan haptic feedback untuk confirmation
 */
function handleLogout(): void {
    vibrateConfirm();
    logoutForm.post('/logout');
}

/**
 * Haptic feedback untuk button press
 * mengikuti iOS press feedback pattern
 */
function onButtonPress(): void {
    vibrate();
}

/**
 * Computed property untuk current year
 * yang digunakan pada footer copyright
 */
const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-gray-50 font-sans antialiased dark:bg-zinc-900">
        <!-- Header dengan Glass Effect -->
        <header
            v-if="showHeader"
            class="fixed top-0 right-0 left-0 z-50 border-b border-gray-200/50 bg-white/80 backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-900/80"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo dan Brand dengan Press Feedback -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="flex items-center gap-2 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] active:scale-[0.97]"
                            @mousedown="onButtonPress"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25 transition-transform duration-200 active:scale-95"
                            >
                                <Tag class="h-5 w-5 text-white" :stroke-width="2" />
                            </div>
                            <span class="font-display text-lg font-semibold text-gray-900 dark:text-white">
                                Label Generator
                            </span>
                        </Link>
                    </div>

                    <!-- Navigation Desktop dengan Press Feedback -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                            @mousedown="onButtonPress"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                            @mousedown="onButtonPress"
                        >
                            Orders
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                            @mousedown="onButtonPress"
                        >
                            Labels
                        </Link>
                    </nav>

                    <!-- User Menu Desktop dengan VueUse onClickOutside -->
                    <div class="hidden items-center gap-3 md:flex">
                        <div v-if="user" ref="userMenuRef" class="relative">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800"
                                @click="handleUserMenuToggle"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 transition-transform duration-200 active:scale-95 dark:bg-zinc-700"
                                >
                                    <User class="h-4 w-4 text-gray-600 dark:text-gray-300" :stroke-width="2" />
                                </div>
                                <span>{{ user.np }}</span>
                            </button>

                            <!-- User Dropdown dengan Spring Animation -->
                            <Transition
                                enter-active-class="transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                                enter-from-class="opacity-0 scale-95 -translate-y-2"
                                enter-to-class="opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="opacity-100 scale-100 translate-y-0"
                                leave-to-class="opacity-0 scale-95 -translate-y-2"
                            >
                                <div
                                    v-if="isUserMenuOpen"
                                    class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl border border-gray-200/50 bg-white/95 p-1 shadow-lg backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-800/95"
                                >
                                    <div class="border-b border-gray-200/50 px-3 py-2 dark:border-zinc-700/50">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.np }}</p>
                                        <p class="text-xs text-gray-500 capitalize dark:text-gray-400">
                                            {{ user.role }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-red-50 active:scale-[0.98] dark:text-red-400 dark:hover:bg-red-900/20"
                                        :disabled="logoutForm.processing"
                                        @click="handleLogout"
                                    >
                                        <LogOut class="h-4 w-4" :stroke-width="2" />
                                        {{ logoutForm.processing ? 'Keluar...' : 'Keluar' }}
                                    </button>
                                </div>
                            </Transition>
                        </div>
                        <Link
                            v-else
                            href="/login"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-blue-600 active:scale-[0.97]"
                            @mousedown="onButtonPress"
                        >
                            Masuk
                        </Link>
                    </div>

                    <!-- Mobile Menu Button dengan Spring Animation -->
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 active:scale-[0.97] md:hidden dark:text-gray-400 dark:hover:bg-zinc-800"
                        @click="handleMobileMenuToggle"
                    >
                        <Transition
                            mode="out-in"
                            enter-active-class="transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                            enter-from-class="opacity-0 scale-75 rotate-90"
                            enter-to-class="opacity-100 scale-100 rotate-0"
                            leave-active-class="transition-all duration-150"
                            leave-from-class="opacity-100 scale-100 rotate-0"
                            leave-to-class="opacity-0 scale-75 -rotate-90"
                        >
                            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" :stroke-width="2" />
                            <X v-else class="h-6 w-6" :stroke-width="2" />
                        </Transition>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu dengan Spring Animation -->
            <Transition
                enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div
                    v-if="isMobileMenuOpen"
                    class="border-t border-gray-200/50 bg-white/95 px-4 py-3 backdrop-blur-xl md:hidden dark:border-zinc-700/50 dark:bg-zinc-900/95"
                >
                    <!-- User Info Mobile -->
                    <div v-if="user" class="mb-3 border-b border-gray-200/50 pb-3 dark:border-zinc-700/50">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-zinc-700"
                            >
                                <User class="h-5 w-5 text-gray-600 dark:text-gray-300" :stroke-width="2" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ user.np }}</p>
                                <p class="text-sm text-gray-500 capitalize dark:text-gray-400">{{ user.role }}</p>
                            </div>
                        </div>
                    </div>

                    <nav class="flex flex-col gap-1">
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                            @mousedown="onButtonPress"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                            @mousedown="onButtonPress"
                        >
                            Orders
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                            @mousedown="onButtonPress"
                        >
                            Labels
                        </Link>

                        <!-- Logout Button Mobile -->
                        <button
                            v-if="user"
                            type="button"
                            class="mt-2 flex items-center gap-2 rounded-lg border-t border-gray-200/50 px-4 py-3 text-sm font-medium text-red-600 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-red-50 active:scale-[0.98] dark:border-zinc-700/50 dark:text-red-400 dark:hover:bg-red-900/20"
                            :disabled="logoutForm.processing"
                            @click="handleLogout"
                        >
                            <LogOut class="h-4 w-4" :stroke-width="2" />
                            {{ logoutForm.processing ? 'Keluar...' : 'Keluar' }}
                        </button>

                        <!-- Login Link Mobile -->
                        <Link
                            v-else
                            href="/login"
                            class="mt-2 rounded-lg bg-blue-500 px-4 py-3 text-center text-sm font-medium text-white transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-blue-600 active:scale-[0.98]"
                            @mousedown="onButtonPress"
                        >
                            Masuk
                        </Link>
                    </nav>
                </div>
            </Transition>
        </header>

        <!-- Main Content Area -->
        <main :class="[showHeader ? 'pt-16' : '', showFooter ? 'pb-20' : '', 'min-h-screen']">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <!-- Footer dengan Glass Effect -->
        <footer
            v-if="showFooter"
            class="fixed right-0 bottom-0 left-0 z-40 border-t border-gray-200/50 bg-white/80 backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-900/80"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ currentYear }} Label Generator. Developed by Zulfikar Hidayatullah.
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">v1.0.0</p>
                </div>
            </div>
        </footer>
    </div>
</template>
