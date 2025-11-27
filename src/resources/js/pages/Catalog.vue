<template>
    <div class="min-h-screen bg-gray-50 overflow-x-hidden">
        <Header :search-query="searchQuery" @search="handleSearch" />
        <div class="container mx-auto px-4 py-6 max-w-full">
            <div class="flex flex-col lg:flex-row gap-6">
                <Sidebar
                    :categories="categories"
                    :manufacturers="manufacturers"
                    :category-id="null"
                    :category-attributes="[]"
                    :filters="filters"
                    @update-filters="handleFilterUpdate"
                    class="w-full lg:w-64 flex-shrink-0"
                />
                <div class="flex-1 min-w-0 w-full lg:w-0">
                    <ProductList
                        :products="products"
                        :loading="loading"
                        :pagination="pagination"
                        @page-change="loadProducts"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Header from '../components/Header.vue';
import Sidebar from '../components/Sidebar.vue';
import ProductList from '../components/ProductList.vue';
import { productApi } from '../services/api';

const route = useRoute();
const router = useRouter();

const searchQuery = ref('');
const products = ref([]);
const loading = ref(false);
const categories = ref([]);
const manufacturers = ref([]);
const filters = ref({
    category_id: null,
    manufacturer: null, // Format: "1,2,3" (comma-separated IDs)
    price: null, // Format: "min:max", ":max", "min:"
    attributes: {}, // Changed to object: { slug: value }
});

const loadCategories = async () => {
    try {
        const response = await fetch('/api/categories');
        const data = await response.json();
        categories.value = data.data || [];
    } catch (error) {
        console.error('Failed to load categories:', error);
    }
};

const loadManufacturers = async () => {
    try {
        const response = await fetch('/api/manufacturers');
        const data = await response.json();
        manufacturers.value = data.data || [];
    } catch (error) {
        console.error('Failed to load manufacturers:', error);
    }
};

const pagination = ref(null);

const loadProducts = async (page = 1) => {
    loading.value = true;
    
    // Build query object with filters and page
    const query = {};
    
    if (filters.value.category_id) {
        query.category_id = filters.value.category_id;
    }
    if (filters.value.manufacturer) {
        query.manufacturer = filters.value.manufacturer;
    }
    if (filters.value.price) {
        query.price = filters.value.price;
    }
    // Attributes are now added directly to query as slug=value
    if (filters.value.attributes && Object.keys(filters.value.attributes).length > 0) {
        Object.assign(query, filters.value.attributes);
    }
    if (page > 1) {
        query.page = page;
    }
    
    // Update URL with all parameters
    router.push({ query });
    
    try {
        const response = await productApi.filter(filters.value, 12, page);
        products.value = response.data.data || [];
        
        // Laravel returns pagination in meta object
        if (response.data.meta) {
            pagination.value = {
                current_page: response.data.meta.current_page,
                last_page: response.data.meta.last_page,
                per_page: response.data.meta.per_page,
                total: response.data.meta.total,
                from: response.data.meta.from,
                to: response.data.meta.to,
            };
        } else {
            pagination.value = null;
        }
    } catch (error) {
        console.error('Failed to load products:', error);
        pagination.value = null;
    } finally {
        loading.value = false;
    }
};

const handleSearch = (query) => {
    searchQuery.value = query;
    // Search is now handled in Header component
};

const loadFiltersFromUrl = () => {
    const query = route.query;
    
    if (query.category_id) {
        filters.value.category_id = parseInt(query.category_id);
    } else {
        filters.value.category_id = null;
    }
    if (query.manufacturer) {
        filters.value.manufacturer = query.manufacturer;
    } else {
        filters.value.manufacturer = null;
    }
    if (query.price) {
        filters.value.price = query.price;
    } else {
        filters.value.price = null;
    }
    // Attributes are now in URL as slug=value format (e.g., power-output=400:600)
    // Extract all attribute slugs from query (they are not in the standard filter list)
    filters.value.attributes = {};
    const standardFilters = ['category_id', 'manufacturer', 'price', 'page', 'limit'];
    Object.keys(query).forEach(key => {
        if (!standardFilters.includes(key) && query[key] !== null && query[key] !== undefined && query[key] !== '') {
            filters.value.attributes[key] = query[key];
        }
    });
};

const handleFilterUpdate = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters };
    loadProducts(1); // loadProducts will update URL with all filters
};

onMounted(() => {
    loadCategories();
    loadManufacturers();
    
    // Load filters from URL
    loadFiltersFromUrl();
    
    // Load products with page from URL query or default to 1
    const pageFromUrl = parseInt(route.query.page) || 1;
    loadProducts(pageFromUrl);
});

// Watch for URL query changes (e.g., browser back/forward)
watch(() => route.query, (newQuery, oldQuery) => {
    // Check if filters changed
    const filtersChanged = 
        newQuery.category_id !== oldQuery?.category_id ||
        newQuery.manufacturer !== oldQuery?.manufacturer ||
        newQuery.price !== oldQuery?.price ||
        newQuery.attributes !== oldQuery?.attributes;
    
    if (filtersChanged) {
        loadFiltersFromUrl();
        const page = parseInt(newQuery.page) || 1;
        loadProducts(page);
    } else if (newQuery.page !== oldQuery?.page) {
        // Only page changed - update filters from URL first, then load products
        loadFiltersFromUrl();
        const page = parseInt(newQuery.page) || 1;
        if (pagination.value && pagination.value.current_page !== page) {
            loadProducts(page);
        }
    }
}, { deep: true });
</script>

