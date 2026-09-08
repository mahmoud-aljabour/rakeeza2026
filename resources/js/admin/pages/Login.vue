<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useApiError } from '../composables/useApiError';

const router = useRouter();
const { login } = useAuth();
const { message } = useApiError();

const form = reactive({
    email: '',
    password: '',
});
const error = ref('');
const loading = ref(false);

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        await login(form.email, form.password);
        await router.push({ name: 'dashboard' });
    } catch (e) {
        error.value = message(e, 'بيانات الدخول غير صحيحة.');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-primary-dark text-white">
        <div class="absolute inset-0 bg-[url('/images/hero-bg-3.jpg')] bg-cover bg-center opacity-30"></div>
        <div class="absolute inset-0 bg-gradient-to-l from-[#040c1cc7] via-[#040c1c6b] to-[#040c1c38]"></div>

        <div class="relative mx-auto flex min-h-screen max-w-md flex-col justify-center px-5 py-12">
            <div class="mb-8 text-center">
                <img src="/images/logo.png" alt="ركيزة" class="mx-auto mb-4 h-16 w-auto">
                <p class="text-sm font-extrabold text-accent">لوحة تحكم ركيزة</p>
                <h1 class="mt-2 text-3xl font-black">تسجيل الدخول</h1>
            </div>

            <form class="rounded-3xl border border-white/15 bg-white/95 p-6 text-slate-800 shadow-xl" @submit.prevent="submit">
                <p v-if="error" class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

                <label class="mb-2 block text-sm font-extrabold text-primary">البريد الإلكتروني</label>
                <input v-model="form.email" type="email" required class="mb-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary" dir="ltr">

                <label class="mb-2 block text-sm font-extrabold text-primary">كلمة المرور</label>
                <input v-model="form.password" type="password" required class="mb-6 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-primary">

                <button type="submit" class="w-full rounded-xl bg-accent px-4 py-3 font-extrabold text-white hover:bg-accent-hover disabled:opacity-60" :disabled="loading">
                    {{ loading ? 'جاري الدخول...' : 'دخول' }}
                </button>
            </form>
        </div>
    </div>
</template>
