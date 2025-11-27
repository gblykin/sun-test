<template>
    <div class="min-h-screen bg-gray-50 overflow-x-hidden">
        <Header :search-query="searchQuery" @search="handleSearch" />
        <div class="container mx-auto px-4 py-6 max-w-full">
            <div class="flex flex-col lg:flex-row gap-6">
                <Sidebar
                    :categories="categories"
                    :manufacturers="manufacturers"
                    :category-id="categoryId"
                    :category-attributes="categoryAttributes"
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
const slug = ref(route.params.slug);

const searchQuery = ref('');
const products = ref([]);
const loading = ref(false);
const categories = ref([]);
const manufacturers = ref([]);
const categoryAttributes = ref([]);
const categoryId = ref(null);
const filters = ref({
    category_id: null,
    manufacturer: null, // Format: "1,2,3" (comma-separated IDs)
    price: null, // Format: "min:max", ":max", "min:"
    attributes: {}, // Changed to object: { slug: value }
});

const loadCategory = async () => {
    try {
        const response = await fetch(`/api/categories?slug=${slug.value}`);
        const data = await response.json();
        if (data.data && data.data.length > 0) {
            categoryId.value = data.data[0].id;
            filters.value.category_id = categoryId.value;
            // Reset attributes filter when category changes
            filters.value.attributes = [];
            await loadCategoryAttributes(categoryId.value);
        }
    } catch (error) {
        console.error('Failed to load category:', error);
    }
};

const loadCategoryAttributes = async (catId) => {
    try {
        const response = await fetch(`/api/categories/${catId}/attributes`);
        const data = await response.json();
        categoryAttributes.value = data.data || [];
    } catch (error) {
        console.error('Failed to load category attributes:', error);
    }
};

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
    // Don't include category_id - it's already in the URL path as slug
    const query = {};
    
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
    router.push({
        params: { slug: slug.value },
        query,
    });
    
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
    
    // Don't override category_id from URL if we're on a category page
    // It should be set from the category slug
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

const initializeData = async () => {
    // Load all data in parallel
    await Promise.all([
        loadCategory(),
        loadCategories(),
        loadManufacturers(),
    ]);
    
    // Load filters from URL (but preserve category_id from loaded category)
    loadFiltersFromUrl();
    // Ensure category_id is set from loaded category
    if (categoryId.value) {
        filters.value.category_id = categoryId.value;
    }
    
    // Load products with page from URL query or default to 1
    const pageFromUrl = parseInt(route.query.page) || 1;
    loadProducts(pageFromUrl);
};

onMounted(() => {
    initializeData();
});

// Watch for category slug changes (when navigating between categories)
watch(() => route.params.slug, async (newSlug) => {
    if (newSlug && newSlug !== slug.value) {
        slug.value = newSlug;
        // Reset filters when category changes
        filters.value = {
            category_id: null,
            manufacturer: null,
            price: null,
            attributes: {}, // Changed to object: { slug: value }
        };
        await initializeData();
    }
});

// Watch for URL query changes (e.g., browser back/forward)
watch(() => route.query, (newQuery, oldQuery) => {
    // Check if filters changed
    const filtersChanged = 
        newQuery.manufacturer !== oldQuery?.manufacturer ||
        newQuery.price !== oldQuery?.price ||
        newQuery.attributes !== oldQuery?.attributes;
    
    if (filtersChanged) {
        loadFiltersFromUrl();
        if (categoryId.value) {
            filters.value.category_id = categoryId.value;
        }
        const page = parseInt(newQuery.page) || 1;
        loadProducts(page);
    } else if (newQuery.page !== oldQuery?.page) {
        // Only page changed
        const page = parseInt(newQuery.page) || 1;
        if (pagination.value && pagination.value.current_page !== page) {
            loadProducts(page);
        }
    }
}, { deep: true });
</script>

