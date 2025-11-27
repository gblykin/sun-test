import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export const productApi = {
    search: (query, limit = 10) => {
        return api.get('/products/search', {
            params: { q: query, limit },
        });
    },
    
    filter: (filters, limit = 12, page = 1) => {
        const params = {
            limit,
            page,
        };
        if (filters.category_id) params.category_id = filters.category_id;
        if (filters.manufacturer) params.manufacturer = filters.manufacturer;
        if (filters.price) params.price = filters.price;
        
        // Attributes are now passed as slug=value format (e.g., power-output=400:600)
        // They should already be in filters object as { slug: value } format
        if (filters.attributes) {
            Object.keys(filters.attributes).forEach(slug => {
                const value = filters.attributes[slug];
                if (value !== null && value !== undefined && value !== '') {
                    params[slug] = value;
                }
            });
        }
        
        return api.get('/products', { params });
    },
};

