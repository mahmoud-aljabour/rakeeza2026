<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import ImageDropzone from '../../components/ImageDropzone.vue';

const route = useRoute();
const router = useRouter();
const { message } = useApiError();
const toast = useToast();
const isEdit = Boolean(route.params.id);
const error = ref('');
const loading = ref(false);
const preview = ref('');
const imageSource = ref('upload');
const form = reactive({
    title: '',
    description: '',
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
    form.description = data.description || '';
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
    payload.append('description', form.description);
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
            toast.success('تم تعديل الخدمة بنجاح.');
        } else {
            await axios.post('/api/admin/services', payload);
            toast.success('تم إضافة الخدمة بنجاح.');
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
    <section class="mx-auto max-w-2xl space-y-5">
        <h2 class="text-2xl font-black text-primary">{{ isEdit ? 'تعديل الخدمة' : 'إضافة خدمة' }}</h2>
        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

            <label class="mb-2 block text-sm font-extrabold text-primary">عنوان الخدمة</label>
            <input v-model="form.title" required class="mb-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">

            <label class="mb-2 block text-sm font-extrabold text-primary">الوصف</label>
            <textarea v-model="form.description" rows="5" class="mb-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"></textarea>

            <label class="mb-3 flex items-center gap-2 text-sm font-bold">
                <input v-model="form.is_active" type="checkbox" class="size-4 accent-accent">
                إظهار الخدمة في الصفحة الرئيسية
            </label>

            <p class="mb-2 text-sm font-extrabold text-primary">الصورة</p>
            <div class="mb-3 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-extrabold"
                    :class="imageSource === 'url' ? 'bg-accent text-white' : 'border border-slate-200 bg-white text-primary'"
                    @click="useUrl"
                >
                    رابط صورة
                </button>
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-extrabold"
                    :class="imageSource === 'upload' ? 'bg-accent text-white' : 'border border-slate-200 bg-white text-primary'"
                    @click="useUpload"
                >
                    رفع ملف
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
                <p class="mt-2 text-xs text-slate-500">الصق رابط الصورة مباشرة، أو مسارًا من الموقع مثل images/service.jpg</p>
            </div>
            <ImageDropzone
                v-else
                :preview="preview"
                @select="onSelect"
            />

            <img v-if="imageSource === 'url' && preview" :src="preview" alt="" class="mb-4 h-40 w-full rounded-xl object-cover">

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">حفظ</button>
                <RouterLink to="/services" class="rounded-xl border border-slate-200 px-5 py-2.5 font-bold text-slate-600">إلغاء</RouterLink>
            </div>
        </form>
    </section>
</template>
