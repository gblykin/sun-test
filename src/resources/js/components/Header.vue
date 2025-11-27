<template>
    <header class="bg-white shadow-sm sticky top-0 z-50 w-full">
        <div class="container mx-auto px-4 py-4 max-w-full">
            <div class="relative">
                <input
                    v-model="localQuery"
                    @input="handleInput"
                    @focus="showDropdown = true"
                    @blur="handleBlur"
                    type="text"
                    placeholder="Search products..."
                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
                <svg
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
                
                <!-- Dropdown with search results -->
                <div
                    v-if="showDropdown && localQuery.trim() && (searchResults.length > 0 || searchLoading || (!searchLoading && searchResults.length === 0))"
                    class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto z-50"
                >
                    <div v-if="searchLoading" class="p-4 text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                    </div>
                    <div v-else-if="searchResults.length > 0">
                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                            <router-link
                                :to="`/search?q=${encodeURIComponent(localQuery)}`"
                                class="block text-center text-blue-600 hover:text-blue-800 font-medium"
                                @mousedown="showDropdown = false"
                            >
                                Suggested products. See all
                            </router-link>
                        </div>
                        <div
                            v-for="product in searchResults"
                            :key="product.id"
                            class="p-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 cursor-pointer"
                            @mousedown="goToProduct(product.slug)"
                        >
                            <div class="font-medium text-gray-900">{{ product.title }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                <span v-if="product.category">{{ product.category.title }}</span>
                                <span v-if="product.manufacturer"> • {{ product.manufacturer.title }}</span>
                            </div>
                            <div class="text-sm font-semibold text-blue-600 mt-1">
                                €{{ formatPrice(product.price) }}
                            </div>
                        </div>
                    </div>
                    <div v-else-if="localQuery.trim() && !searchLoading" class="p-4 text-center text-gray-500">
                        <p>Sorry, we didn't find any matches for "<span class="font-medium">{{ localQuery }}</span>"</p>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useDebounceFn } from '@vueuse/core';
import { productApi } from '../services/api';

const props = defineProps({
    searchQuery: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['search']);

const router = useRouter();
const localQuery = ref(props.searchQuery);
const showDropdown = ref(false);
const searchResults = ref([]);
const searchLoading = ref(false);

watch(() => props.searchQuery, (newVal) => {
    localQuery.value = newVal;
});

const debouncedSearch = useDebounceFn(async (query) => {
    if (!query.trim()) {
        searchResults.value = [];
        return;
    }

    searchLoading.value = true;
    try {
        const response = await productApi.search(query, 5);
        searchResults.value = response.data.data || [];
    } catch (error) {
        console.error('Search failed:', error);
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
}, 300);

const handleInput = () => {
    emit('search', localQuery.value);
    if (localQuery.value.trim()) {
        debouncedSearch(localQuery.value);
        showDropdown.value = true;
    } else {
        searchResults.value = [];
        showDropdown.value = false;
    }
};

const handleBlur = () => {
    // Delay hiding dropdown to allow clicks on results
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};

const goToProduct = (slug) => {
    router.push(`/product/${slug}`);
    showDropdown.value = false;
};

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};
</script>


