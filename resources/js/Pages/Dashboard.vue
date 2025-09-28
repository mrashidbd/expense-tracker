<template>
    <AppLayout title="Dashboard">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                            <p class="text-gray-600">{{ currentMonth.month }} {{ currentMonth.year }}</p>
                        </div>
                        <button
                            @click="showAddTransactionModal = true"
                            class="bg-[#226f54] hover:bg-[#1a5440] text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Transaction
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Income Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Income</p>
                                <p class="text-3xl font-bold text-[#87c38f] mt-1">
                                    Tk. {{ formatCurrency(currentMonth.income) }}
                                </p>
                            </div>
                            <div class="p-3 bg-[#87c38f]/10 rounded-full">
                                <svg class="w-6 h-6 text-[#87c38f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Expenses</p>
                                <p class="text-3xl font-bold text-[#da2c38] mt-1">
                                    Tk. {{ formatCurrency(currentMonth.expense) }}
                                </p>
                            </div>
                            <div class="p-3 bg-[#da2c38]/10 rounded-full">
                                <svg class="w-6 h-6 text-[#da2c38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Net Balance</p>
                                <p class="text-3xl font-bold mt-1" :class="currentMonth.balance >= 0 ? 'text-[#226f54]' : 'text-[#da2c38]'">
                                    Tk. {{ formatCurrency(Math.abs(currentMonth.balance)) }}
                                </p>
                                <p class="text-sm mt-1" :class="currentMonth.balance >= 0 ? 'text-[#87c38f]' : 'text-[#da2c38]'">
                                    {{ currentMonth.balance >= 0 ? 'Profit' : 'Loss' }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="currentMonth.balance >= 0 ? 'bg-[#226f54]/10' : 'bg-[#da2c38]/10'">
                                <svg class="w-6 h-6" :class="currentMonth.balance >= 0 ? 'text-[#226f54]' : 'text-[#da2c38]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Transactions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Monthly Category Breakdown -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">This Month's Breakdown</h3>
                        <div class="space-y-4">
                            <!-- Income Categories -->
                            <div v-if="incomeByCategory.length > 0">
                                <h4 class="text-sm font-medium text-[#87c38f] mb-2">Income Categories</h4>
                                <div class="space-y-2">
                                    <div v-for="category in incomeByCategory" :key="category.category_id" class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                            <span class="text-sm text-gray-700">{{ category.category_name }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Tk. {{ formatCurrency(category.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Expense Categories -->
                            <div v-if="expenseByCategory.length > 0">
                                <h4 class="text-sm font-medium text-[#da2c38] mb-2 mt-4">Expense Categories</h4>
                                <div class="space-y-2">
                                    <div v-for="category in expenseByCategory" :key="category.category_id" class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                            <span class="text-sm text-gray-700">{{ category.category_name }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">Tk. {{ formatCurrency(category.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="incomeByCategory.length === 0 && expenseByCategory.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No transactions this month</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                            <Link :href="route('transactions.index')" class="text-[#226f54] hover:text-[#1a5440] text-sm font-medium">
                                View All
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="transaction in recentTransactions" :key="transaction.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="transaction.type === 'income' ? 'bg-[#87c38f]/20 text-[#87c38f]' : 'bg-[#da2c38]/20 text-[#da2c38]'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="transaction.type === 'income'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ transaction.category.name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(transaction.transaction_date) }}</p>
                                        <p v-if="transaction.description" class="text-xs text-gray-600 mt-1">{{ transaction.description }}</p>
                                    </div>
                                </div>
                                <span class="font-medium" :class="transaction.type === 'income' ? 'text-[#87c38f]' : 'text-[#da2c38]'">
                                    {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </span>
                            </div>
                            <div v-if="recentTransactions.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No recent transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Yearly Overview Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">12-Month Overview</h3>
                    <div class="h-64 flex items-end justify-between gap-2">
                        <div v-for="(month, index) in yearlyData" :key="index" class="flex-1 flex flex-col items-center">
                            <div class="w-full flex flex-col gap-1 relative" :style="{ height: '200px' }">
                                <!-- Income Bar -->
                                <div class="bg-[#87c38f] rounded-t"
                                     :style="{ height: (month.income / maxMonthlyAmount * 180) + 'px' }"
                                     :title="`Income: $${formatCurrency(month.income)}`">
                                </div>
                                <!-- Expense Bar -->
                                <div class="bg-[#da2c38] rounded-b"
                                     :style="{ height: (month.expense / maxMonthlyAmount * 180) + 'px' }"
                                     :title="`Expense: $${formatCurrency(month.expense)}`">
                                </div>
                            </div>
                            <span class="text-xs text-gray-600 mt-2 text-center">{{ month.month }}</span>
                        </div>
                    </div>
                    <div class="flex justify-center gap-6 mt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#87c38f] rounded"></div>
                            <span class="text-sm text-gray-600">Income</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#da2c38] rounded"></div>
                            <span class="text-sm text-gray-600">Expenses</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <AddTransactionModal
            v-if="showAddTransactionModal"
            :categories="categories"
            @close="showAddTransactionModal = false"
            @transaction-added="handleTransactionAdded"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AddTransactionModal from '@/Components/AddTransactionModal.vue'

const props = defineProps({
    currentMonth: Object,
    recentTransactions: Array,
    incomeByCategory: Array,
    expenseByCategory: Array,
    yearlyData: Array,
    categories: Object
})

const showAddTransactionModal = ref(false)

const maxMonthlyAmount = computed(() => {
    if (!props.yearlyData || props.yearlyData.length === 0) return 1000

    return Math.max(
        ...props.yearlyData.map(month => Math.max(month.income, month.expense))
    ) || 1000
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('bn-BD', {
        numberingSystem: 'latn',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    })
}

const handleTransactionAdded = () => {
    showAddTransactionModal.value = false
    // Refresh the page to update data
    router.reload({ only: ['currentMonth', 'recentTransactions', 'incomeByCategory', 'expenseByCategory', 'yearlyData'] })
}
</script>
