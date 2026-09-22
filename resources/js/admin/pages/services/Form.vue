<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';
import ImageDropzone from '../../components/ImageDropzone.vue';
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
const preview = ref('');
const imageSource = ref('upload');
const form = reactive({
    title: '',
    title_en: '',
    slug: '',
    description: '',
    description_en: '',
    body: '',
    body_en: '',
    seo_title: '',
    seo_title_en: '',
    meta_description: '',
    meta_description_en: '',
    projects_count: 0,
    is_active: true,
    image: null,
    image_url: '',
});

onMounted(async () => {
    if (!isEdit) {
        return;
    }

    const { data } = await axios.get(`/api/admin/services/${route.params.id}`);
    form.title = data.title;
    form.title_en = data.title_en || '';
    form.slug = data.slug || '';
    form.description = data.description || '';
    form.description_en = data.description_en || '';
    form.body = data.body || '';
    form.body_en = data.body_en || '';
    form.seo_title = data.seo_title || '';
    form.seo_title_en = data.seo_title_en || '';
    form.meta_description = data.meta_description || '';
    form.meta_description_en = data.meta_description_en || '';
    form.projects_count = data.projects_count ?? 0;
    form.is_active = data.is_active;
    preview.value = data.image_url;

    if (isLinkedPath(data.image_path)) {
        imageSource.value = 'url';
        form.image_url = data.image_path;
        return;
    }

    imageSource.value = 'upload';
});

function isLinkedPath(path) {
    return typeof path === 'string'
        && (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('images/'));
}

function previewFromUrl(value) {
    if (!value) {
        return '';
    }

    return value.startsWith('images/') ? `/${value}` : value;
}

function useUrl() {
    imageSource.value = 'url';
    form.image = null;
    preview.value = previewFromUrl(form.image_url) || preview.value;
}

function useUpload() {
    imageSource.value = 'upload';
}

function onSelect(file) {
    form.image = file;
    preview.value = URL.createObjectURL(file);
}

watch(() => form.image_url, (value) => {
    if (imageSource.value === 'url') {
        preview.value = previewFromUrl(value);
    }
});

async function submit() {
    error.value = '';
    loading.value = true;
    const payload = new FormData();
    payload.append('title', form.title);
    payload.append('title_en', form.title_en);
    payload.append('slug', form.slug);
    payload.append('description', form.description);
    payload.append('description_en', form.description_en);
    payload.append('body', form.body);
    payload.append('body_en', form.body_en);
    payload.append('seo_title', form.seo_title);
    payload.append('seo_title_en', form.seo_title_en);
    payload.append('meta_description', form.meta_description);
    payload.append('meta_description_en', form.meta_description_en);
    payload.append('projects_count', String(form.projects_count ?? 0));
    payload.append('is_active', form.is_active ? '1' : '0');

    if (imageSource.value === 'upload' && form.image) {
        payload.append('image', form.image);
    }

    if (imageSource.value === 'url' && form.image_url.trim()) {
        payload.append('image_url', form.image_url.trim());
    }

    try {
        if (isEdit) {
            await axios.post(`/api/admin/services/${route.params.id}`, payload);
            toast.success(t('services.updated'));
        } else {
            await axios.post('/api/admin/services', payload);
            toast.success(t('services.created'));
        }
        await router.push({ name: 'services' });
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
        <h2 class="text-2xl font-black text-primary">{{ isEdit ? t('services.edit') : t('services.create') }}</h2>
        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

            <ContentLocaleTabs v-model="contentTab" class="mb-6">
                <template #ar>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.field_title') }}</label>
                    <input v-model="form.title" required dir="rtl" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('description') }}</label>
                    <textarea v-model="form.description" rows="4" dir="rtl" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.field_body') }}</label>
                    <textarea v-model="form.body" rows="12" dir="rtl" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                    <p class="mb-4 mt-2 text-xs font-bold text-slate-500">{{ t('services.field_body_help') }}</p>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.seo_title') }}</label>
                    <input v-model="form.seo_title" maxlength="120" dir="rtl" class="mb-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">
                    <p class="mb-4 text-xs font-bold text-slate-500">{{ t('services.seo_title_help') }}</p>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.meta_description') }}</label>
                    <textarea v-model="form.meta_description" rows="3" maxlength="160" dir="rtl" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                    <p class="mt-2 text-xs font-bold text-slate-500">{{ t('services.meta_description_help') }}</p>
                </template>
                <template #en>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_title') }}</label>
                    <input v-model="form.title_en" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_description') }}</label>
                    <textarea v-model="form.description_en" rows="4" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.field_body') }}</label>
                    <textarea v-model="form.body_en" rows="12" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                    <p class="mb-4 mt-2 text-xs font-bold text-slate-500">{{ t('services.field_body_help') }}</p>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.seo_title') }}</label>
                    <input v-model="form.seo_title_en" maxlength="120" class="mb-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">
                    <p class="mb-4 text-xs font-bold text-slate-500">{{ t('services.seo_title_help') }}</p>

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.meta_description') }}</label>
                    <textarea v-model="form.meta_description_en" rows="3" maxlength="160" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                    <p class="mt-2 text-xs font-bold text-slate-500">{{ t('services.meta_description_help') }}</p>
                </template>
            </ContentLocaleTabs>

            <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.slug') }}</label>
            <input v-model="form.slug" dir="ltr" maxlength="80" class="mb-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary" placeholder="debris-removal">
            <p class="mb-4 text-xs font-bold text-slate-500">{{ t('services.slug_help') }}</p>

            <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('services.projects_count') }}</label>
            <input
                v-model.number="form.projects_count"
                type="number"
                min="0"
                dir="ltr"
                class="mb-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"
            >
            <p class="mb-4 text-xs font-bold text-slate-500">{{ t('services.projects_count_help') }}</p>

            <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                <input v-model="form.is_active" type="checkbox" class="size-4 accent-accent">
                {{ t('services.show_home') }}
            </label>

            <p class="mb-2 text-sm font-extrabold text-primary">{{ t('image') }}</p>
            <div class="mb-3 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-extrabold"
                    :class="imageSource === 'url' ? 'bg-accent text-white' : 'border border-slate-200 bg-white text-primary'"
                    @click="useUrl"
                >
                    {{ t('common.image_url') }}
                </button>
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-extrabold"
                    :class="imageSource === 'upload' ? 'bg-accent text-white' : 'border border-slate-200 bg-white text-primary'"
                    @click="useUpload"
                >
                    {{ t('common.upload_file') }}
                </button>
            </div>

            <div v-if="imageSource === 'url'" class="mb-4">
                <input
                    v-model="form.image_url"
                    type="text"
                    dir="ltr"
                    placeholder="https://example.com/image.jpg"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"
                >
                <p class="mt-2 text-xs text-slate-500">{{ t('common.image_url_help') }}</p>
            </div>
            <ImageDropzone
                v-else
                :preview="preview"
                @select="onSelect"
            />

            <img v-if="imageSource === 'url' && preview" :src="preview" alt="" class="mb-4 h-40 w-full rounded-xl object-cover">

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">{{ t('save') }}</button>
                <RouterLink to="/services" class="rounded-xl border border-slate-200 px-5 py-2.5 font-bold text-slate-600">{{ t('cancel') }}</RouterLink>
            </div>
        </form>
    </section>
</template>
