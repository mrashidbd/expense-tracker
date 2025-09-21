<template>
    <AppLayout title="Reports">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                        <p class="text-gray-600">Generate detailed reports and analyze your financial data</p>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Date Range Filter -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Date Range</h3>
                            <p class="text-sm text-gray-600">{{ summary.period }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 items-end">
                            <div class="flex gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                    <input
                                        v-model="filterForm.start_date"
                                        type="date"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <input
                                        v-model="filterForm.end_date"
                                        type="date"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54]"
                                    />
                                </div>
                            </div>
                            <button
                                @click="applyDateFilter"
                                class="px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors whitespace-nowrap"
                            >
                                Update Report
                            </button>
                        </div>
                    </div>

                    <!-- Quick Date Filters -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <button
                            v-for="preset in datePresets"
                            :key="preset.label"
                            @click="applyDatePreset(preset)"
                            class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                        >
                            {{ preset.label }}
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Income</p>
                                <p class="text-2xl font-bold text-[#87c38f] mt-1">
                                    ${{ formatCurrency(summary.total_income) }}
                                </p>
                            </div>
                            <div class="p-3 bg-[#87c38f]/10 rounded-full">
                                <svg class="w-6 h-6 text-[#87c38f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Expenses</p>
                                <p class="text-2xl font-bold text-[#da2c38] mt-1">
                                    ${{ formatCurrency(summary.total_expense) }}
                                </p>
                            </div>
                            <div class="p-3 bg-[#da2c38]/10 rounded-full">
                                <svg class="w-6 h-6 text-[#da2c38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Net Balance</p>
                                <p class="text-2xl font-bold mt-1" :class="summary.net_balance >= 0 ? 'text-[#226f54]' : 'text-[#da2c38]'">
                                    ${{ formatCurrency(Math.abs(summary.net_balance)) }}
                                </p>
                                <p class="text-sm mt-1" :class="summary.net_balance >= 0 ? 'text-[#87c38f]' : 'text-[#da2c38]'">
                                    {{ summary.net_balance >= 0 ? 'Profit' : 'Loss' }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="summary.net_balance >= 0 ? 'bg-[#226f54]/10' : 'bg-[#da2c38]/10'">
                                <svg class="w-6 h-6" :class="summary.net_balance >= 0 ? 'text-[#226f54]' : 'text-[#da2c38]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Transactions</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ summary.transaction_count }}
                                </p>
                            </div>
                            <div class="p-3 bg-[#f4f0bb]/10 rounded-full">
                                <svg class="w-6 h-6 text-[#43291f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Report</h3>
                    <div class="flex flex-wrap gap-3">
                        <button
                            @click="exportReport('pdf')"
                            :disabled="exporting.pdf"
                            class="flex items-center gap-2 px-4 py-2 bg-[#da2c38] text-white rounded-lg hover:bg-[#b91c26] transition-colors disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ exporting.pdf ? 'Generating...' : 'Export PDF' }}
                        </button>
                        <button
                            @click="exportReport('excel')"
                            :disabled="exporting.excel"
                            class="flex items-center gap-2 px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ exporting.excel ? 'Generating...' : 'Export Excel' }}
                        </button>
                        <button
                            @click="exportReport('csv')"
                            :disabled="exporting.csv"
                            class="flex items-center gap-2 px-4 py-2 bg-[#87c38f] text-white rounded-lg hover:bg-[#6ba46e] transition-colors disabled:opacity-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ exporting.csv ? 'Generating...' : 'Export CSV' }}
                        </button>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Daily Trend Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Trend</h3>
                        <div class="h-64 w-full">
                            <canvas ref="dailyChart" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <!-- Category Pie Charts -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Breakdown</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="h-32">
                                <h4 class="text-sm font-medium text-[#87c38f] mb-2 text-center">Income</h4>
                                <div class="h-24 flex items-center justify-center">
                                    <canvas v-if="incomeByCategory.length > 0" ref="incomeChart" class="max-w-full max-h-full"></canvas>
                                    <p v-else class="text-sm text-gray-500">No income data</p>
                                </div>
                            </div>
                            <div class="h-32">
                                <h4 class="text-sm font-medium text-[#da2c38] mb-2 text-center">Expenses</h4>
                                <div class="h-24 flex items-center justify-center">
                                    <canvas v-if="expenseByCategory.length > 0" ref="expenseChart" class="max-w-full max-h-full"></canvas>
                                    <p v-else class="text-sm text-gray-500">No expense data</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Comparison Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">12-Month Comparison</h3>
                    <div class="h-80 w-full">
                        <canvas ref="monthlyChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Category Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Income Categories -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-4 h-4 bg-[#87c38f] rounded"></div>
                            Income Categories
                        </h3>
                        <div class="space-y-3">
                            <div v-for="category in incomeByCategory" :key="category.category_id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ category.category_name }}</span>
                                        <p class="text-sm text-gray-500">{{ category.count }} transactions</p>
                                    </div>
                                </div>
                                <span class="font-bold text-[#87c38f]">${{ formatCurrency(category.total) }}</span>
                            </div>
                            <div v-if="incomeByCategory.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No income in this period</p>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Categories -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-4 h-4 bg-[#da2c38] rounded"></div>
                            Expense Categories
                        </h3>
                        <div class="space-y-3">
                            <div v-for="category in expenseByCategory" :key="category.category_id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ category.category_name }}</span>
                                        <p class="text-sm text-gray-500">{{ category.count }} transactions</p>
                                    </div>
                                </div>
                                <span class="font-bold text-[#da2c38]">${{ formatCurrency(category.total) }}</span>
                            </div>
                            <div v-if="expenseByCategory.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No expenses in this period</p>
                            </div>
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
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="text-left border-b border-gray-200">
                            <tr>
                                <th class="pb-3 text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="pb-3 text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="pb-3 text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="pb-3 text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="pb-3 text-xs font-medium text-gray-500 uppercase text-right">Amount</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr v-for="transaction in transactions.slice(0, 10)" :key="transaction.id" class="hover:bg-gray-50">
                                <td class="py-3 text-sm text-gray-900">
                                    {{ formatDate(transaction.transaction_date) }}
                                </td>
                                <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                              :class="transaction.type === 'income' ? 'bg-[#87c38f]/20 text-[#226f54]' : 'bg-[#da2c38]/20 text-[#da2c38]'">
                                            {{ transaction.type === 'income' ? 'Income' : 'Expense' }}
                                        </span>
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: transaction.category.color }"></div>
                                        <span class="text-sm text-gray-900">{{ transaction.category.name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-sm text-gray-600 max-w-xs truncate">
                                    {{ transaction.description || '-' }}
                                </td>
                                <td class="py-3 text-sm text-right font-medium"
                                    :class="transaction.type === 'income' ? 'text-[#87c38f]' : 'text-[#da2c38]'">
                                    {{ transaction.type === 'income' ? '+' : '-' }}${{ formatCurrency(transaction.amount) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div v-if="transactions.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No transactions in this period</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    filters: Object,
    summary: Object,
    incomeByCategory: Array,
    expenseByCategory: Array,
    dailyData: Array,
    monthlyData: Array,
    transactions: Array
})

const filterForm = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date
})

