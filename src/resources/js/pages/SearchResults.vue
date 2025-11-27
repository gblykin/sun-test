<template>
    <div class="min-h-screen bg-gray-50">
        <Header :search-query="searchQuery" @search="handleSearch" />
        <div class="container mx-auto px-4 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Search Results for "{{ searchQuery }}"
                </h1>
                <p v-if="!loading && products.length > 0" class="text-gray-600">
                    Found {{ pagination?.total || products.length }} product(s)
                </p>
            </div>
            
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
            <div v-else-if="products.length === 0" class="text-center py-8 text-gray-500">
                <p class="text-lg mb-2">No products found</p>
                <p class="text-sm">Try adjusting your search query</p>
            </div>
            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                    />
                </div>
                
                <!-- Pagination -->
                <div v-if="pagination && pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-6">
                    <button
                        @click="loadPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Previous
                    </button>
                    <span class="px-4 py-2 text-sm text-gray-600">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <button
                        @click="loadPage(pagination.current_page + 1)"
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
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Header from '../components/Header.vue';
import ProductCard from '../components/ProductCard.vue';
import { productApi } from '../services/api';

const route = useRoute();
const router = useRouter();

const searchQuery = ref(route.query.q || '');
const products = ref([]);
const loading = ref(false);
const pagination = ref(null);

const loadSearchResults = async (page = 1) => {
    if (!searchQuery.value.trim()) {
        products.value = [];
        pagination.value = null;
        return;
    }

    loading.value = true;
    try {
        const response = await productApi.search(searchQuery.value, 12);
        products.value = response.data.data || [];
        
        // For search, we might need to implement pagination if API supports it
        // For now, just show all results
        pagination.value = null;
    } catch (error) {
        console.error('Search failed:', error);
        products.value = [];
        pagination.value = null;
    } finally {
        loading.value = false;
    }
};

const loadPage = (page) => {
    router.push({
        query: {
            ...route.query,
            page: page > 1 ? page : undefined,
        },
    });
};

const handleSearch = (query) => {
    searchQuery.value = query;
    router.push({
        path: '/search',
        query: { q: query },
    });
};

onMounted(() => {
    if (searchQuery.value) {
        loadSearchResults();
    }
});

watch(() => route.query.q, (newQuery) => {
    searchQuery.value = newQuery || '';
    if (searchQuery.value) {
        loadSearchResults();
    } else {
        products.value = [];
        pagination.value = null;
    }
});
</script>

