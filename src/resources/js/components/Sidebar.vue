<template>
    <aside class="bg-white rounded-lg shadow-sm p-6 h-fit lg:sticky lg:top-24">
        <h2 class="text-lg font-semibold mb-4">Filters</h2>
        
        <!-- Categories -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Categories</h3>
            <div class="space-y-2">
                <router-link
                    to="/"
                    class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded text-sm text-gray-700 hover:text-blue-600"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': !filters.category_id }"
                >
                    All
                </router-link>
                <router-link
                    v-for="category in categories"
                    :key="category.id"
                    :to="`/category/${category.slug}`"
                    class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded text-sm text-gray-700 hover:text-blue-600"
                    :class="{ 'bg-blue-50 text-blue-600 font-medium': filters.category_id === category.id }"
                >
                    {{ category.title }}
                </router-link>
            </div>
        </div>

        <hr class="border-gray-200 mb-6" />

        <!-- Manufacturers -->
        <div class="mb-6">
            <button
                @click="toggleCollapsible('manufacturers')"
                class="flex items-center justify-between w-full text-sm font-medium text-gray-700 mb-3 hover:text-gray-900 cursor-pointer"
            >
                <span>Manufacturers</span>
                <svg
                    :class="['w-4 h-4 transition-transform', isCollapsed('manufacturers') ? '' : 'rotate-180']"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div v-show="!isCollapsed('manufacturers')" class="space-y-2">
                <label
                    v-for="manufacturer in manufacturers"
                    :key="manufacturer.id"
                    class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded"
                >
                    <input
                        type="checkbox"
                        :value="manufacturer.id"
                        :checked="isManufacturerSelected(manufacturer.id)"
                        @change="toggleManufacturer(manufacturer.id)"
                        class="mr-2"
                    />
                    <span class="text-sm">{{ manufacturer.title }}</span>
                </label>
            </div>
        </div>

        <hr class="border-gray-200 mb-6" />

        <!-- Price Range -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Price Range</h3>
            <div class="flex gap-2">
                <input
                    type="number"
                    :value="getPriceMin()"
                    @input="updatePriceRange('min', $event.target.value)"
                    placeholder="Min"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
                <input
                    type="number"
                    :value="getPriceMax()"
                    @input="updatePriceRange('max', $event.target.value)"
                    placeholder="Max"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
            </div>
        </div>

        <hr v-if="categoryAttributes && categoryAttributes.length > 0" class="border-gray-200 mb-6" />

        <!-- Category Attributes -->
        <div v-if="categoryAttributes && categoryAttributes.length > 0" class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Attributes</h3>
            <div
                v-for="attribute in categoryAttributes"
                :key="attribute.id"
                class="mb-4"
            >
                <!-- LIST type - multiple selection -->
                <div v-if="attribute.type.slug === 'list'">
                    <button
                        @click="toggleCollapsible(`attribute-${attribute.id}`)"
                        class="flex items-center justify-between w-full text-sm font-medium text-gray-700 mb-2 hover:text-gray-900 cursor-pointer"
                    >
                        <span>{{ attribute.title }}</span>
                        <svg
                            :class="['w-4 h-4 transition-transform', isCollapsed(`attribute-${attribute.id}`) ? '' : 'rotate-180']"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="!isCollapsed(`attribute-${attribute.id}`)" class="space-y-2">
                        <label
                            v-for="option in attribute.options"
                            :key="option.id"
                            class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded"
                        >
                            <input
                                type="checkbox"
                                :value="option.id"
                                :checked="isAttributeOptionSelected(attribute.id, option.id)"
                                @change="toggleAttributeOption(attribute.id, option.id)"
                                class="mr-2"
                            />
                            <span class="text-sm">{{ option.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- DECIMAL/INTEGER type - range -->
                <div v-else-if="attribute.type.slug === 'decimal' || attribute.type.slug === 'integer'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ attribute.title }}
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            :value="getAttributeMin(attribute.id)"
                            @input="updateAttributeRange(attribute.id, 'min', $event.target.value)"
                            placeholder="Min"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        />
                        <input
                            type="number"
                            :value="getAttributeMax(attribute.id)"
                            @input="updateAttributeRange(attribute.id, 'max', $event.target.value)"
                            placeholder="Max"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        />
                    </div>
                </div>

                <!-- STRING/BOOLEAN type - text input -->
                <div v-else>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ attribute.title }}
                    </label>
                    <input
                        type="text"
                        :value="getAttributeValue(attribute.id)"
                        @input="updateAttributeValue(attribute.id, $event.target.value)"
                        :placeholder="`Enter ${attribute.title.toLowerCase()}`"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                    />
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    manufacturers: {
        type: Array,
        default: () => [],
    },
    categoryId: {
        type: Number,
        default: null,
    },
    categoryAttributes: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update-filters']);