const exporting = reactive({
    pdf: false,
    excel: false,
    csv: false
})

const dailyChart = ref(null)
const incomeChart = ref(null)
const expenseChart = ref(null)
const monthlyChart = ref(null)

const datePresets = [
    {
        label: 'This Month',
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    },
    {
        label: 'Last Month',
        start_date: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split('T')[0],
        end_date: new Date(new Date().getFullYear(), new Date().getMonth(), 0).toISOString().split('T')[0]
    },
    {
        label: 'Last 3 Months',
        start_date: new Date(new Date().getFullYear(), new Date().getMonth() - 2, 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    },
    {
        label: 'This Year',
        start_date: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    },
    {
        label: 'Last Year',
        start_date: new Date(new Date().getFullYear() - 1, 0, 1).toISOString().split('T')[0],
        end_date: new Date(new Date().getFullYear() - 1, 11, 31).toISOString().split('T')[0]
    }
]

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
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

const applyDateFilter = () => {
    router.get(route('reports.index'), filterForm, {
        preserveState: true,
        replace: true
    })
}

const applyDatePreset = (preset) => {
    filterForm.start_date = preset.start_date
    filterForm.end_date = preset.end_date
    applyDateFilter()
}

const exportReport = async (format) => {
    exporting[format] = true

    try {
        const params = {
            start_date: filterForm.start_date,
            end_date: filterForm.end_date
        }

        let url
        switch (format) {
            case 'pdf':
                url = route('reports.export.pdf')
                break
            case 'excel':
                url = route('reports.export.excel')
                break
            case 'csv':
                url = route('reports.export.csv')
                break
        }

        // Create form and submit for file download
        const form = document.createElement('form')
        form.method = 'GET'
        form.action = url

        Object.keys(params).forEach(key => {
            const input = document.createElement('input')
            input.type = 'hidden'
            input.name = key
            input.value = params[key]
            form.appendChild(input)
        })

        document.body.appendChild(form)
        form.submit()
        document.body.removeChild(form)
    } catch (error) {
        console.error('Export failed:', error)
    } finally {
        setTimeout(() => {
            exporting[format] = false
        }, 2000)
    }
}

const createDailyChart = () => {
    if (!dailyChart.value || !props.dailyData.length) return

    new Chart(dailyChart.value, {
        type: 'line',
        data: {
            labels: props.dailyData.map(d => d.day),
            datasets: [
                {
                    label: 'Income',
                    data: props.dailyData.map(d => d.income),
                    borderColor: '#87c38f',
                    backgroundColor: '#87c38f20',
                    tension: 0.4
                },
                {
                    label: 'Expense',
                    data: props.dailyData.map(d => d.expense),
                    borderColor: '#da2c38',
                    backgroundColor: '#da2c3820',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString()
                        }
                    }
                }
            }
        }
    })
}

