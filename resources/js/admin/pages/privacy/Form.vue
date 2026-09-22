<script setup>
import { onMounted, reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';
import ContentLocaleTabs from '../../components/ContentLocaleTabs.vue';

const contentTab = ref('ar');
const route = useRoute();
const router = useRouter();
const { message } = useApiError();
const toast = useToast();
const { t } = useLocale();
const isEdit = Boolean(route.params.id);
const error = ref('');
const loading = ref(false);
const form = reactive({
    title: '',
    title_en: '',
    description: '',
    description_en: '',
    order_column: 1,
    is_active: true,
});

onMounted(async () => {
    if (isEdit) {
        const { data } = await axios.get(`/api/admin/privacy-policy/${route.params.id}`);
        form.title = data.title || '';
        form.title_en = data.title_en || '';
        form.description = data.description || '';
        form.description_en = data.description_en || '';
        form.order_column = data.order_column ?? 0;
        form.is_active = data.is_active;
        return;
    }

    const { data } = await axios.get('/api/admin/privacy-policy', { params: { all: 1 } });
    const rows = Array.isArray(data) ? data : [];
    const max = rows.reduce((highest, row) => Math.max(highest, Number(row.order_column) || 0), 0);
    form.order_column = rows.length === 0 ? 1 : max + 1;
});

async function submit() {
    error.value = '';
    loading.value = true;

    const payload = {
        title: form.title,
        title_en: form.title_en,
        description: form.description,
        description_en: form.description_en,
        order_column: form.order_column || 0,
        is_active: form.is_active,
    };

    try {
        if (isEdit) {
            await axios.put(`/api/admin/privacy-policy/${route.params.id}`, payload);
            toast.success(t('privacy.updated'));
        } else {
            await axios.post('/api/admin/privacy-policy', payload);
            toast.success(t('privacy.created'));
        }

        await router.push({ name: 'privacy' });
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="mx-auto max-w-3xl space-y-5">
        <h2 class="text-2xl font-black text-primary">{{ isEdit ? t('privacy.edit') : t('privacy.create') }}</h2>
        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

            <ContentLocaleTabs v-model="contentTab" class="mb-6">
                <template #ar>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('privacy.field_title') }}</label>
                    <input v-model="form.title" required dir="rtl" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('description') }}</label>
                    <textarea v-model="form.description" required rows="6" dir="rtl" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                </template>
                <template #en>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_title') }}</label>
                    <input v-model="form.title_en" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_description') }}</label>
                    <textarea v-model="form.description_en" rows="6" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                </template>
            </ContentLocaleTabs>

            <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('privacy.order_label') }}</label>
            <input v-model.number="form.order_column" type="number" min="0" class="mb-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" dir="ltr">
            <p class="mb-4 text-xs font-bold text-slate-500">{{ t('privacy.order_help') }}</p>

            <label class="mb-6 flex items-center gap-2 text-sm font-bold">
                <input v-model="form.is_active" type="checkbox" class="size-4 accent-accent">
                {{ t('privacy.show_on_page') }}
            </label>

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">{{ t('save') }}</button>
                <RouterLink to="/privacy-policy" class="rounded-xl border border-slate-200 px-5 py-2.5 font-bold text-slate-600">{{ t('cancel') }}</RouterLink>
            </div>
        </form>
    </section>
</template>
