<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useLocale } from '../composables/useLocale';
import LangSwitch from './LangSwitch.vue';

const router = useRouter();
const route = useRoute();
const { state, logout } = useAuth();
const { t, isRtl } = useLocale();
const menuOpen = ref(false);
const isDesktop = ref(window.matchMedia('(min-width: 1025px)').matches);

const asideShift = computed(() => {
    if (isDesktop.value) {
        return '';
    }

    if (menuOpen.value) {
        return 'translate-x-0';
    }

    return isRtl.value ? 'translate-x-full' : '-translate-x-full';
});

const links = computed(() => [
    { to: '/', label: t('nav.dashboard'), icon: 'fa-gauge-high', exact: true },
    { to: '/leads', label: t('nav.leads'), icon: 'fa-inbox' },
    { to: '/craftsmen', label: t('nav.craftsmen'), icon: 'fa-user-gear' },
    { to: '/statistics', label: t('nav.statistics'), icon: 'fa-chart-column' },
    { to: '/services', label: t('nav.services'), icon: 'fa-screwdriver-wrench' },
    { to: '/projects', label: t('nav.projects'), icon: 'fa-images' },
    { to: '/settings', label: t('nav.settings'), icon: 'fa-sliders' },
    { to: '/password', label: t('nav.password'), icon: 'fa-shield-halved' },
]);

watch(() => route.fullPath, () => {
    menuOpen.value = false;
});

function closeMenu() {
    menuOpen.value = false;
}

function syncDesktop() {
    isDesktop.value = window.matchMedia('(min-width: 1025px)').matches;

    if (isDesktop.value) {
        closeMenu();
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        closeMenu();
    }
}

watch(menuOpen, (open) => {
    document.body.classList.toggle('overflow-hidden', open);
});

onMounted(() => {
    syncDesktop();
    window.addEventListener('resize', syncDesktop);
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('resize', syncDesktop);
    window.removeEventListener('keydown', onKeydown);
    document.body.classList.remove('overflow-hidden');
});

async function onLogout() {
    await logout();
    router.push('/login');
}
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] text-slate-800">
        <button
            v-if="menuOpen"
            type="button"
            class="fixed inset-0 z-40 bg-primary-dark/50 lg:hidden"
            :aria-label="t('close_menu')"
            @click="closeMenu"
        />

        <aside
            class="fixed inset-y-0 start-0 z-50 flex h-dvh w-[min(18rem,86vw)] flex-col overflow-hidden bg-primary-dark text-white transition-transform duration-300 lg:w-72"
            :class="asideShift"
        >
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-5 py-4">
                <a href="/" class="flex min-w-0 flex-1 items-center gap-3" :aria-label="t('panel_full')">
                    <img
                        src="/images/logo.png"
                        alt=""
                        width="63"
                        height="40"
                        class="h-10 w-auto max-h-10 shrink-0 object-contain"
                    >
                    <div class="min-w-0 leading-tight">
                        <p class="text-[11px] font-bold tracking-[0.16em] text-accent uppercase">{{ t('brand') }}</p>
                        <p class="truncate text-base font-extrabold">{{ t('panel') }}</p>
                    </div>
                </a>
                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full text-white lg:hidden"
                    :aria-label="t('close_menu')"
                    @click="closeMenu"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto overscroll-contain p-4">
                <RouterLink
                    v-for="link in links"
                    :key="link.to"
                    v-slot="{ href, navigate, isActive, isExactActive }"
                    :to="link.to"
                    custom
                >
                    <a
                        :href="href"
                        class="flex min-h-11 items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold transition"
                        :class="(link.exact ? isExactActive : isActive) ? 'bg-accent text-white' : 'text-slate-200 hover:bg-white/10'"
                        @click="navigate"
                    >
                        <i class="fa-solid w-4" :class="link.icon"></i>
                        {{ link.label }}
                    </a>
                </RouterLink>
            </nav>

            <div class="shrink-0 border-t border-white/10 p-4">
                <p class="mb-3 text-xs text-slate-300">{{ state.user?.name }}</p>
                <div class="flex gap-2">
                    <a href="/" class="flex min-h-11 flex-1 items-center justify-center rounded-xl border border-white/15 px-3 py-2 text-center text-xs font-bold hover:bg-white/8">{{ t('site') }}</a>
                    <button type="button" class="flex min-h-11 flex-1 items-center justify-center rounded-xl bg-accent px-3 py-2 text-xs font-bold hover:bg-accent-hover" @click="onLogout">{{ t('logout') }}</button>
                </div>
            </div>
        </aside>

        <div class="min-w-0 lg:ps-72">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/85 px-5 py-4 backdrop-blur lg:px-8">
                <button
                    type="button"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-slate-200 text-primary lg:hidden"
                    :aria-label="t('open_menu')"
                    :aria-expanded="menuOpen"
                    @click="menuOpen = true"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold text-primary">{{ t('header_title') }}</h1>
                    <p class="text-sm text-slate-500">{{ t('header_subtitle') }}</p>
                </div>
                <LangSwitch class="ms-auto shrink-0" />
            </header>
            <main class="px-5 py-6 lg:px-8">
                <RouterView />
            </main>
        </div>
    </div>
</template>
