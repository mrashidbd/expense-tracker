<template>
    <AppLayout title="Add Transaction">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex items-center gap-4">
                        <Link :href="route('transactions.index')"
                              class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Add Transaction</h1>
                            <p class="text-gray-600">Record a new income or expense</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <form @submit.prevent="submitTransaction">
                        <!-- Transaction Type -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-4">Transaction Type</label>
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    @click="form.type = 'income'"
                                    :class="[
                                        'p-6 border-2 rounded-xl text-center font-medium transition-all duration-200',
                                        form.type === 'income'
                                            ? 'border-[#87c38f] bg-[#87c38f]/10 text-[#226f54]'
                                            : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                    ]"
                                >
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span class="text-lg">Income</span>
                                        <span class="text-sm text-gray-500">Money coming in</span>
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'expense'"
                                    :class="[
                                        'p-6 border-2 rounded-xl text-center font-medium transition-all duration-200',
                                        form.type === 'expense'
                                            ? 'border-[#da2c38] bg-[#da2c38]/10 text-[#da2c38]'
                                            : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                    ]"
                                >
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                        <span class="text-lg">Expense</span>
                                        <span class="text-sm text-gray-500">Money going out</span>
                                    </div>
                                </button>
                            </div>
                            <div v-if="form.errors.type" class="text-red-500 text-sm mt-2">{{ form.errors.type }}</div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-6">
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-lg">Tk.</span>
                                </div>
                                <input
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full pl-10 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                                    :class="{ 'border-red-500': form.errors.amount }"
                                />
                            </div>
                            <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                        </div>

                        <!-- Category -->
                        <div class="mb-6">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select
                                id="category"
                                v-model="form.category_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
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
                            <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>

                            <!-- Quick Add Category -->
                            <div class="mt-2">
                                <button
                                    type="button"
                                    @click="showAddCategoryModal = true"
                                    class="text-[#226f54] hover:text-[#1a5440] text-sm font-medium"
                                >
                                    + Add new category
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Add a note about this transaction..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors resize-none"
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                        </div>

                        <!-- Date -->
                        <div class="mb-8">
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input
                                id="date"
                                v-model="form.transaction_date"
                                type="date"
                                :max="today"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                                :class="{ 'border-red-500': form.errors.transaction_date }"
                            />
                            <div v-if="form.errors.transaction_date" class="text-red-500 text-sm mt-1">{{ form.errors.transaction_date }}</div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <Link :href="route('transactions.index')"
                                  class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-center font-medium">
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 px-6 py-3 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ form.processing ? 'Adding...' : 'Add Transaction' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Add Category Modal -->
        <div v-if="showAddCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Quick Add Category</h2>
                    <button @click="showAddCategoryModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="addQuickCategory" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                        <input
                            v-model="quickCategory.name"
                            type="text"
                            placeholder="Enter category name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                        />
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showAddCategoryModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="!quickCategory.name || addingCategory"
                                class="flex-1 px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors disabled:opacity-50">
                            {{ addingCategory ? 'Adding...' : 'Add' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, reactive } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: Object
})

const showAddCategoryModal = ref(false)
const addingCategory = ref(false)

const form = useForm({
    type: 'expense',
    amount: '',
    category_id: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0]
})

const quickCategory = reactive({
    name: ''
})

const today = new Date().toISOString().split('T')[0]

const availableCategories = computed(() => {
    if (!form.type || !props.categories) return []
    return props.categories[form.type] || []
})

// Reset category when type changes
watch(() => form.type, () => {
    form.category_id = ''
})

const submitTransaction = () => {
    form.post(route('transactions.store'))
}

const addQuickCategory = async () => {
    if (!quickCategory.name || !form.type) return

    addingCategory.value = true

    const categoryData = {
        name: quickCategory.name,
        type: form.type,
        color: form.type === 'income' ? '#87c38f' : '#da2c38'
    }

    router.post(route('categories.store'), categoryData, {
        onSuccess: (page) => {
            // Find the newly created category and select it
            const newCategories = page.props.categories
            if (newCategories && newCategories[form.type]) {
                const newCategory = newCategories[form.type].find(cat => cat.name === quickCategory.name)
                if (newCategory) {
                    form.category_id = newCategory.id
                }
            }

            showAddCategoryModal.value = false
            quickCategory.name = ''
        },
        onFinish: () => {
            addingCategory.value = false
        },
        preserveState: false
    })
}
</script>
