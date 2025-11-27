<template>
    <div class="w-full overflow-hidden">
        <!-- Regular Products -->
        <div class="min-h-[600px]">
            <h3 class="text-lg font-semibold mb-4">Products</h3>
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Skeleton loaders - matching ProductCard structure -->
                <div
                    v-for="n in 12"
                    :key="n"
                    class="bg-white rounded-lg shadow-sm p-6 animate-pulse"
                >
                    <!-- Title -->
                    <div class="h-6 bg-gray-200 rounded mb-2 w-3/4"></div>
                    <!-- Price -->
                    <div class="h-8 bg-gray-200 rounded w-24 mb-3"></div>
                    <!-- Category -->
                    <div class="h-4 bg-gray-200 rounded mb-3 w-1/2"></div>
                    <!-- Manufacturer -->
                    <div class="h-4 bg-gray-200 rounded mb-3 w-1/2"></div>
                    <!-- Attributes (3 lines) -->
                    <div class="h-4 bg-gray-200 rounded mb-1 w-2/3"></div>
                    <div class="h-4 bg-gray-200 rounded mb-1 w-2/3"></div>
                    <div class="h-4 bg-gray-200 rounded mb-3 w-2/3"></div>
                    <!-- Description -->
                    <div class="h-4 bg-gray-200 rounded mb-1 w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>
            <div v-else-if="products.length === 0" class="text-center py-8 text-gray-500">
                No products found
            </div>
            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                    />
                </div>
            </div>
            
            <!-- Pagination - always reserve space, outside of v-else -->
            <div class="flex justify-center items-center gap-2 mt-6 min-h-[48px] w-full">
                <div v-if="pagination && pagination.last_page > 1" class="flex justify-center items-center gap-2">
                    <button
                        @click="$emit('page-change', pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Previous
                    </button>
                    <span class="px-4 py-2 text-sm text-gray-600">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <button
                        @click="$emit('page-change', pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import ProductCard from './ProductCard.vue';

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    pagination: {
        type: Object,
        default: null,
    },
});

defineEmits(['page-change']);
</script>

