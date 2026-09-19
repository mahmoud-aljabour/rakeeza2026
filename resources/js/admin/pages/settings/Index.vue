<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';

const { message } = useApiError();
const toast = useToast();
const { t } = useLocale();
const tab = ref('contact');
const error = ref('');
const success = ref('');
const loading = ref(false);
const form = reactive({});

const tabs = computed(() => [
    { id: 'contact', label: t('settings.tab_contact') },
    { id: 'hero', label: t('settings.tab_hero') },
    { id: 'more', label: t('settings.tab_more') },
]);

const groups = computed(() => ({
    contact: [
        { key: 'phone', label: t('settings.phone') },
        { key: 'email', label: t('settings.email') },
        { key: 'whatsapp', label: t('settings.whatsapp') },
        { key: 'hours', label: t('settings.hours') },
        { key: 'contact_intro', label: t('settings.contact_intro'), type: 'textarea' },
        { key: 'footer_about', label: t('settings.footer_about'), type: 'textarea' },
    ],
    hero: [
        { key: 'hero_kicker', label: t('settings.hero_kicker') },
        { key: 'hero_title', label: t('settings.hero_title') },
        { key: 'hero_highlight', label: t('settings.hero_highlight') },
        { key: 'hero_text', label: t('settings.hero_text'), type: 'textarea' },
        { key: 'about_text', label: t('settings.about_text'), type: 'textarea' },
        { key: 'about_highlight_1', label: t('settings.about_highlight_1') },
        { key: 'about_highlight_2', label: t('settings.about_highlight_2') },
        { key: 'vision_text', label: t('settings.vision_text'), type: 'textarea' },
    ],
    more: [
        { key: 'craftsman_kicker', label: t('settings.craftsman_kicker') },
        { key: 'craftsman_title', label: t('settings.craftsman_title') },
        { key: 'craftsman_text', label: t('settings.craftsman_text'), type: 'textarea' },
        { key: 'craftsman_feature_1', label: t('settings.craftsman_feature_1') },
        { key: 'craftsman_feature_2', label: t('settings.craftsman_feature_2') },
        { key: 'why_kicker', label: t('settings.why_kicker') },
        { key: 'why_title', label: t('settings.why_title') },
        { key: 'why_lead', label: t('settings.why_lead'), type: 'textarea' },
        { key: 'why_item_1', label: t('settings.why_item_1') },
        { key: 'why_item_2', label: t('settings.why_item_2') },
        { key: 'why_item_3', label: t('settings.why_item_3') },
        { key: 'why_item_4', label: t('settings.why_item_4') },
        { key: 'why_item_5', label: t('settings.why_item_5') },
        { key: 'why_item_6', label: t('settings.why_item_6') },
        { key: 'why_caption_title', label: t('settings.why_caption_title') },
        { key: 'why_caption_text', label: t('settings.why_caption_text') },
    ],
}));

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
        success.value = t('settings.saved');
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
            <h2 class="text-2xl font-black text-primary">{{ t('settings.title') }}</h2>
            <p class="text-sm text-slate-500">{{ t('settings.subtitle') }}</p>
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

            <button type="submit" class="mt-6 rounded-xl bg-accent px-5 py-2.5 font-extrabold text-white hover:bg-accent-hover" :disabled="loading">{{ t('settings.save') }}</button>
        </form>
    </section>
</template>
