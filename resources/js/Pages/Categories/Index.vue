<template>
    <AppLayout title="Categories">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                            <p class="text-gray-600">Organize your transactions with custom categories</p>
                        </div>
                        <button
                            @click="showAddCategoryModal = true"
                            class="bg-[#226f54] hover:bg-[#1a5440] text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Category
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Categories Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Income Categories -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <div class="w-4 h-4 bg-[#87c38f] rounded"></div>
                                Income Categories
                            </h3>
                            <span class="text-sm text-gray-500">{{ incomeCategories.length }} categories</span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="category in incomeCategories" :key="category.id"
                                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full"
                                         :style="{ backgroundColor: category.color }"></div>
                                    <span class="font-medium text-gray-900">{{ category.name }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editCategory(category)"
                                            class="text-[#226f54] hover:text-[#1a5440] p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteCategory(category)"
                                            class="text-[#da2c38] hover:text-[#b91c26] p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="expenseCategories.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No expense categories yet</p>
                                <button @click="showAddCategoryModal = true; newCategory.type = 'expense'"
                                        class="text-[#226f54] hover:text-[#1a5440] text-sm font-medium mt-2">
                                    Add your first expense category
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Categories -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <div class="w-4 h-4 bg-[#87c38f] rounded"></div>
                                Expense Categories
                            </h3>
                            <span class="text-sm text-gray-500">{{ expenseCategories.length }} categories</span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="category in expenseCategories" :key="category.id"
                                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full"
                                         :style="{ backgroundColor: category.color }"></div>
                                    <span class="font-medium text-gray-900">{{ category.name }}</span>
                                </div>
                                <div
                                    class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editCategory(category)"
                                            class="text-[#226f54] hover:text-[#1a5440] p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button @click="deleteCategory(category)"
                                            class="text-[#da2c38] hover:text-[#b91c26] p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="incomeCategories.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No income categories yet</p>
                                <button @click="showAddCategoryModal = true; newCategory.type = 'income'"
                                        class="text-[#226f54] hover:text-[#1a5440] text-sm font-medium mt-2">
                                    Add your first income category
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <div v-if="showAddCategoryModal || editingCategory"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <!-- Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ editingCategory ? 'Edit Category' : 'Add Category' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="saveCategory" class="p-6">
                    <!-- Category Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Category Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="newCategory.type = 'income'"
                                :class="[
                                    'p-3 border-2 rounded-lg text-center font-medium transition-all duration-200',
                                    newCategory.type === 'income'
                                        ? 'border-[#87c38f] bg-[#87c38f]/10 text-[#226f54]'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                ]"
                            >
                                Income
                            </button>
                            <button
                                type="button"
                                @click="newCategory.type = 'expense'"
                                :class="[
                                    'p-3 border-2 rounded-lg text-center font-medium transition-all duration-200',
                                    newCategory.type === 'expense'
                                        ? 'border-[#da2c38] bg-[#da2c38]/10 text-[#da2c38]'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                ]"
                            >
                                Expense
                            </button>
                        </div>
                        <div v-if="errors.type" class="text-red-500 text-sm mt-1">{{ errors.type }}</div>
                    </div>

                    <!-- Category Name -->
                    <div class="mb-4">
                        <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-2">Category
                            Name</label>
                        <input
                            id="categoryName"
                            v-model="newCategory.name"
                            type="text"
                            placeholder="Enter category name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
                    </div>

                    <!-- Color -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <div class="flex items-center gap-3">
                            <input
                                v-model="newCategory.color"
                                type="color"
                                class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer"
                            />
                            <input
                                v-model="newCategory.color"
                                type="text"
                                placeholder="#226f54"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#226f54] focus:border-[#226f54] transition-colors"
                                :class="{ 'border-red-500': errors.color }"
                            />
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button
                                type="button"
                                v-for="color in predefinedColors"
                                :key="color"
                                @click="newCategory.color = color"
                                class="w-6 h-6 rounded border-2 border-gray-300 hover:border-gray-400 transition-colors"
                                :style="{ backgroundColor: color }"
                            ></button>
                        </div>
                        <div v-if="errors.color" class="text-red-500 text-sm mt-1">{{ errors.color }}</div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="flex-1 px-4 py-2 bg-[#226f54] text-white rounded-lg hover:bg-[#1a5440] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ processing ? 'Saving...' : editingCategory ? 'Update' : 'Add Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-[#da2c38]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Delete Category</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Are you sure you want to delete "{{ categoryToDelete?.name }}"? This action cannot be
                                undone.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="showDeleteModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="confirmDelete"
                                :disabled="deletingCategory"
                                class="flex-1 px-4 py-2 bg-[#da2c38] text-white rounded-lg hover:bg-[#b91c26] transition-colors disabled:opacity-50">
                            {{ deletingCategory ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {ref, computed, reactive} from 'vue'
import {router, useForm} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: Array,
    filters: Object
})

const showAddCategoryModal = ref(false)
const showDeleteModal = ref(false)
const editingCategory = ref(null)
const categoryToDelete = ref(null)
const processing = ref(false)
const deletingCategory = ref(false)
const errors = ref({})

const newCategory = reactive({
    name: '',
    type: 'expense',
    color: '#226f54'
})

const predefinedColors = [
    '#226f54', '#87c38f', '#da2c38', '#b91c26',
    '#f4f0bb', '#43291f', '#2563eb', '#7c3aed',
    '#059669', '#dc2626', '#ea580c', '#0891b2'
]

const incomeCategories = computed(() => {
    return props.categories.filter(cat => cat.type === 'income')
})

const expenseCategories = computed(() => {
    return props.categories.filter(cat => cat.type === 'expense')
})

const editCategory = (category) => {
    editingCategory.value = category
    newCategory.name = category.name
    newCategory.type = category.type
    newCategory.color = category.color
    showAddCategoryModal.value = true
}

const deleteCategory = (category) => {
    categoryToDelete.value = category
    showDeleteModal.value = true
}

const closeModal = () => {
    showAddCategoryModal.value = false
    editingCategory.value = null
    errors.value = {}

    // Reset form
    newCategory.name = ''
    newCategory.type = 'expense'
    newCategory.color = '#226f54'
}

const saveCategory = () => {
    processing.value = true
    errors.value = {}

    const data = {...newCategory}

    if (editingCategory.value) {
        // Update existing category
        router.put(route('categories.update', editingCategory.value.id), data, {
            onSuccess: () => {
                closeModal()
            },
            onError: (formErrors) => {
                errors.value = formErrors
            },
            onFinish: () => {
                processing.value = false
            }
        })
    } else {
        // Create new category
        router.post(route('categories.store'), data, {
            onSuccess: () => {
                closeModal()
            },
            onError: (formErrors) => {
                errors.value = formErrors
            },
            onFinish: () => {
                processing.value = false
            }
        })
    }
}

const confirmDelete = () => {
    if (!categoryToDelete.value) return

    deletingCategory.value = true

    router.delete(route('categories.destroy', categoryToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            categoryToDelete.value = null
        },
        onError: () => {
            // Handle error (category might have transactions)
        },
        onFinish: () => {
            deletingCategory.value = false
        }
    })
}
</script>
