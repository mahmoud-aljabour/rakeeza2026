<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import { useLocale } from '../composables/useLocale';

const { t, locale } = useLocale();
const { state: auth } = useAuth();
const stats = ref(null);

const welcomeTitle = computed(() => t('dashboard.title', {
    name: auth.user?.name || t('brand'),
}));


onMounted(async () => {
    const { data } = await axios.get('/api/admin/stats');
    stats.value = data;
});

const cards = computed(() => [
    { key: 'active_services', label: t('dashboard.active_services'), to: '/services', icon: 'fa-screwdriver-wrench' },
    { key: 'projects', label: t('dashboard.projects'), to: '/projects', icon: 'fa-images' },
    { key: 'pending', label: t('dashboard.pending'), to: '/leads', icon: 'fa-inbox' },
    { key: 'craftsmen', label: t('dashboard.craftsmen'), to: '/craftsmen', icon: 'fa-user-gear' },
]);

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
</script>

<template>
    <section class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-primary">{{ welcomeTitle }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ t('dashboard.text') }}</p>
        </div>

        <div v-if="stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <RouterLink
                v-for="card in cards"
                :key="card.key"
                :to="card.to"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-accent"
            >
                <i class="fa-solid mb-3 text-accent" :class="card.icon"></i>
                <p class="text-3xl font-black text-primary">
                    {{ card.key === 'pending' ? stats.leads.pending : card.key === 'craftsmen' ? stats.craftsmen.pending : stats[card.key] }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ card.label }}</p>
            </RouterLink>
        </div>

        <div v-if="stats" class="grid gap-4 xl:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h3 class="text-lg font-black text-primary">{{ t('dashboard.recent_leads') }}</h3>
                    <RouterLink to="/leads" class="text-sm font-bold text-accent hover:text-accent-hover">
                        {{ t('dashboard.view_all') }}
                    </RouterLink>
                </div>
                <ul v-if="recentLeads.length" class="divide-y divide-slate-100">
                    <li v-for="lead in recentLeads" :key="lead.id" class="flex items-start justify-between gap-3 px-5 py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-extrabold text-primary">{{ lead.name }}</p>
                            <a
                                :href="`tel:${lead.phone}`"
                                class="mt-1 inline-flex text-sm font-bold text-slate-600 hover:text-primary"
                                dir="ltr"
                            >{{ lead.phone }}</a>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span
                                    v-for="title in leadServiceTitles(lead).slice(0, 2)"
                                    :key="`${lead.id}-${title}`"
                                    class="inline-flex max-w-[12rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-extrabold text-primary"
                                >
                                    {{ title }}
                                </span>
                                <span
                                    v-if="leadServiceTitles(lead).length > 2"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-black text-accent"
                                    :title="leadServiceTitles(lead).slice(2).join(locale === 'en' ? ', ' : '، ')"
                                >
                                    +{{ leadServiceTitles(lead).length - 2 }}
                                </span>
                            </div>
                        </div>
                        <div class="shrink-0 text-end">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold" :class="statusClass(lead.status)">
                                {{ leadStatus(lead.status) }}
                            </span>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ formatDate(lead.created_at) }}</p>
                        </div>
                    </li>
                </ul>
                <p v-else class="px-5 py-8 text-center text-sm font-bold text-slate-400">{{ t('leads.empty') }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h3 class="text-lg font-black text-primary">{{ t('dashboard.recent_craftsmen') }}</h3>
                    <RouterLink to="/craftsmen" class="text-sm font-bold text-accent hover:text-accent-hover">
                        {{ t('dashboard.view_all') }}
                    </RouterLink>
                </div>
                <ul v-if="recentCraftsmen.length" class="divide-y divide-slate-100">
                    <li v-for="item in recentCraftsmen" :key="item.id" class="flex items-start justify-between gap-3 px-5 py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-extrabold text-primary">{{ item.name }}</p>
                            <a
                                :href="`tel:${item.phone}`"
                                class="mt-1 inline-flex text-sm font-bold text-slate-600 hover:text-primary"
                                dir="ltr"
                            >{{ item.phone }}</a>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span
                                    v-if="item.city"
                                    class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-600"
                                >
                                    {{ item.city }}
                                </span>
                                <span
                                    v-for="specialty in specialtyList(item.specialty).slice(0, 2)"
                                    :key="`${item.id}-${specialty}`"
                                    class="inline-flex max-w-[10rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-extrabold text-primary"
                                >
                                    {{ specialty }}
                                </span>
                                <span
                                    v-if="specialtyList(item.specialty).length > 2"
                                    class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-black text-accent"
                                    :title="specialtyList(item.specialty).slice(2).join(locale === 'en' ? ', ' : '، ')"
                                >
                                    +{{ specialtyList(item.specialty).length - 2 }}
                                </span>
                            </div>
                        </div>
                        <div class="shrink-0 text-end">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold" :class="statusClass(item.status)">
                                {{ craftsmanStatus(item.status) }}
                            </span>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ formatDate(item.created_at) }}</p>
                        </div>
                    </li>
                </ul>
                <p v-else class="px-5 py-8 text-center text-sm font-bold text-slate-400">{{ t('craftsmen.empty') }}</p>
            </article>
        </div>
    </section>
</template>
