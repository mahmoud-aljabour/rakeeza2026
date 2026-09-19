<script setup>
import { useLocale } from '../composables/useLocale';

defineProps({
    tone: {
        type: String,
        default: 'light',
    },
});

const { locale, t, setLocale } = useLocale();

const options = [
    { value: 'ar', code: 'lang.code_ar', name: 'lang.ar' },
    { value: 'en', code: 'lang.code_en', name: 'lang.en' },
];
</script>

<template>
    <div
        class="inline-flex items-center rounded-full p-1"
        :class="tone === 'dark'
            ? 'border border-white/20 bg-white/10'
            : 'border border-slate-200 bg-slate-100/80'"
        role="group"
        :aria-label="t('lang.group')"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            class="inline-flex min-h-9 min-w-11 items-center justify-center gap-1.5 rounded-full px-3 text-[11px] font-extrabold tracking-[0.08em] transition"
            :class="locale === option.value
                ? (tone === 'dark' ? 'bg-accent text-white shadow-sm' : 'bg-primary text-white shadow-sm')
                : (tone === 'dark' ? 'text-white/75 hover:text-white' : 'text-slate-500 hover:text-primary')"
            :aria-pressed="locale === option.value"
            :aria-label="t(option.name)"
            @click="setLocale(option.value)"
        >
            <i v-if="locale === option.value" class="fa-solid fa-language text-[12px]" aria-hidden="true"></i>
            <span>{{ t(option.code) }}</span>
        </button>
    </div>
</template>
