// resources/js/composables/useOfflineStore.js
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import offlineManager from '@/OfflineManager.js'

export function useOfflineStore() {
    const isOnline = ref(navigator.onLine)
    const syncInProgress = ref(false)
    const offlineTransactionCount = ref(0)
    const showOfflineBanner = ref(false)
    const syncStatus = reactive({
        lastSync: null,
        pendingSync: false,
        syncError: null
    })

    // Event handlers
    const handleOnline = () => {
        isOnline.value = true
        showOfflineBanner.value = false
        syncStatus.pendingSync = true
        updateOfflineCount()
    }

    const handleOffline = () => {
        isOnline.value = false
        showOfflineBanner.value = true
    }

    const handleSyncComplete = () => {
        syncInProgress.value = false
        syncStatus.pendingSync = false
        syncStatus.lastSync = new Date()
        syncStatus.syncError = null
        updateOfflineCount()
    }

    const updateOfflineCount = async () => {
        try {
            const count = await offlineManager.getOfflineTransactionCount()
            offlineTransactionCount.value = count
        } catch (error) {
            console.error('Failed to get offline transaction count:', error)
            // Set to 0 on error to prevent UI issues
            offlineTransactionCount.value = 0
        }
    }

    // Store transaction offline
    const storeTransactionOffline = async (transactionData) => {
        try {
            const result = await offlineManager.storeTransactionOffline(transactionData)
            await updateOfflineCount()
            return result
        } catch (error) {
            console.error('Failed to store transaction offline:', error)
            throw error
        }
    }

    // Store category offline
    const storeCategoryOffline = async (categoryData) => {
        try {
            const result = await offlineManager.storeCategoryOffline(categoryData)
            return result
        } catch (error) {
            console.error('Failed to store category offline:', error)
            throw error
        }
    }

    // Manual sync trigger
    const triggerSync = async () => {
        if (!isOnline.value || syncInProgress.value) return

        syncInProgress.value = true
        syncStatus.pendingSync = true
        syncStatus.syncError = null

        try {
            await offlineManager.syncOfflineData()
        } catch (error) {
            syncStatus.syncError = error.message
            console.error('Manual sync failed:', error)
        }
    }

    // Get offline transactions for display
    const getOfflineTransactions = async () => {
        try {
            return await offlineManager.getOfflineTransactions()
        } catch (error) {
            console.error('Failed to get offline transactions:', error)
            return []
        }
    }

    // Enhanced form submission that works offline
    const submitForm = async (url, data, options = {}) => {
        const defaultOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            ...options
        }

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        if (csrfToken) {
            defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken
        }

        if (isOnline.value) {
            try {
                // Try online submission first
                const response = await fetch(url, {
                    ...defaultOptions,
                    body: JSON.stringify(data)
                })

                if (response.ok) {
                    return await response.json()
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`)
                }
            } catch (error) {
                // If online submission fails, store offline
                console.log('Online submission failed, storing offline:', error)

                if (url.includes('/transactions')) {
                    await storeTransactionOffline(data)
                    return { success: true, offline: true, message: 'Transaction saved offline' }
                } else if (url.includes('/categories')) {
                    await storeCategoryOffline(data)
                    return { success: true, offline: true, message: 'Category saved offline' }
                }

                throw error
            }
        } else {
            // Store offline when offline
            if (url.includes('/transactions')) {
                await storeTransactionOffline(data)
                return { success: true, offline: true, message: 'Transaction saved offline' }
            } else if (url.includes('/categories')) {
                await storeCategoryOffline(data)
                return { success: true, offline: true, message: 'Category saved offline' }
            }

            throw new Error('Cannot submit this type of data while offline')
        }
    }

    // Check if we should show offline indicator
    const shouldShowOfflineIndicator = () => {
        return !isOnline.value || offlineTransactionCount.value > 0
    }

    // Setup and cleanup
    onMounted(async () => {
        // Register event listeners
        offlineManager.on('onOnline', handleOnline)
        offlineManager.on('onOffline', handleOffline)
        offlineManager.on('onSyncComplete', handleSyncComplete)

        // Initial setup
        await updateOfflineCount()

        // Show offline banner if offline or has pending syncs
        showOfflineBanner.value = !isOnline.value || offlineTransactionCount.value > 0

        // Check sync status
        const lastSync = await offlineManager.getAppData('lastSync')
        if (lastSync) {
            syncStatus.lastSync = new Date(lastSync)
        }
    })

    onUnmounted(() => {
        // Cleanup event listeners
        offlineManager.off('onOnline', handleOnline)
        offlineManager.off('onOffline', handleOffline)
        offlineManager.off('onSyncComplete', handleSyncComplete)
    })

    return {
        // State
        isOnline,
        syncInProgress,
        offlineTransactionCount,
        showOfflineBanner,
        syncStatus,

        // Methods
        storeTransactionOffline,
        storeCategoryOffline,
        triggerSync,
        getOfflineTransactions,
        submitForm,
        shouldShowOfflineIndicator,
        updateOfflineCount
    }
}
