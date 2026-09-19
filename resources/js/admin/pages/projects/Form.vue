<script setup>
import { onMounted, reactive, ref } from 'vue';
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
const imageSource = ref('upload');
const imageUrl = ref('');
const gallery = ref([]);
const services = ref([]);
const form = reactive({
    title: '',
    title_en: '',
    details: '',
    details_en: '',
    order_column: 0,
    service_id: '',
});

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/admin/services', {
            params: { all: 1 },
        });
        services.value = Array.isArray(data) ? data : [];
    } catch {
        services.value = [];
    }

    if (!isEdit) return;
    const { data } = await axios.get(`/api/admin/projects/${route.params.id}`);
    form.title = data.title;
    form.title_en = data.title_en || '';
    form.details = data.details || '';
    form.details_en = data.details_en || '';
    form.order_column = data.order_column;
    form.service_id = data.service_id ? String(data.service_id) : '';
    gallery.value = (data.image_paths?.length ? data.image_paths : (data.image_path ? [data.image_path] : []))
        .map((path, index) => ({
            id: `existing-${index}-${path}`,
            source: 'existing',
            path,
            preview: data.image_urls?.[index] || previewFromUrl(path),
            file: null,
        }));
});

function previewFromUrl(value) {
    if (!value) {
        return '';
    }

    return value.startsWith('images/') ? `/${value}` : value;
}

function useUrl() {
    imageSource.value = 'url';
}

function useUpload() {
    imageSource.value = 'upload';
}

function addFile(file) {
    if (gallery.value.length >= 12) {
        error.value = t('errors.max_images');
        toast.error(error.value);
        return;
    }

    gallery.value.push({
        id: `file-${Date.now()}-${file.name}`,
        source: 'file',
        path: '',
        preview: URL.createObjectURL(file),
        file,
    });
}

function addUrl() {
    const value = imageUrl.value.trim();

    if (!value) {
        return;
    }

    if (gallery.value.length >= 12) {
        error.value = t('errors.max_images');
        toast.error(error.value);
        return;
    }

    gallery.value.push({
        id: `url-${Date.now()}-${value}`,
        source: 'url',
        path: value,
        preview: previewFromUrl(value),
        file: null,
    });
    imageUrl.value = '';
}

function removeImage(id) {
    gallery.value = gallery.value.filter((item) => item.id !== id);
}

async function submit() {
    error.value = '';
    loading.value = true;
    const payload = new FormData();
    payload.append('title', form.title);
    payload.append('title_en', form.title_en);
    payload.append('details', form.details);
    payload.append('details_en', form.details_en);
    payload.append('order_column', String(form.order_column || 0));
    payload.append('service_id', form.service_id || '');
    payload.append('sync_images', '1');

    gallery.value.forEach((item, index) => {
        payload.append(`items[${index}][source]`, item.source);

        if (item.source === 'existing') {
            payload.append(`items[${index}][path]`, item.path);
        }

        if (item.source === 'url') {
            payload.append(`items[${index}][url]`, item.path);
        }

        if (item.source === 'file' && item.file) {
            payload.append(`items[${index}][image]`, item.file);
        }
    });

    try {
        if (isEdit) {
            await axios.post(`/api/admin/projects/${route.params.id}`, payload);
            toast.success(t('projects.updated'));
        } else {
            await axios.post('/api/admin/projects', payload);
            toast.success(t('projects.created'));
        }
        await router.push({ name: 'projects' });
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="mx-auto max-w-2xl space-y-5">
        <h2 class="text-2xl font-black text-primary">{{ isEdit ? t('projects.edit') : t('projects.create') }}</h2>
        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

            <ContentLocaleTabs v-model="contentTab" class="mb-6">
                <template #ar>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('title') }}</label>
                    <input v-model="form.title" required dir="rtl" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('projects.field_details') }}</label>
                    <textarea v-model="form.details" rows="5" dir="rtl" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary" :placeholder="t('projects.details_placeholder')"></textarea>
                </template>
                <template #en>
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_title') }}</label>
                    <input v-model="form.title_en" class="mb-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary">

                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('common.en_details') }}</label>
                    <textarea v-model="form.details_en" rows="5" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-primary"></textarea>
                </template>
            </ContentLocaleTabs>

            <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('projects.linked_service') }}</label>
            <select v-model="form.service_id" class="mb-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">
                <option value="">{{ t('projects.no_service') }}</option>
                <option v-for="service in services" :key="service.id" :value="String(service.id)">{{ service.title }}</option>
            </select>
            <p class="mb-4 text-xs font-bold text-slate-500">{{ t('projects.link_help') }}</p>

            <label class="mb-2 block text-sm font-extrabold text-primary">{{ t('projects.order_label') }}</label>
            <input v-model.number="form.order_column" type="number" min="0" class="mb-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" dir="ltr">

            <p class="mb-2 text-sm font-extrabold text-primary">{{ t('images') }}</p>
            <p class="mb-3 text-xs font-bold text-slate-500">{{ t('projects.images_help') }}</p>
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

            <div v-if="imageSource === 'url'" class="mb-4 flex flex-col gap-2 sm:flex-row">
                <input
                    v-model="imageUrl"
                    type="text"
                    dir="ltr"
                    placeholder="https://example.com/image.jpg"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"
                    @keydown.enter.prevent="addUrl"
                >
                <button
                    type="button"
                    class="rounded-xl bg-primary px-4 py-3 text-sm font-extrabold text-white hover:bg-primary-light"
                    @click="addUrl"
                >
                    {{ t('add') }}
                </button>
            </div>
            <ImageDropzone
                v-else
                multiple
                @select="addFile"
            />

            <div v-if="gallery.length" class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <article v-for="(item, index) in gallery" :key="item.id" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    <img :src="item.preview" alt="" class="h-28 w-full object-cover">
                    <span v-if="index === 0" class="absolute end-2 top-2 rounded-full bg-accent px-2 py-0.5 text-[10px] font-extrabold text-white">{{ t('cover') }}</span>
                    <button
                        type="button"
                        class="absolute start-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[11px] font-extrabold text-red-600"
                        @click="removeImage(item.id)"
                    >
                        {{ t('delete') }}
                    </button>
                </article>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">{{ t('save') }}</button>
                <RouterLink to="/projects" class="rounded-xl border border-slate-200 px-5 py-2.5 font-bold text-slate-600">{{ t('cancel') }}</RouterLink>
            </div>
        </form>
    </section>
</template>
