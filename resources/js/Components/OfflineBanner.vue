<template>
    <Transition name="slide-down">
        <div v-if="shouldShow" class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-[#f4f0bb] to-[#87c38f] border-b border-[#226f54]/20 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <!-- Status Icon -->
                        <div class="flex-shrink-0">
                            <svg v-if="!isOnline" class="w-5 h-5 text-[#da2c38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L5.636 18.364m12.728 0L5.636 5.636"></path>
                            </svg>
                            <svg v-else-if="syncInProgress" class="w-5 h-5 text-[#226f54] animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg v-else-if="offlineTransactionCount > 0" class="w-5 h-5 text-[#226f54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 text-[#87c38f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <!-- Status Message -->
                        <div class="flex-1">
                            <p class="text-sm font-medium text-[#43291f]">
                                <span v-if="!isOnline">You're offline</span>
                                <span v-else-if="syncInProgress">Syncing data...</span>
                                <span v-else-if="offlineTransactionCount > 0">
                                    {{ offlineTransactionCount }} transaction{{ offlineTransactionCount === 1 ? '' : 's' }} pending sync
                                </span>
                                <span v-else>All data synced</span>
                            </p>
                            <p v-if="!isOnline" class="text-xs text-[#43291f]/70">
                                You can still add transactions. They'll sync when you're back online.
                            </p>
                            <p v-else-if="syncStatus.syncError" class="text-xs text-[#da2c38]">
                                Sync failed: {{ syncStatus.syncError }}
                            </p>
                            <p v-else-if="syncStatus.lastSync" class="text-xs text-[#43291f]/70">
                                Last synced: {{ formatTime(syncStatus.lastSync) }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <!-- Sync Button -->
                        <button
                            v-if="isOnline && offlineTransactionCount > 0 && !syncInProgress"
                            @click="triggerSync"
                            class="px-3 py-1 text-xs font-medium bg-[#226f54] text-white rounded-md hover:bg-[#1a5440] transition-colors"
                        >
                            Sync Now
                        </button>

                        <!-- View Offline Data Button -->
                        <button
                            v-if="offlineTransactionCount > 0"
                            @click="showOfflineData = true"
                            class="px-3 py-1 text-xs font-medium bg-white/50 text-[#43291f] rounded-md hover:bg-white/70 transition-colors"
                        >
                            View
                        </button>

                        <!-- Dismiss Button -->
                        <button
                            v-if="isOnline && offlineTransactionCount === 0"
                            @click="dismiss"
                            class="p-1 text-[#43291f]/70 hover:text-[#43291f] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div v-if="syncInProgress" class="h-1 bg-[#226f54]/20 rounded-full overflow-hidden">
                    <div class="h-full bg-[#226f54] rounded-full animate-pulse" style="width: 60%"></div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Offline Data Modal -->
    <div v-if="showOfflineData" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Offline Data</h2>
                <button @click="showOfflineData = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-96">
                <div v-if="offlineTransactions.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No offline data</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="transaction in offlineTransactions" :key="transaction.id"
                         class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="transaction.data.type === 'income' ? 'bg-[#87c38f]/20 text-[#87c38f]' : 'bg-[#da2c38]/20 text-[#da2c38]'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="transaction.data.type === 'income'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ transaction.data.type === 'income' ? '+' : '-' }}${{ formatCurrency(transaction.data.amount) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ formatTime(new Date(transaction.timestamp)) }}</p>
                                <p v-if="transaction.data.description" class="text-xs text-gray-600">{{ transaction.data.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span v-if="transaction.synced" class="text-xs text-[#87c38f]">✓ Synced</span>
                            <span v-else class="text-xs text-[#f4f0bb] bg-[#43291f] px-2 py-1 rounded">Pending</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <p class="text-sm text-gray-600">
                        {{ offlineTransactions.filter(t => !t.synced).length }} pending sync
                    </p>
                    <div class="flex gap-3">
                        <button @click="showOfflineData = false"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                        <button v-if="isOnline && !syncInProgress" @click="triggerSync"
                                class="px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors">
                            Sync All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOfflineStore } from '@/composables/useOfflineStore.js'

const {
    isOnline,
    syncInProgress,
    offlineTransactionCount,
    showOfflineBanner,
    syncStatus,
    triggerSync,
    getOfflineTransactions
} = useOfflineStore()

const dismissed = ref(false)
const showOfflineData = ref(false)
const offlineTransactions = ref([])

const shouldShow = computed(() => {
    return !dismissed.value && (
        !isOnline.value ||
        offlineTransactionCount.value > 0 ||
        syncInProgress.value ||
        showOfflineBanner.value
    )
})

const dismiss = () => {
    dismissed.value = true
    // Auto-show again if offline count changes
    setTimeout(() => {
        if (offlineTransactionCount.value > 0 || !isOnline.value) {
            dismissed.value = false
        }
    }, 5000)
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatTime = (date) => {
    return new Intl.RelativeTimeFormat('en', { numeric: 'auto' }).format(
        Math.round((date.getTime() - Date.now()) / (1000 * 60)),
        'minute'
    )
}

// Load offline transactions when modal opens
const loadOfflineTransactions = async () => {
    try {
        offlineTransactions.value = await getOfflineTransactions()
    } catch (error) {
        console.error('Failed to load offline transactions:', error)
    }
}

// Watch for modal opening
const originalShowOfflineData = showOfflineData.value
$: {
    if (showOfflineData.value && !originalShowOfflineData) {
        loadOfflineTransactions()
    }
}

onMounted(() => {
    // Reset dismissed state when offline count changes
    $: {
        if (offlineTransactionCount.value > 0) {
            dismissed.value = false
        }
    }
})
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from {
    transform: translateY(-100%);
    opacity: 0;
}

.slide-down-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}
</style>
