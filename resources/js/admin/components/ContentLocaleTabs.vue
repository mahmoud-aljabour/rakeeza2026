<script setup>
import { useLocale } from '../composables/useLocale';

const { t } = useLocale();
const active = defineModel({ type: String, default: 'ar' });

const tabs = [
    { id: 'ar', labelKey: 'common.locale_tab_ar' },
    { id: 'en', labelKey: 'common.locale_tab_en' },
];
</script>

<template>
    <div class="space-y-4">
        <div
            class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 p-1.5"
            role="tablist"
            :aria-label="t('common.locale_tablist')"
        >
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                role="tab"
                class="min-h-[44px] flex-1 rounded-xl px-4 py-2.5 text-sm font-extrabold transition sm:flex-none sm:min-w-[140px]"
                :class="active === item.id
                    ? item.id === 'en'
                        ? 'bg-white text-primary shadow-sm ring-1 ring-slate-200'
                        : 'bg-primary text-white shadow-sm'
                    : 'text-slate-600 hover:bg-white/70'"
                :aria-selected="active === item.id"
                @click="active = item.id"
            >
                <span v-if="item.id === 'en'" class="inline-flex items-center gap-2">
                    <span class="rounded-md bg-primary/10 px-1.5 py-0.5 text-[10px] font-black tracking-wide text-primary">EN</span>
                    {{ t(item.labelKey) }}
                </span>
                <span v-else>{{ t(item.labelKey) }}</span>
            </button>
        </div>

        <div
            v-show="active === 'ar'"
            role="tabpanel"
            class="rounded-2xl border border-primary/20 bg-primary/[0.04] p-4 sm:p-5"
        >
            <slot name="ar" />
        </div>

        <div
            v-show="active === 'en'"
            role="tabpanel"
            dir="ltr"
            lang="en"
            class="rounded-2xl border border-dashed border-slate-300 bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-5"
        >
            <slot name="en" />
        </div>
    </div>
</template>