const createCategoryCharts = () => {
    // Income pie chart
    if (incomeChart.value && props.incomeByCategory.length > 0) {
        new Chart(incomeChart.value, {
            type: 'doughnut',
            data: {
                labels: props.incomeByCategory.map(c => c.category_name),
                datasets: [{
                    data: props.incomeByCategory.map(c => c.total),
                    backgroundColor: props.incomeByCategory.map(c => c.color)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        })
    }

    // Expense pie chart
    if (expenseChart.value && props.expenseByCategory.length > 0) {
        new Chart(expenseChart.value, {
            type: 'doughnut',
            data: {
                labels: props.expenseByCategory.map(c => c.category_name),
                datasets: [{
                    data: props.expenseByCategory.map(c => c.total),
                    backgroundColor: props.expenseByCategory.map(c => c.color)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        })
    }
}

const createMonthlyChart = () => {
    if (!monthlyChart.value) return

    new Chart(monthlyChart.value, {
        type: 'bar',
        data: {
            labels: props.monthlyData.map(m => m.month),
            datasets: [
                {
                    label: 'Income',
                    data: props.monthlyData.map(m => m.income),
                    backgroundColor: '#87c38f'
                },
                {
                    label: 'Expense',
                    data: props.monthlyData.map(m => m.expense),
                    backgroundColor: '#da2c38'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString()
                        }
                    }
                }
            }
        }
    })
}

onMounted(() => {
    nextTick(() => {
        createDailyChart()
        createCategoryCharts()
        createMonthlyChart()
    })
})
</script>
