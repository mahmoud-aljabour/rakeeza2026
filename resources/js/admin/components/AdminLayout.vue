<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { state, logout } = useAuth();

const links = [
    { to: '/', label: 'الرئيسية', icon: 'fa-gauge-high', exact: true },
    { to: '/services', label: 'الخدمات', icon: 'fa-screwdriver-wrench' },
    { to: '/projects', label: 'الأعمال', icon: 'fa-images' },
    { to: '/leads', label: 'الطلبات', icon: 'fa-inbox' },
    { to: '/craftsmen', label: 'الحرفيون', icon: 'fa-user-gear' },
    { to: '/settings', label: 'محتوى الموقع', icon: 'fa-sliders' },
];

async function onLogout() {
    await logout();
    router.push('/login');
}
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] text-slate-800 lg:flex">
        <aside class="bg-primary-dark text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-72 lg:flex-col">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-5">
                <a href="/" class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="ركيزة" class="h-12 w-auto">
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-accent uppercase">Rakeeza</p>
                        <p class="text-sm font-extrabold">لوحة التحكم</p>
                    </div>
                </a>
            </div>

            <nav class="flex flex-1 flex-col gap-1 p-4">
                <RouterLink
                    v-for="link in links"
                    :key="link.to"
                    v-slot="{ href, navigate, isActive, isExactActive }"
                    :to="link.to"
                    custom
                >
                    <a
                        :href="href"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition"
                        :class="(link.exact ? isExactActive : isActive) ? 'bg-accent text-white' : 'text-slate-200 hover:bg-white/10'"
                        @click="navigate"
                    >
                        <i class="fa-solid w-4" :class="link.icon"></i>
                        {{ link.label }}
                    </a>
                </RouterLink>
            </nav>

            <div class="border-t border-white/10 p-4">
                <p class="mb-3 text-xs text-slate-300">{{ state.user?.name }}</p>
                <div class="flex gap-2">
                    <a href="/" class="flex-1 rounded-xl border border-white/15 px-3 py-2 text-center text-xs font-bold hover:bg-white/8">الموقع</a>
                    <button type="button" class="flex-1 rounded-xl bg-accent px-3 py-2 text-xs font-bold hover:bg-accent-hover" @click="onLogout">خروج</button>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/85 px-5 py-4 backdrop-blur lg:px-8">
                <h1 class="text-lg font-extrabold text-primary">إدارة محتوى ركيزة</h1>
                <p class="text-sm text-slate-500">التعديلات تظهر مباشرة على الصفحة الرئيسية.</p>
            </header>
            <main class="px-5 py-6 lg:px-8">
                <RouterView />
            </main>
        </div>
    </div>
</template>
