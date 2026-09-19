<script setup>
import { useToast } from '../composables/useToast';
import { useLocale } from '../composables/useLocale';

const { toasts, dismiss } = useToast();
const { t } = useLocale();

function icon(type) {
    return type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
}
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[80] flex flex-col items-center gap-2 px-4 sm:items-end sm:pe-6">
        <TransitionGroup name="toast" tag="div" class="flex w-full max-w-sm flex-col gap-2">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-3 rounded-2xl px-4 py-3 text-sm font-extrabold text-white shadow-lg"
                :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
                role="status"
            >
                <i class="fa-solid mt-0.5" :class="icon(toast.type)"></i>
                <p class="flex-1 leading-6">{{ toast.text }}</p>
                <button type="button" class="mt-0.5 text-white/80 hover:text-white" :aria-label="t('close')" @click="dismiss(toast.id)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.28s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