// Collapsible state management
// By default, all sections are collapsed (closed)
const collapsedSections = ref(new Set(['manufacturers']));

// Initialize collapsed state for all LIST attributes
if (props.categoryAttributes) {
    props.categoryAttributes.forEach(attr => {
        if (attr.type?.slug === 'list') {
            collapsedSections.value.add(`attribute-${attr.id}`);
        }
    });
}

const isCollapsed = (section) => {
    return collapsedSections.value.has(section);
};

const toggleCollapsible = (section) => {
    if (collapsedSections.value.has(section)) {
        collapsedSections.value.delete(section);
    } else {
        collapsedSections.value.add(section);
    }
};

// Auto-open sections if they have selected values (but keep closed by default)
watch(() => props.filters, (newFilters) => {
    // Auto-open manufacturers if any selected
    if (newFilters.manufacturer && newFilters.manufacturer !== '') {
        collapsedSections.value.delete('manufacturers');
    }
    
    // Auto-open attribute sections if any selected
    if (newFilters.attributes) {
        Object.keys(newFilters.attributes).forEach(slug => {
            const value = newFilters.attributes[slug];
            if (value && value !== '') {
                // Find attribute by slug
                const attribute = props.categoryAttributes?.find(a => a.slug === slug);
                if (attribute && attribute.type?.slug === 'list') {
                    collapsedSections.value.delete(`attribute-${attribute.id}`);
                }
            }
        });
    }
}, { deep: true });

// Watch for categoryAttributes changes to initialize collapsed state for new attributes
watch(() => props.categoryAttributes, (newAttributes) => {
    if (newAttributes) {
        newAttributes.forEach(attr => {
            if (attr.type?.slug === 'list' && !props.filters.attributes?.[attr.slug]) {
                // Only add to collapsed if no value is selected
                collapsedSections.value.add(`attribute-${attr.id}`);
            }
        });
    }
}, { immediate: true });

const updateFilter = (key, value) => {
    emit('update-filters', { [key]: value });
};

const isManufacturerSelected = (manufacturerId) => {
    if (!props.filters.manufacturer) return false;
    const values = props.filters.manufacturer.split(',').map(v => parseInt(v.trim())).filter(v => !isNaN(v));
    return values.includes(manufacturerId);
};

const toggleManufacturer = (manufacturerId) => {
    const currentValue = props.filters.manufacturer || '';
    const currentValues = currentValue ? currentValue.split(',').map(v => parseInt(v.trim())).filter(v => !isNaN(v)) : [];
    
    let newValues;
    if (currentValues.includes(manufacturerId)) {
        newValues = currentValues.filter(v => v !== manufacturerId);
    } else {
        newValues = [...currentValues, manufacturerId];
    }

    emit('update-filters', { manufacturer: newValues.length > 0 ? newValues.join(',') : null });
};

// Local state for price inputs to prevent clearing during editing
const priceInputs = ref({
    min: '',
    max: '',
});

// Initialize price inputs from filters
watch(() => props.filters.price, (newPrice) => {
    if (newPrice) {
        const [min, max] = newPrice.split(':').map(v => v.trim() || '');
        priceInputs.value.min = min;
        priceInputs.value.max = max;
    } else {
        priceInputs.value.min = '';
        priceInputs.value.max = '';
    }
}, { immediate: true });

const getPriceMin = () => {
    return priceInputs.value.min;
};

const getPriceMax = () => {
    return priceInputs.value.max;
};

