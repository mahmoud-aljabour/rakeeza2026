<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';
import { itemsFrom, metaFrom } from '../../composables/usePaginatedList';
import PaginationBar from '../../components/PaginationBar.vue';

const { message } = useApiError();
const toast = useToast();
const { t } = useLocale();
const sections = ref([]);
const error = ref('');
const loading = ref(true);
const page = ref(1);
const meta = ref(metaFrom({}, 1));

async function load(nextPage = page.value) {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await axios.get('/api/admin/privacy-policy', {
            params: { page: nextPage, per_page: 12 },
        });
        const rows = itemsFrom(data);
        const nextMeta = metaFrom(data, nextPage);

        if (nextMeta.last_page && nextPage > nextMeta.last_page) {
            await load(nextMeta.last_page);
            return;
        }

        sections.value = rows;
        meta.value = nextMeta;
        page.value = nextMeta.current_page;
    } catch (e) {
        sections.value = [];
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}

async function remove(section) {
    if (!confirm(t('privacy.confirm_delete', { title: section.title }))) {
        return;
    }

    error.value = '';

    try {
        const { data } = await axios.delete(`/api/admin/privacy-policy/${section.id}`);
        toast.fromResponse(data, t('privacy.deleted'));
        const nextPage = sections.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;
        await load(nextPage);
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

onMounted(() => load(1));
</script>

<template>
    <section class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-primary">{{ t('privacy.title') }}</h2>
                <p class="text-sm text-slate-500">{{ t('privacy.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/privacy-policy" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-extrabold text-primary">{{ t('privacy.view_page') }}</a>
                <RouterLink to="/privacy-policy/create" class="rounded-xl bg-accent px-4 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover">{{ t('privacy.add') }}</RouterLink>
            </div>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div v-if="loading" class="space-y-3">
            <article v-for="item in 4" :key="item" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 h-4 w-1/3 animate-pulse rounded bg-slate-200"></div>
                <div class="h-3 w-full animate-pulse rounded bg-slate-100"></div>
            </article>
        </div>

        <div v-else class="space-y-3">
            <article v-for="section in sections" :key="section.id" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-extrabold text-primary">{{ section.title }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ t('privacy.order', { order: section.order_column }) }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="section.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                            {{ section.is_active ? t('privacy.visible') : t('privacy.hidden') }}
                        </span>
                    </div>
                </div>
                <p class="line-clamp-3 text-sm text-slate-500">{{ section.description }}</p>
                <div class="mt-4 flex gap-2">
                    <RouterLink :to="`/privacy-policy/${section.id}/edit`" class="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white">{{ t('edit') }}</RouterLink>
                    <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700" @click="remove(section)">{{ t('delete') }}</button>
                </div>
            </article>
            <p v-if="!sections.length" class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-slate-400">{{ t('privacy.empty') }}</p>
        </div>

        <PaginationBar
            :page="page"
            :last-page="meta.last_page"
            :total="meta.total"
            :from="meta.from"
            :to="meta.to"
            @change="load"
        />
    </section>
</template>
