<template>
    <div class="min-h-screen bg-gray-50">
        <Header :search-query="''" @search="handleSearch" />
        <div class="container mx-auto px-4 py-6">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>
            <div v-else-if="product" class="max-w-4xl mx-auto">
                <!-- Back button -->
                <button
                    @click="$router.back()"
                    class="mb-6 text-blue-600 hover:text-blue-800 flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>

                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.title }}</h1>
                    
                    <div class="mb-6">
                        <span class="text-3xl font-bold text-blue-600">€{{ formatPrice(product.price) }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Category</span>
                            <p class="text-lg text-gray-900">{{ product.category?.title || 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Manufacturer</span>
                            <p class="text-lg text-gray-900">{{ product.manufacturer?.title || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- All Category Attributes -->
                    <div v-if="categoryAttributes.length > 0" class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Specifications</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="attr in categoryAttributes"
                                :key="attr.id"
                                class="border-b border-gray-200 pb-3"
                            >
                                <span class="text-sm font-medium text-gray-500">{{ attr.attribute?.title }}:</span>
                                <span class="ml-2 text-gray-900">
                                    <span v-if="attr.attribute_option">{{ attr.attribute_option.label }}</span>
                                    <span v-else-if="attr.value_decimal !== null">{{ formatNumber(attr.value_decimal) }}{{ attr.attribute?.unit ? ' ' + attr.attribute.unit : '' }}</span>
                                    <span v-else-if="attr.value_text">{{ attr.value_text }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="product.description" class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                        <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-12">
                <p class="text-gray-500 text-lg">Product not found</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Header from '../components/Header.vue';
import { productApi } from '../services/api';

const route = useRoute();
const router = useRouter();

const product = ref(null);
const loading = ref(true);

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};

const formatNumber = (num) => {
    return parseFloat(num).toLocaleString();
};

const categoryAttributes = computed(() => {
    if (!product.value?.attribute_values || product.value.attribute_values.length === 0) {
        return [];
    }
    
    // Get all category attributes (not limited to 3 on detail page)
    return product.value.attribute_values.filter(attr => attr.attribute);
});

const loadProduct = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/api/products/${route.params.slug}`);
        if (!response.ok) {
            throw new Error('Product not found');
        }
        const data = await response.json();
        product.value = data.data;
    } catch (error) {
        console.error('Failed to load product:', error);
        product.value = null;
    } finally {
        loading.value = false;
    }
};

const handleSearch = (query) => {
    // Search is now handled in Header component (dropdown)
    // No redirect needed - dropdown shows results
};

onMounted(() => {
    loadProduct();
});

// Reload if slug changes
watch(() => route.params.slug, () => {
    loadProduct();
});
</script>

