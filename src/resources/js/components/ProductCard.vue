<template>
    <router-link
        :to="`/product/${product.slug}`"
        class="block bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 cursor-pointer"
    >
        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ product.title }}</h4>
        
        <div class="mb-3">
            <span class="text-2xl font-bold text-blue-600">€{{ formatPrice(product.price) }}</span>
        </div>

        <div class="mb-3 text-sm text-gray-600">
            <span class="font-medium">Category:</span> {{ product.category?.title || 'N/A' }}
        </div>

        <div class="mb-3 text-sm text-gray-600">
            <span class="font-medium">Manufacturer:</span> {{ product.manufacturer?.title || 'N/A' }}
        </div>

        <!-- Category Attributes (max 3) - before description -->
        <div v-if="categoryAttributes.length > 0" class="mb-3">
            <div class="space-y-1">
                <div
                    v-for="attr in categoryAttributes"
                    :key="attr.id"
                    class="text-sm text-gray-600"
                >
                    <span class="font-medium">{{ attr.attribute?.title }}: </span>
                    <span v-if="attr.attribute_option">{{ attr.attribute_option.label }}</span>
                    <span v-else-if="attr.value_decimal !== null">{{ formatNumber(attr.value_decimal) }}{{ attr.attribute?.unit ? ' ' + attr.attribute.unit : '' }}</span>
                    <span v-else-if="attr.value_text">{{ attr.value_text }}</span>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500 line-clamp-2">{{ product.description }}</p>
    </router-link>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
};

const formatNumber = (num) => {
    return parseFloat(num).toLocaleString();
};

// Get category attributes (max 3)
// Backend already filters attribute_values to only include category attributes and limits to 3
const categoryAttributes = computed(() => {
    if (!props.product.attribute_values || props.product.attribute_values.length === 0) {
        return [];
    }
    
    // Backend already filtered and limited to 3, so just return them
    return props.product.attribute_values.filter(attr => attr.attribute);
});
</script>

