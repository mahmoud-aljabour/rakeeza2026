<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../composables/useApiError';
import { useLocale } from '../composables/useLocale';

const { message } = useApiError();
const { t, locale } = useLocale();
const statistics = ref(null);
const error = ref('');

const summary = computed(() => statistics.value?.summary ?? {
    total: 0,
    pending: 0,
    contacted: 0,
    completed: 0,
    closed: 0,
    completed_count: 0,
    total_revenue: 0,
});
const completedLeads = computed(() => statistics.value?.completed_leads ?? []);

const statusCards = computed(() => [
    {
        key: 'total',
        label: t('statistics.total'),
        value: summary.value.total,
        icon: 'fa-chart-simple',
        tone: 'bg-slate-100 text-slate-700',
    },
    {
        key: 'pending',
        label: t('leads.pending'),
        value: summary.value.pending,
        icon: 'fa-inbox',
        tone: 'bg-amber-50 text-amber-700',
    },
    {
        key: 'contacted',
        label: t('leads.contacted'),
        value: summary.value.contacted,
        icon: 'fa-phone',
        tone: 'bg-sky-50 text-sky-700',
    },
    {
        key: 'completed',
        label: t('leads.completed'),
        value: summary.value.completed,
        icon: 'fa-circle-check',
        tone: 'bg-emerald-50 text-emerald-700',
    },
    {
        key: 'closed',
        label: t('leads.closed'),
        value: summary.value.closed,
        icon: 'fa-ban',
        tone: 'bg-slate-100 text-slate-500',
    },
    {
        key: 'total_revenue',
        label: t('statistics.total_revenue'),
        value: formatMoney(summary.value.total_revenue),
        icon: 'fa-coins',
        tone: 'bg-amber-50 text-amber-700',
        isMoney: true,
    },
]);

function servicesList(lead) {
    const titles = Array.isArray(lead.services)
        ? lead.services.map((service) => service.title).filter(Boolean)
        : [];

    if (titles.length) {
        return titles;
    }

    return lead.service?.title ? [lead.service.title] : [];
}

function formatMoney(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'en' ? 'en-GB' : 'ar-EG', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/admin/statistics');
        statistics.value = data;
    } catch (exception) {
        error.value = message(exception);
    }
});
</script>

<template>
    <section class="space-y-5">
        <div>
            <h2 class="text-2xl font-black text-primary">{{ t('statistics.title') }}</h2>
            <p class="text-sm text-slate-500">{{ t('statistics.subtitle') }}</p>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div v-if="statistics" class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
            <article
                v-for="card in statusCards"
                :key="card.key"
                class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm"
            >
                <div class="mb-2 flex h-8 w-8 items-center justify-center rounded-lg text-sm" :class="card.tone">
                    <i class="fa-solid" :class="card.icon"></i>
                </div>
                <p class="text-xl font-black text-primary">{{ card.value }}</p>
                <p class="mt-0.5 text-xs font-bold text-slate-500">{{ card.label }}</p>
            </article>
        </div>

        <div v-if="statistics" class="space-y-3">
            <div>
                <h3 class="text-lg font-black text-primary">{{ t('statistics.completed_section') }}</h3>
                <p class="text-sm text-slate-500">{{ t('statistics.completed_section_hint') }}</p>
            </div>

            <div class="admin-table-scroll rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[48rem] text-start text-sm">
                    <thead class="bg-slate-50 text-primary">
                        <tr>
                            <th class="px-4 py-3 font-extrabold">{{ t('name') }}</th>
                            <th class="px-4 py-3 font-extrabold">{{ t('phone') }}</th>
                            <th class="px-4 py-3 font-extrabold">{{ t('service') }}</th>
                            <th class="px-4 py-3 font-extrabold">{{ t('statistics.price') }}</th>
                            <th class="px-4 py-3 font-extrabold">{{ t('statistics.completed_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lead in completedLeads" :key="lead.id" class="border-t border-slate-100">
                            <td class="whitespace-nowrap px-4 py-3 font-bold text-primary">{{ lead.name }}</td>
                            <td class="whitespace-nowrap px-4 py-3" dir="ltr">{{ lead.phone }}</td>
                            <td class="px-4 py-3">
                                <div class="flex max-w-xs items-center gap-1.5">
                                    <span class="inline-flex max-w-[11rem] truncate rounded-full bg-primary/8 px-2.5 py-1 text-xs font-extrabold text-primary">
                                        {{ servicesList(lead)[0] || t('leads.general') }}
                                    </span>
                                    <span
                                        v-if="servicesList(lead).length > 1"
                                        class="inline-flex min-w-7 items-center justify-center rounded-full bg-accent/15 px-2 py-1 text-xs font-black text-accent"
                                        :title="servicesList(lead).slice(1).join(locale === 'en' ? ', ' : '، ')"
                                    >
                                        +{{ servicesList(lead).length - 1 }}
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-extrabold text-emerald-700">{{ formatMoney(lead.completed_price) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatDate(lead.completed_at) }}</td>
                        </tr>
                        <tr v-if="!completedLeads.length">
                            <td colspan="5" class="px-4 py-10 text-center text-slate-400">{{ t('statistics.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
