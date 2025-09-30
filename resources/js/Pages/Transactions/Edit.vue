<template>
    <AuthenticatedLayout>
        <Head title="Edit Transaction"/>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                     @click="handleClose"></div>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="updateTransaction">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">

                                    <div class="flex justify-between items-center p-6 border-b border-gray-200">
                                        <h2 class="text-xl font-semibold text-gray-900">Edit Transaction</h2>
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

                                    <!-- Transaction Type -->
                                    <div class="my-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Transaction
                                            Type</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button
                                                type="button"
                                                @click="transactionType = 'income'"
                                                :class="['p-4 border-2 rounded-lg text-center font-medium transition-all duration-200', transactionType === 'income' ? 'border-[#87c38f] bg-[#87c38f]/10 text-[#226f54]' : 'border-gray-200 hover:border-gray-300 text-gray-600']"
                                            >
                                                <div class="flex flex-col items-center gap-2">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Income
                                                </div>
                                            </button>
                                            <button
                                                type="button"
                                                @click="transactionType = 'expense'"
                                                :class="['p-4 border-2 rounded-lg text-center font-medium transition-all duration-200', transactionType === 'expense' ? 'border-[#da2c38] bg-[#da2c38]/10 text-[#da2c38]' : 'border-gray-200 hover:border-gray-300 text-gray-600']"
                                            >
                                                <div class="flex flex-col items-center gap-2">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                    Expense
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <!-- Category -->
                                        <div>
                                            <label for="category" class="block text-sm font-medium text-gray-700">
                                                Category
                                            </label>
                                            <select
                                                id="category"
                                                v-model="form.category_id"
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                                required
                                            >
                                                <option value="" disabled>Select a category</option>
                                                <optgroup
                                                    v-if="transactionType && filteredCategories.length > 0"
                                                    :label="transactionType === 'income' ? 'Income Categories' : 'Expense Categories'"
                                                >
                                                    <option
                                                        v-for="category in filteredCategories"
                                                        :key="category.id"
                                                        :value="category.id"
                                                    >
                                                        {{ category.name }}
                                                    </option>
                                                </optgroup>
                                            </select>
                                            <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">
                                                {{ form.errors.category_id }}
                                            </div>
                                        </div>

                                        <!-- Amount -->
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                                Amount
                                            </label>
                                            <input
                                                type="number"
                                                id="amount"
                                                v-model="form.amount"
                                                step="0.01"
                                                min="0"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            />
                                            <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">
                                                {{ form.errors.amount }}
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label for="description" class="block text-sm font-medium text-gray-700">
                                                Description
                                            </label>
                                            <textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="3"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            ></textarea>
                                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">
                                                {{ form.errors.description }}
                                            </div>
                                        </div>

                                        <!-- Date -->
                                        <div>
                                            <label for="transaction_date"
                                                   class="block text-sm font-medium text-gray-700">
                                                Date
                                            </label>
                                            <input
                                                type="date"
                                                id="transaction_date"
                                                v-model="form.transaction_date"
                                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                required
                                            />
                                            <div v-if="form.errors.transaction_date" class="text-red-500 text-xs mt-1">
                                                {{ form.errors.transaction_date }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-between">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full inline-flex justify-center border border-transparent shadow-sm px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update' }}
                            </button>
                            <button
                                type="button"
                                @click="handleClose"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import {computed, ref, watch} from 'vue';
import {Head, useForm, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaction: {
        type: Object,
        required: true
    },
    categories: Object
});

const transactionType = ref(props.transaction?.type || 'expense');

const filteredCategories = computed(() => {
    return props.categories[transactionType.value] || [];
});


// Initialize form with existing transaction data
const form = useForm({
    category_id: props.transaction?.category_id || '',
    type: props.transaction?.type || 'expense',
    amount: props.transaction?.amount || '',
    description: props.transaction?.description || '',
    transaction_date: props.transaction?.transaction_date
        ? props.transaction.transaction_date.split('T')[0]
        : '',
});

// watch sync transaction type with form:
watch(transactionType, (newType) => {
    form.type = newType;
    // Reset category if it doesn't belong to the new type
    const currentCategory = props.categories[form.type]?.find(cat => cat.id === form.category_id);
    if (!currentCategory) {
        form.category_id = '';
    }
});

// Update transaction
const updateTransaction = () => {
    form.put(route('transactions.update', props.transaction.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('transactions.index'));
        },
    });
};

// Handle close modal
const handleClose = () => {
    router.visit(route('transactions.index'));
};
</script>
