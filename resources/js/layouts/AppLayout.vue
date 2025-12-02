<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Menu, Tag, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
 * State untuk mobile menu visibility
 * dengan spring animation saat toggle
 */
const isMobileMenuOpen = ref(false);

/**
 * Toggle mobile menu dengan haptic feedback
 * untuk memberikan tactile response seperti native iOS
 */
function toggleMobileMenu(): void {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;

    // Haptic feedback jika tersedia
    if ('vibrate' in navigator) {
        navigator.vibrate(10);
    }
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
                    <!-- Logo dan Brand -->
                    <div class="flex items-center gap-3">
                        <Link
                            href="/"
                            class="flex items-center gap-2 transition-transform duration-200 active:scale-[0.97]"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25"
                            >
                                <Tag class="h-5 w-5 text-white" :stroke-width="2" />
                            </div>
                            <span class="font-display text-lg font-semibold text-gray-900 dark:text-white">
                                Label Generator
                            </span>
                        </Link>
                    </div>

                    <!-- Navigation Desktop -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                        >
                            Orders
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 active:scale-[0.97] dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                        >
                            Labels
                        </Link>
                    </nav>

                    <!-- Mobile Menu Button -->
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-gray-100 active:scale-[0.97] md:hidden dark:text-gray-400 dark:hover:bg-zinc-800"
                        @click="toggleMobileMenu"
                    >
                        <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" :stroke-width="2" />
                        <X v-else class="h-6 w-6" :stroke-width="2" />
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
                    <nav class="flex flex-col gap-1">
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                        >
                            Orders
                        </Link>
                        <Link
                            href="/"
                            class="rounded-lg px-4 py-3 text-sm font-medium text-gray-600 transition-all duration-200 hover:bg-gray-100 active:scale-[0.98] dark:text-gray-300 dark:hover:bg-zinc-800"
                        >
                            Labels
                        </Link>
                    </nav>
                </div>
            </Transition>
        </header>

        <!-- Main Content Area dengan Staggered Animation -->
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

<style scoped>
/**
 * Custom animation untuk spring physics effect
 * yang memberikan feel native iOS
 */
@keyframes spring-bounce {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }
    50% {
        transform: scale(1.02);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.spring-enter {
    animation: spring-bounce 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
</style>

