<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';

const { message } = useApiError();
const toast = useToast();
const tab = ref('contact');
const error = ref('');
const success = ref('');
const loading = ref(false);
const form = reactive({});

const tabs = [
    { id: 'contact', label: 'بيانات التواصل' },
    { id: 'hero', label: 'الهيرو ومن نحن' },
    { id: 'more', label: 'الحرفيين ولماذا ركيزة' },
];

const groups = {
    contact: [
        { key: 'phone', label: 'الهاتف' },
        { key: 'email', label: 'البريد الإلكتروني' },
        { key: 'whatsapp', label: 'واتساب (بدون +)' },
        { key: 'hours', label: 'ساعات العمل' },
        { key: 'contact_intro', label: 'مقدمة قسم اتصل بنا', type: 'textarea' },
        { key: 'footer_about', label: 'نص الفوتر', type: 'textarea' },
    ],
    hero: [
        { key: 'hero_kicker', label: 'كلمة الهيرو العلوية' },
        { key: 'hero_title', label: 'عنوان الهيرو' },
        { key: 'hero_highlight', label: 'الكلمة المميزة في العنوان' },
        { key: 'hero_text', label: 'وصف الهيرو', type: 'textarea' },
        { key: 'about_text', label: 'نص من نحن', type: 'textarea' },
        { key: 'about_highlight_1', label: 'نقطة تميز 1' },
        { key: 'about_highlight_2', label: 'نقطة تميز 2' },
        { key: 'vision_text', label: 'نص الرؤية', type: 'textarea' },
    ],
    more: [
        { key: 'craftsman_kicker', label: 'عنوان قسم الحرفي الصغير' },
        { key: 'craftsman_title', label: 'عنوان قسم الحرفي' },
        { key: 'craftsman_text', label: 'وصف قسم الحرفي', type: 'textarea' },
        { key: 'craftsman_feature_1', label: 'ميزة الحرفي 1' },
        { key: 'craftsman_feature_2', label: 'ميزة الحرفي 2' },
        { key: 'why_kicker', label: 'عنوان لماذا ركيزة الصغير' },
        { key: 'why_title', label: 'عنوان لماذا ركيزة' },
        { key: 'why_lead', label: 'وصف لماذا ركيزة', type: 'textarea' },
        { key: 'why_item_1', label: 'سبب 1' },
        { key: 'why_item_2', label: 'سبب 2' },
        { key: 'why_item_3', label: 'سبب 3' },
        { key: 'why_item_4', label: 'سبب 4' },
        { key: 'why_item_5', label: 'سبب 5' },
        { key: 'why_item_6', label: 'سبب 6' },
        { key: 'why_caption_title', label: 'عنوان صورة لماذا ركيزة' },
        { key: 'why_caption_text', label: 'وصف صورة لماذا ركيزة' },
    ],
};

onMounted(async () => {
    const { data } = await axios.get('/api/admin/settings');
    Object.assign(form, data);
});

async function submit() {
    error.value = '';
    success.value = '';
    loading.value = true;
    try {
        const { data } = await axios.put('/api/admin/settings', form);
        Object.assign(form, data);
        success.value = 'تم حفظ محتوى الموقع، وتظهر التغييرات فوراً في الصفحة الرئيسية.';
        toast.success(success.value);
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="space-y-5">
        <div>
            <h2 class="text-2xl font-black text-primary">محتوى الموقع</h2>
            <p class="text-sm text-slate-500">هذه النصوص هي نفس محتوى قالب ركيزة على الصفحة الرئيسية.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="rounded-full px-4 py-2 text-sm font-extrabold"
                :class="tab === item.id ? 'bg-accent text-white' : 'bg-white text-primary border border-slate-200'"
                @click="tab = item.id"
            >
                {{ item.label }}
            </button>
        </div>

        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>
            <p v-if="success" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ success }}</p>

            <div class="grid gap-4">
                <div v-for="field in groups[tab]" :key="field.key">
                    <label class="mb-2 block text-sm font-extrabold text-primary">{{ field.label }}</label>
                    <textarea
                        v-if="field.type === 'textarea'"
                        v-model="form[field.key]"
                        rows="4"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"
                    ></textarea>
                    <input
                        v-else
                        v-model="form[field.key]"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary"
                    >
                </div>
            </div>

            <button type="submit" class="mt-6 rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">حفظ المحتوى</button>
        </form>
    </section>
</template>
