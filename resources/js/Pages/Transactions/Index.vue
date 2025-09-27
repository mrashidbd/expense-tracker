<template>
    <AppLayout title="Transactions">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
                            <p class="text-gray-600">Manage your income and expenses</p>
                        </div>
                        <Link
                            :href="route('transactions.create')"
                            class="bg-[#226f54] hover:bg-[#1a5440] text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Transaction
                        </Link>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select v-model="filterForm.type" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]">
                                <option value="">All Types</option>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <select v-model="filterForm.month" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]">
                                <option value="">Current Month</option>
                                <option v-for="month in monthOptions" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select v-model="filterForm.year" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]">
                                <option value="">Current Year</option>
                                <option v-for="year in yearOptions" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select v-model="filterForm.category_id" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]">
                                <option value="">All Categories</option>
                                <optgroup v-if="categories.income && categories.income.length > 0" label="Income Categories">
                                    <option v-for="category in categories.income" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </optgroup>
                                <optgroup v-if="categories.expense && categories.expense.length > 0" label="Expense Categories">
                                    <option v-for="category in categories.expense" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button @click="clearFilters" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            Clear Filters
                        </button>
                        <button @click="showAllTransactions" class="px-3 py-1 text-sm bg-[#f4f0bb] hover:bg-[#f0eb9c] text-[#43291f] rounded-lg transition-colors">
                            Show All Time
                        </button>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <!-- Summary Bar -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Total Income</p>
                                <p class="text-xl font-bold text-[#87c38f]">Tk. {{ formatCurrency(totalIncome) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Total Expenses</p>
                                <p class="text-xl font-bold text-[#da2c38]">Tk. {{ formatCurrency(totalExpense) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Net Balance</p>
                                <p class="text-xl font-bold" :class="netBalance >= 0 ? 'text-[#226f54]' : 'text-[#da2c38]'">
                                    Tk. {{ formatCurrency(Math.abs(netBalance)) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="table-items-header text-left">Date</th>
                                <th class="table-items-header text-left hidden md:table-cell">Type</th>
                                <th class="table-items-header text-left">Category</th>
                                <th class="table-items-header text-left hidden md:table-cell">Description</th>
                                <th class="table-items-header text-right">Amount</th>
                                <th class="table-items-header text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                                <td class="table-items-row whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(transaction.transaction_date) }}
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="transaction.type === 'income' ? 'bg-[#87c38f]/20 text-[#226f54]' : 'bg-[#da2c38]/20 text-[#da2c38]'">
                                            {{ transaction.type === 'income' ? 'Income' : 'Expense' }}
                                        </span>
                                </td>
                                <td class="table-items-row whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: transaction.category.color }"></div>
                                        <span class="text-sm text-gray-900">{{ transaction.category.name }}</span>
                                    </div>
                                </td>
                                <td class="table-items-row hidden md:table-cell text-sm text-gray-900 max-w-xs truncate">
                                    {{ transaction.description || '-' }}
                                </td>
                                <td class="table-items-row whitespace-nowrap text-sm text-right font-medium"
                                    :class="transaction.type === 'income' ? 'text-[#87c38f]' : 'text-[#da2c38]'">
                                    {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </td>
                                <td class="table-items-row whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('transactions.edit', transaction.id)"
                                              class="text-[#226f54] hover:text-[#1a5440] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -0.5 21 21"
                                            class="w-5 h-5">
                                                <path fill="currentColor" fill-rule="evenodd" d="M3.15 14c-.58 0-1.05-.448-1.05-1V7c0-.552.47-1 1.05-1 .58 0 1.05-.448 1.05-1s-.47-1-1.05-1H2.1C.94 4 0 4.895 0 6v8c0 1.105.94 2 2.1 2h1.05c.58 0 1.05-.448 1.05-1s-.47-1-1.05-1M18.9 4h-7.35c-.58 0-1.05.448-1.05 1s.47 1 1.05 1h6.3c.58 0 1.05.448 1.05 1v6c0 .552-.47 1-1.05 1h-6.3c-.58 0-1.05.448-1.05 1s.47 1 1.05 1h7.35c1.16 0 2.1-.895 2.1-2V6c0-1.105-.94-2-2.1-2m-8.4 15c0 .552-.47 1-1.05 1h-4.2c-.58 0-1.05-.448-1.05-1s.47-1 1.05-1H6.3V2H5.25C4.67 2 4.2 1.552 4.2 1s.47-1 1.05-1h4.2c.58 0 1.05.448 1.05 1s-.47 1-1.05 1H8.4v16h1.05c.58 0 1.05.448 1.05 1"/>
                                            </svg>
                                        </Link>
                                        <button @click="deleteTransaction(transaction)"
                                                class="text-[#da2c38] hover:text-[#b91c26] transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="-3 0 32 32"
                                            class="w-5 h-5">
                                                <path fill="currentColor" fill-rule="evenodd" d="M7 15a1 1 0 0 1 2 0v12a1 1 0 1 1-2 0V15Zm5 0a1 1 0 0 1 2 0v12a1 1 0 1 1-2 0V15Zm5 0a1 1 0 0 1 2 0v12a1 1 0 1 1-2 0V15ZM2 28a4 4 0 0 0 4 4h14a4 4 0 0 0 4-4V12H2v16ZM16 4h-6V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1Zm8 0h-6V2a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v2a2 2 0 0 0 1.999 2h22.003A2 2 0 0 0 26 8V6a2 2 0 0 0-2-2Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="transactions.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first transaction.</p>
                            <div class="mt-6">
                                <Link :href="route('transactions.create')"
                                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#226f54] hover:bg-[#1a5440]">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Transaction
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                        <Pagination :links="transactions.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#da2c38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Delete Transaction</h3>
                            <p class="text-sm text-gray-500 mt-1">Are you sure you want to delete this transaction? This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="showDeleteModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="confirmDelete"
                                :disabled="deletingTransaction"
                                class="flex-1 px-4 py-2 bg-[#da2c38] text-white rounded-lg hover:bg-[#b91c26] transition-colors disabled:opacity-50">
                            {{ deletingTransaction ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    transactions: Object,
    categories: Object,
    filters: Object
})

const showDeleteModal = ref(false)
const deletingTransaction = ref(false)
const transactionToDelete = ref(null)

const filterForm = reactive({
    type: props.filters.type || '',
    month: props.filters.month || '',
    year: props.filters.year || '',
    category_id: props.filters.category_id || ''
})

const monthOptions = [
    { value: '1', label: 'January' },
    { value: '2', label: 'February' },
    { value: '3', label: 'March' },
    { value: '4', label: 'April' },
    { value: '5', label: 'May' },
    { value: '6', label: 'June' },
    { value: '7', label: 'July' },
    { value: '8', label: 'August' },
    { value: '9', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' }
]

const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear()
    const years = []
    for (let i = currentYear; i >= currentYear - 5; i--) {
        years.push(i)
    }
    return years
})

const totalIncome = computed(() => {
    return props.transactions.data
        .filter(t => t.type === 'income')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
})

const totalExpense = computed(() => {
    return props.transactions.data
        .filter(t => t.type === 'expense')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
})

const netBalance = computed(() => totalIncome.value - totalExpense.value)

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('bn-BD', {
        numberingSystem: 'latn',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const applyFilters = () => {
    const params = {}
    if (filterForm.type) params.type = filterForm.type
    if (filterForm.month) params.month = filterForm.month
    if (filterForm.year) params.year = filterForm.year
    if (filterForm.category_id) params.category_id = filterForm.category_id

    router.get(route('transactions.index'), params, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    filterForm.type = ''
    filterForm.month = ''
    filterForm.year = ''
    filterForm.category_id = ''

    router.get(route('transactions.index'), {}, {
        preserveState: true,
        replace: true
    })
}

const showAllTransactions = () => {
    router.get(route('transactions.index'), { all: true }, {
        preserveState: true,
        replace: true
    })
}

const deleteTransaction = (transaction) => {
    transactionToDelete.value = transaction
    showDeleteModal.value = true
}

const confirmDelete = () => {
    if (!transactionToDelete.value) return

    deletingTransaction.value = true

    router.delete(route('transactions.destroy', transactionToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            transactionToDelete.value = null
        },
        onFinish: () => {
            deletingTransaction.value = false
        }
    })
}
</script>
