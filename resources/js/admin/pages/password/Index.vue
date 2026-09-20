<script setup>
import { computed, reactive, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';
import { useLocale } from '../../composables/useLocale';

const { message } = useApiError();
const toast = useToast();
const { t } = useLocale();

const error = ref('');
const success = ref('');
const loading = ref(false);
const showCurrent = ref(false);
const showPassword = ref(false);
const showConfirmation = ref(false);

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const strength = computed(() => {
    const value = form.password || '';
    let score = 0;

    if (value.length >= 8) {
        score += 1;
    }
    if (value.length >= 12) {
        score += 1;
    }
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) {
        score += 1;
    }
    if (/\d/.test(value)) {
        score += 1;
    }
    if (/[^A-Za-z0-9]/.test(value)) {
        score += 1;
    }

    if (!value) {
        return { score: 0, key: 'empty', width: '0%', bar: 'bg-slate-200' };
    }

    if (score <= 2) {
        return { score, key: 'weak', width: '33%', bar: 'bg-red-500' };
    }

    if (score <= 3) {
        return { score, key: 'fair', width: '55%', bar: 'bg-amber-500' };
    }

    if (score === 4) {
        return { score, key: 'good', width: '78%', bar: 'bg-sky-500' };
    }

    return { score, key: 'strong', width: '100%', bar: 'bg-emerald-500' };
});

const checks = computed(() => [
    { key: 'length', ok: (form.password || '').length >= 8 },
    { key: 'mixed', ok: /[a-z]/.test(form.password || '') && /[A-Z]/.test(form.password || '') },
    { key: 'number', ok: /\d/.test(form.password || '') },
    { key: 'symbol', ok: /[^A-Za-z0-9]/.test(form.password || '') },
    { key: 'match', ok: form.password !== '' && form.password === form.password_confirmation },
]);

async function submit() {
    error.value = '';
    success.value = '';
    loading.value = true;

    try {
        const { data } = await axios.put('/api/admin/password', {
            current_password: form.current_password,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        success.value = data.message || t('password.saved');
        toast.success(success.value);
        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';
    } catch (e) {
        error.value = message(e, t('errors.generic'));
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="mx-auto max-w-xl space-y-5">
        <div>
            <h2 class="text-2xl font-black text-primary">{{ t('password.title') }}</h2>
            <p class="text-sm text-slate-500">{{ t('password.subtitle') }}</p>
        </div>

        <form class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
            <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>
            <p v-if="success" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ success }}</p>

            <div class="grid gap-4">
                <div>
                    <label class="mb-2 block text-sm font-extrabold text-primary" for="current-password">{{ t('password.current') }}</label>
                    <div class="relative">
                        <input
                            id="current-password"
                            v-model="form.current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pe-12 outline-none focus:border-primary"
                            dir="ltr"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 end-0 px-3 text-slate-500"
                            :aria-label="showCurrent ? t('password.hide') : t('password.show')"
                            @click="showCurrent = !showCurrent"
                        >
                            <i class="fa-solid" :class="showCurrent ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-extrabold text-primary" for="new-password">{{ t('password.new') }}</label>
                    <div class="relative">
                        <input
                            id="new-password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pe-12 outline-none focus:border-primary"
                            dir="ltr"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 end-0 px-3 text-slate-500"
                            :aria-label="showPassword ? t('password.hide') : t('password.show')"
                            @click="showPassword = !showPassword"
                        >
                            <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>

                    <div class="mt-3 space-y-2" aria-live="polite">
                        <div class="flex items-center justify-between gap-3 text-xs font-extrabold">
                            <span class="text-slate-500">{{ t('password.strength_label') }}</span>
                            <span
                                :class="{
                                    'text-slate-400': strength.key === 'empty',
                                    'text-red-600': strength.key === 'weak',
                                    'text-amber-600': strength.key === 'fair',
                                    'text-sky-600': strength.key === 'good',
                                    'text-emerald-600': strength.key === 'strong',
                                }"
                            >
                                {{ strength.key === 'empty' ? t('password.strength_empty') : t(`password.strength_${strength.key}`) }}
                            </span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :class="strength.bar"
                                :style="{ width: strength.width }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-extrabold text-primary" for="confirm-password">{{ t('password.confirm') }}</label>
                    <div class="relative">
                        <input
                            id="confirm-password"
                            v-model="form.password_confirmation"
                            :type="showConfirmation ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pe-12 outline-none focus:border-primary"
                            dir="ltr"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 end-0 px-3 text-slate-500"
                            :aria-label="showConfirmation ? t('password.hide') : t('password.show')"
                            @click="showConfirmation = !showConfirmation"
                        >
                            <i class="fa-solid" :class="showConfirmation ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <ul class="grid gap-2 rounded-xl bg-slate-50 p-4 text-sm">
                    <li
                        v-for="check in checks"
                        :key="check.key"
                        class="flex items-center gap-2 font-bold"
                        :class="check.ok ? 'text-emerald-700' : 'text-slate-500'"
                    >
                        <i class="fa-solid text-xs" :class="check.ok ? 'fa-circle-check' : 'fa-circle'"></i>
                        {{ t(`password.check_${check.key}`) }}
                    </li>
                </ul>
            </div>

            <button
                type="submit"
                class="mt-5 w-full rounded-xl bg-accent px-4 py-3 font-extrabold text-white hover:bg-accent-hover disabled:opacity-60"
                :disabled="loading"
            >
                {{ loading ? t('password.saving') : t('password.submit') }}
            </button>
        </form>
    </section>
</template>
