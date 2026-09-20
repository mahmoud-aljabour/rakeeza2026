<script setup>
import { computed } from 'vue';
import { useLocale } from '../composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    page: {
        type: Number,
        required: true,
    },
    lastPage: {
        type: Number,
        default: 1,
    },
    total: {
        type: Number,
        default: 0,
    },
    from: {
        type: Number,
        default: 0,
    },
    to: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['change']);

const showControls = computed(() => props.lastPage > 1);
const canGoPrevious = computed(() => props.page > 1);
const canGoNext = computed(() => props.page < props.lastPage);

const summary = computed(() => t('common.showing', {
    from: props.from,
    to: props.to,
    total: props.total,
}));
</script>

<template>
    <div
        v-if="total > 0"
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
    >
        <p class="inline-flex items-center gap-2 text-sm font-bold text-slate-500">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-primary/8 text-primary">
                <i class="fa-solid fa-list-ol text-[12px]" aria-hidden="true"></i>
            </span>
            <span>
                {{ t('common.showing_prefix') }}
                <span class="mx-1 font-black text-primary">{{ from }}</span>
                <span class="text-slate-400">{{ t('common.showing_separator') }}</span>
                <span class="mx-1 font-black text-primary">{{ to }}</span>
                <span class="text-slate-400">{{ t('common.showing_of') }}</span>
                <span class="mx-1 font-black text-primary">{{ total }}</span>
            </span>
            <span class="sr-only">{{ summary }}</span>
        </p>

        <div v-if="showControls" class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="inline-flex h-10 min-w-[7.5rem] items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-primary transition hover:border-primary/30 hover:bg-white disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="!canGoPrevious"
                :aria-label="t('common.previous')"
                @click="emit('change', page - 1)"
            >
                <i class="fa-solid fa-chevron-left text-[11px] rtl:rotate-180" aria-hidden="true"></i>
                {{ t('common.previous') }}
            </button>
            <span class="inline-flex h-10 min-w-[4.5rem] items-center justify-center rounded-xl bg-primary px-3 text-sm font-black text-white">
                {{ page }} / {{ lastPage }}
            </span>
            <button
                type="button"
                class="inline-flex h-10 min-w-[7.5rem] items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-extrabold text-primary transition hover:border-primary/30 hover:bg-white disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="!canGoNext"
                :aria-label="t('common.next')"
                @click="emit('change', page + 1)"
            >
                {{ t('common.next') }}
                <i class="fa-solid fa-chevron-right text-[11px] rtl:rotate-180" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>
