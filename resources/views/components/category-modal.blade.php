{{-- Category Create/Edit Modal --}}
<div
    x-show="showCategoryModal"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    @click.self="closeCategoryModal()"
    style="display: none;"
>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold" x-text="editingCategory ? 'Edit Category' : 'Create Category'"></h2>
            <button @click="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <form @submit.prevent="saveCategoryForm()" x-ref="categoryForm">
            {{-- Name Field --}}
            <div class="mb-4">
                <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                <input
                    type="text"
                    id="category-name"
                    x-model="categoryForm.name"
                    placeholder="Enter category name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
                <template x-if="errors.name">
                    <p class="text-red-500 text-sm mt-1" x-text="errors.name[0]"></p>
                </template>
            </div>

            {{-- Description Field --}}
            <div class="mb-6">
                <label for="category-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    id="category-description"
                    x-model="categoryForm.description"
                    placeholder="Enter category description (optional)"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                <template x-if="errors.description">
                    <p class="text-red-500 text-sm mt-1" x-text="errors.description[0]"></p>
                </template>
            </div>

            {{-- Loading State --}}
            <template x-if="isSubmitting">
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <p class="text-sm text-blue-700">Saving...</p>
                </div>
            </template>

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    @click="closeCategoryModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-text="editingCategory ? 'Update' : 'Create'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