const updatePriceRange = (type, value) => {
    // Update local state immediately
    if (type === 'min') {
        priceInputs.value.min = value || '';
    } else {
        priceInputs.value.max = value || '';
    }
    
    // Update filter only if we have at least one value
    const min = priceInputs.value.min.trim();
    const max = priceInputs.value.max.trim();
    
    // Format: "min:max", ":max", "min:", or null
    let newPrice = null;
    if (min && max) {
        newPrice = `${min}:${max}`;
    } else if (min) {
        newPrice = `${min}:`;
    } else if (max) {
        newPrice = `:${max}`;
    }

    emit('update-filters', { price: newPrice });
};

// Helper to get attribute slug from ID
const getAttributeSlug = (attributeId) => {
    const attribute = props.categoryAttributes?.find(a => a.id === attributeId);
    return attribute?.slug || null;
};

const isAttributeOptionSelected = (attributeId, optionId) => {
    const slug = getAttributeSlug(attributeId);
    if (!slug || !props.filters.attributes) return false;
    const value = props.filters.attributes[slug];
    if (!value) return false;
    const values = value.split(',').map(v => parseInt(v.trim()));
    return values.includes(optionId);
};

const toggleAttributeOption = (attributeId, optionId) => {
    const slug = getAttributeSlug(attributeId);
    if (!slug) return;
    
    const currentValue = props.filters.attributes?.[slug] || '';
    const currentValues = currentValue ? currentValue.split(',').map(v => parseInt(v.trim())).filter(v => !isNaN(v)) : [];
    
    let newValues;
    if (currentValues.includes(optionId)) {
        newValues = currentValues.filter(v => v !== optionId);
    } else {
        newValues = [...currentValues, optionId];
    }

    updateAttributeBySlug(slug, newValues.length > 0 ? newValues.join(',') : null);
};

const updateAttributeRange = (attributeId, type, value) => {
    const slug = getAttributeSlug(attributeId);
    if (!slug) return;
    
    // Initialize local state if needed
    if (!attributeInputs.value[attributeId]) {
        attributeInputs.value[attributeId] = { min: '', max: '' };
    }
    
    // Update local state immediately
    if (type === 'min') {
        attributeInputs.value[attributeId].min = value || '';
    } else {
        attributeInputs.value[attributeId].max = value || '';
    }
    
    // Update filter only if we have at least one value
    const min = attributeInputs.value[attributeId].min.trim();
    const max = attributeInputs.value[attributeId].max.trim();
    
    // Format: "min:max", ":max", "min:", or null
    let newValue = null;
    if (min && max) {
        newValue = `${min}:${max}`;
    } else if (min) {
        newValue = `${min}:`;
    } else if (max) {
        newValue = `:${max}`;
    }

    updateAttributeBySlug(slug, newValue);
};

const updateAttributeValue = (attributeId, value) => {
    const slug = getAttributeSlug(attributeId);
    if (!slug) return;
    updateAttributeBySlug(slug, value || null);
};

const updateAttributeBySlug = (slug, value) => {
    const attributes = { ...(props.filters.attributes || {}) };
    
    if (value === null || value === '') {
        delete attributes[slug];
    } else {
        attributes[slug] = value;
    }

    emit('update-filters', { attributes });
};

// Local state for attribute range inputs
const attributeInputs = ref({});

// Initialize attribute inputs from filters
watch(() => props.filters.attributes, (newAttributes) => {
    if (newAttributes) {
        Object.keys(newAttributes).forEach(slug => {
            const value = newAttributes[slug];
            if (value && value.includes(':')) {
                const [min, max] = value.split(':').map(v => v.trim() || '');
                const attribute = props.categoryAttributes?.find(a => a.slug === slug);
                if (attribute) {
                    if (!attributeInputs.value[attribute.id]) {
                        attributeInputs.value[attribute.id] = { min: '', max: '' };
                    }
                    attributeInputs.value[attribute.id].min = min;
                    attributeInputs.value[attribute.id].max = max;
                }
            }
        });
    }
}, { immediate: true, deep: true });

const getAttributeMin = (attributeId) => {
    return attributeInputs.value[attributeId]?.min || '';
};

const getAttributeMax = (attributeId) => {
    return attributeInputs.value[attributeId]?.max || '';
};

const getAttributeValue = (attributeId) => {
    const slug = getAttributeSlug(attributeId);
    if (!slug || !props.filters.attributes) return '';
    return props.filters.attributes[slug] || '';
};
</script>

