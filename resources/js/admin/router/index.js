import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import AdminLayout from '../components/AdminLayout.vue';
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';
import Statistics from '../pages/Statistics.vue';
import ServicesIndex from '../pages/services/Index.vue';
import ServiceForm from '../pages/services/Form.vue';
import ProjectsIndex from '../pages/projects/Index.vue';
import ProjectForm from '../pages/projects/Form.vue';
import LeadsIndex from '../pages/leads/Index.vue';
import CraftsmenIndex from '../pages/craftsmen/Index.vue';
import SettingsIndex from '../pages/settings/Index.vue';
import PasswordIndex from '../pages/password/Index.vue';

const router = createRouter({
    history: createWebHistory('/admin'),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: Login,
            meta: { guest: true },
        },
        {
            path: '/',
            component: AdminLayout,
            meta: { auth: true },
            children: [
                { path: '', name: 'dashboard', component: Dashboard },
                { path: 'statistics', name: 'statistics', component: Statistics },
                { path: 'services', name: 'services', component: ServicesIndex },
                { path: 'services/create', name: 'services.create', component: ServiceForm },
                { path: 'services/:id/edit', name: 'services.edit', component: ServiceForm },
                { path: 'projects', name: 'projects', component: ProjectsIndex },
                { path: 'projects/create', name: 'projects.create', component: ProjectForm },
                { path: 'projects/:id/edit', name: 'projects.edit', component: ProjectForm },
                { path: 'leads', name: 'leads', component: LeadsIndex },
                { path: 'craftsmen', name: 'craftsmen', component: CraftsmenIndex },
                { path: 'settings', name: 'settings', component: SettingsIndex },
                { path: 'password', name: 'password', component: PasswordIndex },
            ],
        },
    ],
});

router.beforeEach(async (to) => {
    const { state, fetchUser } = useAuth();

    if (!state.loaded) {
        try {
            await axios.get('/sanctum/csrf-cookie');
            await fetchUser();
        } catch {
            state.loaded = true;
            state.user = null;
        }
    }

    if (to.meta.auth && !state.user) {
        return { name: 'login' };
    }

    if (to.meta.guest && state.user) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
