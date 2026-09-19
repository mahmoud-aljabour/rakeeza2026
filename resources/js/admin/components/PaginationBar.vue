<script setup>
import { useLocale } from '../composables/useLocale';

const { t } = useLocale();

defineProps({
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

function pages(current, last) {
    const items = [];
    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    for (let value = start; value <= end; value += 1) {
        items.push(value);
    }

    return items;
}
</script>

<template>
    <div v-if="total > 0" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-bold text-slate-500">
            {{ t('common.showing', { from, to, total }) }}
        </p>
        <div v-if="lastPage > 1" class="flex flex-wrap items-center gap-1">
            <button
                type="button"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-primary disabled:opacity-40"
                :disabled="page <= 1"
                @click="emit('change', page - 1)"
            >
                {{ t('common.previous') }}
            </button>
            <button
                v-for="item in pages(page, lastPage)"
                :key="item"
                type="button"
                class="min-w-9 rounded-xl px-3 py-2 text-xs font-extrabold"
                :class="item === page ? 'bg-primary text-white' : 'border border-slate-200 bg-white text-primary'"
                @click="emit('change', item)"
            >
                {{ item }}
            </button>
            <button
                type="button"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-primary disabled:opacity-40"
                :disabled="page >= lastPage"
                @click="emit('change', page + 1)"
            >
                {{ t('common.next') }}
            </button>
        </div>
    </div>
</template>
