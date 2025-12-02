<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useDebounceFn, useVibrate } from '@vueuse/core';
import {
    ChevronLeft,
    ChevronRight,
    Edit,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    User,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

/**
 * Interface untuk user data dari backend
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
 * Interface untuk pagination links
 */
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

/**
 * Interface untuk paginated users
 */
interface PaginatedUsers {
    data: UserData[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

/**
 * Interface untuk filter options
 */
interface Filters {
    search?: string;
    role?: string;
    status?: string;
}

/**
 * Interface untuk role option
 */
interface RoleOption {
    value: string;
    label: string;
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
    users: PaginatedUsers;
    filters: Filters;
    roles: RoleOption[];
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
 * Local filter state
 */
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');

/**
 * State untuk confirm delete modal
 */
const deleteConfirm = ref<UserData | null>(null);

/**
 * Mount hook untuk trigger entrance animation
 */
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

/**
 * Computed untuk stats
 */
const totalUsers = computed(() => props.users.total);
const adminCount = computed(() => props.users.data.filter((u) => u.role === 'admin').length);

/**
 * Debounced search function
 */
const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

/**
 * Watch search input untuk trigger debounced search
 */
watch(search, () => {
    debouncedSearch();
});

/**
 * Apply filters ke URL
 */
function applyFilters(): void {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: roleFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

/**
 * Handle role filter change
 */
function handleRoleChange(): void {
    vibrate();
    applyFilters();
}

/**
 * Handle status filter change
 */
function handleStatusChange(): void {
    vibrate();
    applyFilters();
}

/**
 * Clear all filters
 */
function clearFilters(): void {
    vibrate();
    search.value = '';
    roleFilter.value = '';
    statusFilter.value = '';
    router.get('/admin/users', {}, { preserveState: true, preserveScroll: true });
}

/**
 * Check if any filter is active
 */
const hasActiveFilters = computed(() => {
    return search.value || roleFilter.value || statusFilter.value;
});

/**
 * Handle delete dengan confirmation
 */
function handleDelete(user: UserData): void {
    vibrate();
    deleteConfirm.value = user;
}

/**
 * Confirm delete action
 */
function confirmDelete(): void {
    if (deleteConfirm.value) {
        vibrate();
        router.delete(`/admin/users/${deleteConfirm.value.id}`, {
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
 * Get role badge classes
 */
function getRoleBadgeClasses(role: string): string {
    if (role === 'admin') {
        return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
    }
    return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
}

/**
 * Get status badge classes
 */
function getStatusBadgeClasses(isActive: boolean): string {
    if (isActive) {
        return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
    }
    return 'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400';
}

/**
 * Haptic feedback untuk button press
 */
function onButtonPress(): void {
    vibrate();
}
</script>

<template>
    <AppLayout title="Kelola User">
        <div class="space-y-6">
            <!-- Header dengan Stats -->
            <div
                class="transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="font-display text-2xl font-bold text-gray-900 dark:text-white">Kelola User</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Kelola akun pengguna sistem label generator
                        </p>
                    </div>
                    <Link
                        href="/admin/users/create"
                        class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-blue-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40 active:scale-[0.97]"
                        @mousedown="onButtonPress"
                    >
                        <Plus class="h-4 w-4" :stroke-width="2.5" />
                        Tambah User
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
                                <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" :stroke-width="2" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total User</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalUsers }}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-xl border border-gray-200/50 bg-white/80 p-4 backdrop-blur-xl dark:border-zinc-700/50 dark:bg-zinc-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30"
                            >
                                <ShieldCheck class="h-5 w-5 text-purple-600 dark:text-purple-400" :stroke-width="2" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Admin</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ adminCount }}</p>
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

            <!-- Filters -->
            <div
                class="rounded-2xl border border-gray-200/50 bg-white/80 p-4 shadow-xl backdrop-blur-xl transition-all delay-100 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                            :stroke-width="2"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari berdasarkan NP..."
                            class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm uppercase transition-all duration-200 placeholder:normal-case focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-500"
                            @focus="onButtonPress"
                        />
                    </div>

                    <!-- Role Filter -->
                    <select
                        v-model="roleFilter"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500"
                        @change="handleRoleChange"
                    >
                        <option value="">Semua Role</option>
                        <option v-for="role in roles" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>

                    <!-- Status Filter -->
                    <select
                        v-model="statusFilter"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-blue-500"
                        @change="handleStatusChange"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>

                    <!-- Clear Filters -->
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-200 dark:hover:bg-zinc-600"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" :stroke-width="2" />
                        Reset
                    </button>
                </div>
            </div>

            <!-- User List -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200/50 bg-white/80 shadow-xl backdrop-blur-xl transition-all delay-150 duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] dark:border-zinc-700/50 dark:bg-zinc-800/80"
                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200/50 dark:border-zinc-700/50">
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Role
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Workstation
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
                                v-for="(user, index) in users.data"
                                :key="user.id"
                                class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] hover:bg-gray-50/50 dark:hover:bg-zinc-700/30"
                                :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                                :style="{ transitionDelay: `${200 + index * 30}ms` }"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full"
                                            :class="[
                                                user.role === 'admin'
                                                    ? 'bg-purple-100 dark:bg-purple-900/30'
                                                    : 'bg-blue-100 dark:bg-blue-900/30',
                                            ]"
                                        >
                                            <User
                                                class="h-5 w-5"
                                                :class="[
                                                    user.role === 'admin'
                                                        ? 'text-purple-600 dark:text-purple-400'
                                                        : 'text-blue-600 dark:text-blue-400',
                                                ]"
                                                :stroke-width="2"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ user.np }}</p>
                                            <p v-if="user.name" class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ user.name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold capitalize"
                                        :class="getRoleBadgeClasses(user.role)"
                                    >
                                        {{ user.role === 'admin' ? 'Administrator' : 'Operator' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-600 dark:text-gray-300">
                                        {{ user.workstation?.name || '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold"
                                        :class="getStatusBadgeClasses(user.is_active)"
                                    >
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-gray-100 hover:text-blue-600 active:scale-95 dark:text-gray-400 dark:hover:bg-zinc-700 dark:hover:text-blue-400"
                                            @mousedown="onButtonPress"
                                        >
                                            <Edit class="h-4 w-4" :stroke-width="2" />
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-red-50 hover:text-red-600 active:scale-95 dark:text-gray-400 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                            @click="handleDelete(user)"
                                        >
                                            <Trash2 class="h-4 w-4" :stroke-width="2" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <Users class="mx-auto h-12 w-12 text-gray-300 dark:text-zinc-600" />
                                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ hasActiveFilters ? 'Tidak ada user yang sesuai filter.' : 'Belum ada user.' }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="users.last_page > 1"
                    class="flex items-center justify-between border-t border-gray-200/50 px-6 py-4 dark:border-zinc-700/50"
                >
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan {{ users.data.length }} dari {{ users.total }} user
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-if="users.prev_page_url"
                            :href="users.prev_page_url"
                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-gray-100 active:scale-95 dark:text-gray-400 dark:hover:bg-zinc-700"
                            @mousedown="onButtonPress"
                        >
                            <ChevronLeft class="h-5 w-5" :stroke-width="2" />
                        </Link>
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            {{ users.current_page }} / {{ users.last_page }}
                        </span>
                        <Link
                            v-if="users.next_page_url"
                            :href="users.next_page_url"
                            class="rounded-lg p-2 text-gray-500 transition-all duration-200 hover:bg-gray-100 active:scale-95 dark:text-gray-400 dark:hover:bg-zinc-700"
                            @mousedown="onButtonPress"
                        >
                            <ChevronRight class="h-5 w-5" :stroke-width="2" />
                        </Link>
                    </div>
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
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Hapus User?</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Apakah Anda yakin ingin menghapus user
                                    <strong class="text-gray-700 dark:text-gray-200">{{ deleteConfirm.np }}</strong
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

