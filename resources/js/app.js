import './bootstrap';
import { createApp } from 'vue';
import App from './admin/App.vue';
import router from './admin/router';

const el = document.getElementById('app');

if (el) {
    createApp(App).use(router).mount(el);
}
