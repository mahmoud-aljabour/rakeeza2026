<script setup>
import { ref } from 'vue';
import { useLocale } from '../composables/useLocale';

const { t } = useLocale();

const props = defineProps({
    preview: {
        type: String,
        default: '',
    },
    multiple: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select']);
const dragging = ref(false);

function applyFile(file) {
    if (! file || ! file.type.startsWith('image/')) {
        return;
    }

    emit('select', file);
}

function applyFiles(fileList) {
    const files = [...(fileList || [])];

    if (props.multiple) {
        files.forEach(applyFile);
        return;
    }

    applyFile(files[0]);
}

function onChange(event) {
    applyFiles(event.target.files);
    event.target.value = '';
}

function onDrop(event) {
    dragging.value = false;
    applyFiles(event.dataTransfer.files);
}
</script>

<template>
    <label
        class="relative mb-4 flex min-h-48 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed transition"
        :class="dragging
            ? 'border-accent bg-orange-50'
            : 'border-slate-300 bg-slate-50 hover:border-primary hover:bg-[#0a3356]/5'"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop"
    >
        <input type="file" accept="image/*" class="sr-only" :multiple="multiple" @change="onChange">

        <img
            v-if="preview && !multiple"
            :src="preview"
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
        >
        <span v-if="preview && !multiple" class="absolute inset-0 bg-[#0a3356]/50"></span>

        <span
            class="relative z-10 flex flex-col items-center gap-3 px-4 text-center"
            :class="preview && !multiple ? 'text-white' : 'text-primary'"
        >
            <span
                class="flex size-16 items-center justify-center rounded-2xl text-3xl shadow-sm"
                :class="preview && !multiple ? 'bg-white/95 text-accent' : 'bg-white text-accent'"
            >
                <i class="fa-regular fa-image"></i>
            </span>
            <span>
                <span class="block text-sm font-extrabold">
                    {{ multiple
                        ? t('common.drop_add')
                        : (preview ? t('common.drop_change') : t('common.drop_upload')) }}
                </span>
                <span class="mt-1 block text-xs font-bold opacity-75">
                    {{ multiple
                        ? t('common.drop_multi_hint')
                        : t('common.drop_hint') }}
                </span>
            </span>
        </span>
    </label>
</template>
