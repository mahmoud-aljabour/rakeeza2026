<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../composables/useApiError';
import { useAuth } from '../composables/useAuth';
import { useLocale } from '../composables/useLocale';

const { message } = useApiError();
const { t, locale } = useLocale();
const { state: auth } = useAuth();
const stats = ref(null);
const loading = ref(true);
const error = ref('');

const welcomeTitle = computed(() => t('dashboard.title', {
    name: auth.user?.name || t('brand'),
}));

const todayLabel = computed(() => new Intl.DateTimeFormat(locale.value === 'en' ? 'en-GB' : 'ar-EG', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
}).format(new Date()));

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/admin/stats');
        stats.value = data;
    } catch (exception) {
        error.value = message(exception);
    } finally {
        loading.value = false;
    }
});

const cards = computed(() => {
    const pendingLeads = stats.value?.leads?.pending ?? 0;
    const pendingCraftsmen = stats.value?.craftsmen?.pending ?? 0;

    return [
        {
            key: 'active_services',
            label: t('dashboard.active_services'),
            to: '/services',
            icon: 'fa-screwdriver-wrench',
            tone: 'bg-primary/10 text-primary',
            value: stats.value?.active_services ?? 0,
            urgent: false,
        },
        {
            key: 'projects',
            label: t('dashboard.projects'),
            to: '/projects',
            icon: 'fa-images',
            tone: 'bg-sky-50 text-sky-700',
            value: stats.value?.projects ?? 0,
            urgent: false,
        },
        {
            key: 'pending',
            label: t('dashboard.pending'),
            to: '/leads',
            icon: 'fa-inbox',
            tone: 'bg-amber-50 text-amber-700',
            value: pendingLeads,
            urgent: pendingLeads > 0,
        },
        {
            key: 'craftsmen',
            label: t('dashboard.craftsmen'),
            to: '/craftsmen',
            icon: 'fa-user-gear',
            tone: 'bg-orange-50 text-accent',
            value: pendingCraftsmen,
            urgent: pendingCraftsmen > 0,
        },
    ];
});

const recentLeads = computed(() => stats.value?.recent_leads ?? []);
const recentCraftsmen = computed(() => stats.value?.recent_craftsmen ?? []);

function leadStatus(value) {
    return t(`leads.${value}`);
}

function leadServiceTitles(lead) {
    const titles = Array.isArray(lead.services)
        ? lead.services.map((service) => service.title).filter(Boolean)
        : [];

    if (titles.length) {
        return titles;
    }

    if (lead.service?.title) {
        return [lead.service.title];
    }

    return [t('leads.general')];
}

function specialtyList(value) {
    if (Array.isArray(value)) {
        return value.filter((specialty) => typeof specialty === 'string' && specialty.trim());
    }

    if (typeof value !== 'string' || !value.trim()) {
        return [];
    }

    try {
        const decoded = JSON.parse(value);

        return Array.isArray(decoded) ? specialtyList(decoded) : [value];
    } catch {
        return [value];
    }
}

function craftsmanStatus(value) {
    return t(`craftsmen.${value}`);
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(locale.value === 'en' ? 'en-GB' : 'ar-EG', {
        day: 'numeric',
        month: 'short',
    }).format(new Date(value));
}

function statusClass(value) {
    if (value === 'completed' || value === 'converted' || value === 'accepted') {
        return 'bg-emerald-50 text-emerald-700';
    }

    if (value === 'contacted' || value === 'reviewing') {
        return 'bg-sky-50 text-sky-700';
    }

    if (value === 'closed' || value === 'rejected') {
        return 'bg-slate-100 text-slate-500';
    }

    return 'bg-amber-50 text-amber-700';
}

function initials(name) {
    if (!name || typeof name !== 'string') {
        return '?';
    }

    const parts = name.trim().split(/\s+/).filter(Boolean).slice(0, 2);

    return parts.map((part) => part.charAt(0)).join('').toUpperCase() || '?';
}
</script>

