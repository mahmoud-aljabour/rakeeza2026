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
const projects = ref([]);
const error = ref('');
const loading = ref(true);
const page = ref(1);
const meta = ref(metaFrom({}, 1));

async function load(nextPage = page.value) {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await axios.get('/api/admin/projects', {
            params: { page: nextPage, per_page: 8 },
        });
        const rows = itemsFrom(data);
        const nextMeta = metaFrom(data, nextPage);

        if (nextMeta.last_page && nextPage > nextMeta.last_page) {
            await load(nextMeta.last_page);
            return;
        }

        projects.value = rows;
        meta.value = nextMeta;
        page.value = nextMeta.current_page;
    } catch (e) {
        projects.value = [];
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}

async function remove(project) {
    if (!confirm(t('projects.confirm_delete', { title: project.title }))) return;
    error.value = '';
    try {
        const { data } = await axios.delete(`/api/admin/projects/${project.id}`);
        toast.fromResponse(data, t('projects.deleted'));
        const nextPage = projects.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;
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
                <h2 class="text-2xl font-black text-primary">{{ t('projects.title') }}</h2>
                <p class="text-sm text-slate-500">{{ t('projects.subtitle') }}</p>
            </div>
            <RouterLink to="/projects/create" class="rounded-xl bg-accent px-4 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover">{{ t('projects.add') }}</RouterLink>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div v-if="loading" class="grid gap-4 md:grid-cols-2">
            <article v-for="item in 4" :key="item" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="h-56 animate-pulse bg-slate-200"></div>
            </article>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2">
            <article v-for="project in projects" :key="project.id" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <img :src="project.image_url" :alt="project.title" class="h-56 w-full object-cover" loading="lazy" decoding="async">
                <span v-if="(project.images_count || project.image_urls?.length || 0) > 1" class="absolute start-3 top-3 rounded-full bg-primary/80 px-2 py-1 text-[11px] font-extrabold text-white">
                    {{ t('projects.photos', { count: project.images_count || project.image_urls.length }) }}
                </span>
                <span v-if="project.service?.title" class="absolute end-3 top-3 max-w-[70%] truncate rounded-full bg-accent px-2 py-1 text-[11px] font-extrabold text-white">
                    {{ project.service.title }}
                </span>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-primary-dark/90 to-transparent p-4 text-white">
                    <h3 class="font-extrabold">{{ project.title }}</h3>
                    <p v-if="project.details" class="mt-1 line-clamp-2 text-xs text-slate-100/90">{{ project.details }}</p>
                    <p class="text-xs text-slate-200">{{ project.service?.title ? t('projects.service_line', { title: project.service.title }) : t('projects.unlinked') }} · {{ t('projects.order', { order: project.order_column }) }}</p>
                    <div class="mt-3 flex gap-2">
                        <RouterLink :to="`/projects/${project.id}/edit`" class="rounded-lg bg-accent px-3 py-1.5 text-xs font-bold">{{ t('edit') }}</RouterLink>
                        <button type="button" class="rounded-lg bg-white/15 px-3 py-1.5 text-xs font-bold" @click="remove(project)">{{ t('delete') }}</button>
                    </div>
                </div>
            </article>
            <p v-if="!projects.length" class="col-span-full rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-slate-400">{{ t('projects.empty') }}</p>
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
