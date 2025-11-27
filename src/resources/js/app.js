import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import Catalog from './pages/Catalog.vue';
import CategoryCatalog from './pages/CategoryCatalog.vue';
import SearchResults from './pages/SearchResults.vue';
import ProductDetail from './pages/ProductDetail.vue';

const routes = [
    { path: '/', component: Catalog },
    { path: '/category/:slug', component: CategoryCatalog, props: true },
    { path: '/search', component: SearchResults },
    { path: '/product/:slug', component: ProductDetail, props: true },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const app = createApp(App);
app.use(router);
app.mount('#app');
