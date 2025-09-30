<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Offline Banner -->
        <OfflineBanner />

        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200" :class="{ 'mt-16': shouldShowOfflineBanner }">
            <!-- Rest of your existing navigation code stays the same -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Mobile menu button -->
                    <button
                        @click="showMobileMenu = !showMobileMenu"
                        class="sm:hidden px-6 py-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#226f54] focus:ring-offset-2"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <!-- Logo and Main Navigation -->
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-2">
                                <ApplicationLogo
                                    class="block h-9 w-auto"
                                />
                            </Link>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                :href="route('dashboard')"
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200',
                                    route().current('dashboard')
                                        ? 'border-[#226f54] text-[#226f54]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Dashboard
                            </Link>
                            <Link
                                :href="route('transactions.index')"
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200',
                                    route().current('transactions.*')
                                        ? 'border-[#226f54] text-[#226f54]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Transactions
                            </Link>
                            <Link
                                :href="route('categories.index')"
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200',
                                    route().current('categories.*')
                                        ? 'border-[#226f54] text-[#226f54]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Categories
                            </Link>
                            <Link
                                :href="route('reports.index')"
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200',
                                    route().current('reports.*')
                                        ? 'border-[#226f54] text-[#226f54]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Reports
                            </Link>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center">
                        <!-- Offline Indicator -->
                        <div v-if="shouldShowOfflineIndicator" class="mr-2 md:mr-4">
                            <div class="flex items-center gap-2 px-3 py-1 bg-[#f4f0bb] rounded-full">
                                <div class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-[#87c38f]' : 'bg-[#da2c38]'"></div>
                                <span class="text-xs font-medium text-[#43291f]">
                                    {{ isOnline ? `${offlineTransactionCount} pending` : 'Offline' }}
                                </span>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="w-8 h-8 bg-[#226f54] rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700">
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-show="showUserMenu"
                                @click.away="showUserMenu = false"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            >
                                <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-100">
                                    {{ $page.props.auth.user.email }}
                                </div>
                                <Link :href="route('dashboard')"
                                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    Dashboard
                                </Link>
                                <div class="border-t border-gray-100 my-1"></div>
                                <Link :href="route('logout')" method="post" as="button"
                                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    Sign Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div v-show="showMobileMenu" class="sm:hidden border-t border-gray-200">
                <div class="pt-2 pb-3 space-y-1">
                    <Link
                        :href="route('dashboard')"
                        :class="[
                            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200',
                            route().current('dashboard')
                                ? 'border-[#226f54] text-[#226f54] bg-[#226f54]/5'
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        ]"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="route('transactions.index')"
                        :class="[
                            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200',
                            route().current('transactions.*')
                                ? 'border-[#226f54] text-[#226f54] bg-[#226f54]/5'
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        ]"
                    >
                        Transactions
                    </Link>
                    <Link
                        :href="route('categories.index')"
                        :class="[
                            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200',
                            route().current('categories.*')
                                ? 'border-[#226f54] text-[#226f54] bg-[#226f54]/5'
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        ]"
                    >
                        Categories
                    </Link>
                    <Link
                        :href="route('reports.index')"
                        :class="[
                            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200',
                            route().current('reports.*')
                                ? 'border-[#226f54] text-[#226f54] bg-[#226f54]/5'
                                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                        ]"
                    >
                        Reports
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
             class="fixed bottom-4 right-4 bg-[#87c38f] text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ $page.props.flash.success }}
            </div>
        </div>

        <div v-if="$page.props.flash?.error"
             class="fixed bottom-4 right-4 bg-[#da2c38] text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                {{ $page.props.flash.error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import OfflineBanner from '@/Components/OfflineBanner.vue'
import { useOfflineStore } from '@/composables/useOfflineStore.js'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    title: String
})

const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const { isOnline, offlineTransactionCount, shouldShowOfflineIndicator } = useOfflineStore()

const shouldShowOfflineBanner = computed(() => {
    return !isOnline.value || offlineTransactionCount.value > 0
})

onMounted(() => {
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            showUserMenu.value = false
        }
    })
})
</script>
