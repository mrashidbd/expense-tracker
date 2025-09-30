<template>

    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                 @click="$emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <!-- Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Add Transaction</h2>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Offline Warning -->
                <div v-if="!isOnline" class="p-4 bg-[#f4f0bb] border-b border-[#226f54]/20">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#43291f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <p class="text-sm text-[#43291f]">
                            You're offline. Transaction will be saved locally and synced when you're back online.
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitTransaction" class="p-6">
                    <!-- Transaction Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Transaction Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="form.type = 'income'"
                                :class="[
                                'p-4 border-2 rounded-lg text-center font-medium transition-all duration-200',
                                form.type === 'income'
                                    ? 'border-[#87c38f] bg-[#87c38f]/10 text-[#226f54]'
                                    : 'border-gray-200 hover:border-gray-300 text-gray-600'
                            ]"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Income
                                </div>
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'expense'"
                                :class="[
                                'p-4 border-2 rounded-lg text-center font-medium transition-all duration-200',
                                form.type === 'expense'
                                    ? 'border-[#da2c38] bg-[#da2c38]/10 text-[#da2c38]'
                                    : 'border-gray-200 hover:border-gray-300 text-gray-600'
                            ]"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 12H4"></path>
                                    </svg>
                                    Expense
                                </div>
                            </button>
                        </div>
                        <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                    </div>

                    <!-- Amount -->
                    <div class="mb-4">
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Tk. </span>
                            </div>
                            <input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                                :class="{ 'border-red-500': form.errors.amount }"
                            />
                        </div>
                        <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select
                            id="category"
                            v-model="form.category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                            :class="{ 'border-red-500': form.errors.category_id }"
                        >
                            <option value="">Select a category</option>
                            <optgroup v-if="form.type && availableCategories.length > 0"
                                      :label="form.type === 'income' ? 'Income Categories' : 'Expense Categories'">
                                <option
                                    v-for="category in availableCategories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </optgroup>
                        </select>
                        <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{
                                form.errors.category_id
                            }}
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description
                            (Optional)</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Add a note about this transaction..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors resize-none"
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{
                                form.errors.description
                            }}
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="mb-6">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input
                            id="date"
                            v-model="form.transaction_date"
                            type="date"
                            :max="today"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                            :class="{ 'border-red-500': form.errors.transaction_date }"
                        />
                        <div v-if="form.errors.transaction_date" class="text-red-500 text-sm mt-1">
                            {{ form.errors.transaction_date }}
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Adding...' : 'Add Transaction' }}
                        </button>
                    </div>
                </form>

                <!-- Success Message for Offline -->
                <div v-if="showOfflineSuccess" class="p-4 bg-[#87c38f]/10 border-t border-[#87c38f]/20">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#226f54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-sm text-[#226f54]">Transaction saved offline. It will sync when you're back
                            online.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, computed, watch} from 'vue'
import {useForm, router} from '@inertiajs/vue3'
import {useOfflineStore} from '@/composables/useOfflineStore.js'

const props = defineProps({
    categories: Object
})

const emit = defineEmits(['close', 'transaction-added'])

const {isOnline, storeTransactionOffline} = useOfflineStore()

const form = useForm({
    type: 'expense',
    amount: '',
    category_id: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0]
})

const showOfflineSuccess = ref(false)

const today = new Date().toISOString().split('T')[0]

const availableCategories = computed(() => {
    if (!form.type || !props.categories) return []
    return props.categories[form.type] || []
})

// Reset category when type changes
watch(() => form.type, () => {
    form.category_id = ''
})

const submitTransaction = async () => {
    if (isOnline.value) {
        // Use Inertia's form submission for online requests (handles CSRF automatically)
        form.post(route('transactions.store'), {
            onSuccess: () => {
                emit('transaction-added')
                emit('close')
            },
            onError: (errors) => {
                console.error('Transaction submission failed:', errors)
            }
        })
    } else {
        // Store offline when offline
        try {
            await storeTransactionOffline({
                amount: form.amount,
                type: form.type,
                category_id: form.category_id,
                description: form.description,
                transaction_date: form.transaction_date
            })

            showOfflineSuccess.value = true
            setTimeout(() => {
                emit('transaction-added')
                emit('close')
            }, 2000)
        } catch (error) {
            console.error('Failed to save offline transaction:', error)
        }
    }
}
</script>
