// resources/js/composables/useOfflineStore.js
import { ref, onMounted, onUnmounted } from 'vue'
import offlineManager from '@/OfflineManager.js'

export function useOfflineStore() {
    const isOnline = ref(navigator.onLine)
    const showOfflineBanner = ref(false)

    // Event handlers
    const handleOnline = () => {
        isOnline.value = true
        showOfflineBanner.value = false
    }

    const handleOffline = () => {
        isOnline.value = false
        showOfflineBanner.value = true
    }

    // Check if we should show offline indicator
    const shouldShowOfflineIndicator = () => {
        return !isOnline.value
    }

    // Setup and cleanup
    onMounted(() => {
        // Register event listeners
        offlineManager.on('onOnline', handleOnline)
        offlineManager.on('onOffline', handleOffline)

        // Show offline banner if offline
        showOfflineBanner.value = !isOnline.value
    })

    onUnmounted(() => {
        // Cleanup event listeners
        offlineManager.off('onOnline', handleOnline)
        offlineManager.off('onOffline', handleOffline)
    })

    return {
        // State
        isOnline,
        showOfflineBanner,

        // Methods
        shouldShowOfflineIndicator
    }
}