<template>
    <section class="space-y-6">
        <header class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="absolute inset-y-0 start-0 w-1.5 bg-accent" aria-hidden="true" />
            <div class="absolute -end-16 -top-16 h-40 w-40 rounded-full bg-primary/5" aria-hidden="true" />
            <div class="absolute -bottom-20 end-20 h-36 w-36 rounded-full bg-accent/10" aria-hidden="true" />
            <div class="relative flex flex-col gap-3 px-5 py-5 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div class="min-w-0">
                    <p class="text-xs font-semibold tracking-wide text-accent uppercase">{{ t('brand') }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-primary sm:text-[1.75rem]">{{ welcomeTitle }}</h2>
                    <p class="mt-1.5 max-w-xl text-sm font-normal leading-relaxed text-slate-500">{{ t('dashboard.text') }}</p>
                </div>
                <p class="shrink-0 text-sm font-medium text-slate-400">{{ todayLabel }}</p>
            </div>
        </header>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div v-if="loading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-hidden="true">
            <div
                v-for="n in 4"
                :key="n"
                class="h-32 animate-pulse rounded-2xl border border-slate-200 bg-slate-100"
            />
        </div>

        <div v-else-if="stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <RouterLink
                v-for="card in cards"
                :key="card.key"
                :to="card.to"
                class="group relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                :class="card.urgent ? 'border-accent/40 ring-1 ring-accent/20' : 'border-slate-200 hover:border-accent/50'"
            >
                <div class="flex items-start justify-between gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl text-base transition group-hover:scale-105"
                        :class="card.tone"
                    >
                        <i class="fa-solid" :class="card.icon"></i>
                    </div>
                    <span
                        v-if="card.urgent"
                        class="inline-flex items-center rounded-full bg-accent/15 px-2.5 py-1 text-[11px] font-semibold text-accent"
                    >
                        {{ t('dashboard.needs_attention') }}
                    </span>
                    <i
                        v-else
                        class="fa-solid fa-arrow-right text-sm text-slate-300 transition group-hover:text-accent rtl:rotate-180"
                    ></i>
                </div>
                <p class="mt-4 text-3xl font-bold tracking-tight text-primary">{{ card.value }}</p>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ card.label }}</p>
            </RouterLink>
        </div>

        <div v-if="stats" class="grid gap-4 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <i class="fa-solid fa-inbox text-sm"></i>
                        </div>
                        <h3 class="truncate text-lg font-semibold text-primary">{{ t('dashboard.recent_leads') }}</h3>
                    </div>
                    <RouterLink
                        to="/leads"
                        class="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-accent transition hover:text-accent-hover"
                    >
                        {{ t('dashboard.view_all') }}
                        <i class="fa-solid fa-arrow-right text-xs rtl:rotate-180"></i>
                    </RouterLink>
                </div>
                <ul v-if="recentLeads.length" class="divide-y divide-slate-100">
                    <li
                        v-for="lead in recentLeads"
                        :key="lead.id"
                        class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50/80"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                            aria-hidden="true"
                        >
                            {{ initials(lead.name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-primary">{{ lead.name }}</p>
                                    <a
                                        :href="`tel:${lead.phone}`"
                                        class="mt-0.5 inline-flex text-sm font-medium text-slate-500 transition hover:text-primary"
                                        dir="ltr"
                                    >{{ lead.phone }}</a>
                                </div>
                                <div class="shrink-0 text-end">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClass(lead.status)"
                                    >
                                        {{ leadStatus(lead.status) }}
                                    </span>
                                    <p class="mt-1 text-xs font-medium text-slate-400">{{ formatDate(lead.created_at) }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <span
                                    v-for="title in leadServiceTitles(lead).slice(0, 2)"
                                    :key="`${lead.id}-${title}`"
                                    class="inline-flex max-w-[12rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-medium text-primary"
                                >
                                    {{ title }}
                                </span>
                                <span
                                    v-if="leadServiceTitles(lead).length > 2"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-semibold text-accent"
                                    :title="leadServiceTitles(lead).slice(2).join(locale === 'en' ? ', ' : '، ')"
                                >
                                    +{{ leadServiceTitles(lead).length - 2 }}
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div v-else class="flex flex-col items-center px-5 py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-400">{{ t('leads.empty') }}</p>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-accent">
                            <i class="fa-solid fa-user-gear text-sm"></i>
                        </div>
                        <h3 class="truncate text-lg font-semibold text-primary">{{ t('dashboard.recent_craftsmen') }}</h3>
                    </div>
                    <RouterLink
                        to="/craftsmen"
                        class="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-accent transition hover:text-accent-hover"
                    >
                        {{ t('dashboard.view_all') }}
                        <i class="fa-solid fa-arrow-right text-xs rtl:rotate-180"></i>
                    </RouterLink>
                </div>
                <ul v-if="recentCraftsmen.length" class="divide-y divide-slate-100">
                    <li
                        v-for="item in recentCraftsmen"
                        :key="item.id"
                        class="flex items-start gap-3 px-5 py-4 transition hover:bg-slate-50/80"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent/10 text-xs font-semibold text-accent"
                            aria-hidden="true"
                        >
                            {{ initials(item.name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-primary">{{ item.name }}</p>
                                    <a
                                        :href="`tel:${item.phone}`"
                                        class="mt-0.5 inline-flex text-sm font-medium text-slate-500 transition hover:text-primary"
                                        dir="ltr"
                                    >{{ item.phone }}</a>
                                </div>
                                <div class="shrink-0 text-end">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClass(item.status)"
                                    >
                                        {{ craftsmanStatus(item.status) }}
                                    </span>
                                    <p class="mt-1 text-xs font-medium text-slate-400">{{ formatDate(item.created_at) }}</p>
                                </div>
                            </div>
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                <span
                                    v-if="item.city"
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"
                                >
                                    {{ item.city }}
                                </span>
                                <span
                                    v-for="specialty in specialtyList(item.specialty).slice(0, 2)"
                                    :key="`${item.id}-${specialty}`"
                                    class="inline-flex max-w-[10rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-medium text-primary"
                                >
                                    {{ specialty }}
                                </span>
                                <span
                                    v-if="specialtyList(item.specialty).length > 2"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-semibold text-accent"
                                    :title="specialtyList(item.specialty).slice(2).join(locale === 'en' ? ', ' : '، ')"
                                >
                                    +{{ specialtyList(item.specialty).length - 2 }}
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div v-else class="flex flex-col items-center px-5 py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-400">{{ t('craftsmen.empty') }}</p>
                </div>
            </article>
        </div>
    </section>
</template>
