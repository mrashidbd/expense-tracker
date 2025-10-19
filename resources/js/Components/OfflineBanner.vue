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
                            <svg v-else class="w-5 h-5 text-[#87c38f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <!-- Status Message -->
                        <div class="flex-1">
                            <p class="text-sm font-medium text-[#43291f]">
                                <span v-if="!isOnline">You're offline</span>
                                <span v-else>Connection restored</span>
                            </p>
                            <p v-if="!isOnline" class="text-xs text-[#43291f]/70">
                                ExpenseTracker requires an internet connection to function. Please check your connection and try again.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <!-- Dismiss Button -->
                        <button
                            v-if="isOnline"
                            @click="dismiss"
                            class="p-1 text-[#43291f]/70 hover:text-[#43291f] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOfflineStore } from '@/composables/useOfflineStore.js'

const {
    isOnline,
    showOfflineBanner
} = useOfflineStore()

const dismissed = ref(false)

const shouldShow = computed(() => {
    return !dismissed.value && (
        !isOnline.value ||
        showOfflineBanner.value
    )
})

const dismiss = () => {
    dismissed.value = true
    // Auto-show again if goes offline
    setTimeout(() => {
        if (!isOnline.value) {
            dismissed.value = false
        }
    }, 5000)
}

onMounted(() => {
    // Reset dismissed state when going offline
    $: {
        if (!isOnline.value) {
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