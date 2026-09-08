<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { itemsFrom, metaFrom } from '../../composables/usePaginatedList';
import PaginationBar from '../../components/PaginationBar.vue';

const { message } = useApiError();
const toast = useToast();
const services = ref([]);
const error = ref('');
const loading = ref(true);
const page = ref(1);
const meta = ref(metaFrom({}, 1));

async function load(nextPage = page.value) {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await axios.get('/api/admin/services', {
            params: { page: nextPage, per_page: 9 },
        });
        const rows = itemsFrom(data);
        const nextMeta = metaFrom(data, nextPage);

        if (nextMeta.last_page && nextPage > nextMeta.last_page) {
            await load(nextMeta.last_page);
            return;
        }

        services.value = rows;
        meta.value = nextMeta;
        page.value = nextMeta.current_page;
    } catch (e) {
        services.value = [];
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}

async function remove(service) {
    if (!confirm(`حذف خدمة "${service.title}"؟`)) return;
    error.value = '';
    try {
        const { data } = await axios.delete(`/api/admin/services/${service.id}`);
        toast.fromResponse(data, 'تم حذف الخدمة بنجاح.');
        const nextPage = services.value.length === 1 && page.value > 1 ? page.value - 1 : page.value;
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
                <h2 class="text-2xl font-black text-primary">الخدمات</h2>
                <p class="text-sm text-slate-500">تظهر الخدمات النشطة فقط في الصفحة الرئيسية.</p>
            </div>
            <RouterLink to="/services/create" class="rounded-xl bg-accent px-4 py-2.5 text-sm font-extrabold text-white hover:bg-accent-hover">إضافة خدمة</RouterLink>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div v-if="loading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article v-for="item in 6" :key="item" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="h-40 animate-pulse bg-slate-200"></div>
                <div class="space-y-3 p-4">
                    <div class="h-4 w-2/3 animate-pulse rounded bg-slate-200"></div>
                    <div class="h-3 w-full animate-pulse rounded bg-slate-100"></div>
                    <div class="h-3 w-4/5 animate-pulse rounded bg-slate-100"></div>
                </div>
            </article>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article v-for="service in services" :key="service.id" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <img :src="service.image_url" :alt="service.title" class="h-40 w-full object-cover" loading="lazy" decoding="async">
                <div class="p-4">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <h3 class="font-extrabold text-primary">{{ service.title }}</h3>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="service.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                            {{ service.is_active ? 'ظاهرة' : 'مخفية' }}
                        </span>
                    </div>
                    <p class="line-clamp-3 text-sm text-slate-500">{{ service.description }}</p>
                    <div class="mt-4 flex gap-2">
                        <RouterLink :to="`/services/${service.id}/edit`" class="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white">تعديل</RouterLink>
                        <button type="button" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700" @click="remove(service)">حذف</button>
                    </div>
                </div>
            </article>
            <p v-if="!services.length" class="col-span-full rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-slate-400">لا توجد خدمات بعد.</p>
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
