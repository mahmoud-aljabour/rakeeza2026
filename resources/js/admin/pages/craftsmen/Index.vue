<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';

const { message } = useApiError();
const toast = useToast();
const craftsmen = ref([]);
const filter = ref('');
const error = ref('');
const exporting = ref(false);

const statuses = [
    { value: '', label: 'الكل' },
    { value: 'pending', label: 'جديد' },
    { value: 'reviewing', label: 'قيد المراجعة' },
    { value: 'accepted', label: 'مقبول' },
    { value: 'rejected', label: 'مرفوض' },
];

async function load() {
    const { data } = await axios.get('/api/admin/craftsmen', {
        params: filter.value ? { status: filter.value } : {},
    });
    craftsmen.value = data;
}

async function changeStatus(item, status) {
    error.value = '';
    try {
        const { data } = await axios.patch(`/api/admin/craftsmen/${item.id}`, { status });
        item.status = data.status;
        toast.fromResponse(data, 'تم تحديث حالة طلب الحرفي.');
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

async function downloadWord(url, fallbackName, params = {}) {
    const response = await axios.get(url, {
        params,
        responseType: 'blob',
    });

    if (response.data.type === 'application/json') {
        const payload = JSON.parse(await response.data.text());
        throw new Error(payload.message || 'تعذر تصدير الملف.');
    }

    const disposition = response.headers['content-disposition'] || '';
    const encoded = disposition.match(/filename\*=UTF-8''([^;]+)/i);
    const plain = disposition.match(/filename="?([^"]+)"?/i);
    const name = decodeURIComponent(encoded?.[1] || plain?.[1] || fallbackName);
    const objectUrl = URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = objectUrl;
    link.download = name;
    link.click();
    URL.revokeObjectURL(objectUrl);
}

async function exportList() {
    error.value = '';
    exporting.value = true;
    try {
        await downloadWord(
            '/api/admin/craftsmen/export',
            'طلبات-الحرفيين.doc',
            filter.value ? { status: filter.value } : {},
        );
        toast.success('تم تصدير طلبات الحرفيين.');
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        exporting.value = false;
    }
}

async function exportOne(item) {
    error.value = '';
    try {
        await downloadWord(`/api/admin/craftsmen/${item.id}/export`, `طلب-حرفي-${item.name}.doc`);
        toast.success(`تم تصدير طلب ${item.name}.`);
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

async function remove(item) {
    if (!confirm(`حذف طلب ${item.name}؟`)) return;
    error.value = '';
    try {
        const { data } = await axios.delete(`/api/admin/craftsmen/${item.id}`);
        toast.fromResponse(data, 'تم حذف طلب الحرفي بنجاح.');
        await load();
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

onMounted(load);
</script>

<template>
    <section class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-black text-primary">طلبات الحرفيين</h2>
                <p class="text-sm text-slate-500">الطلبات القادمة من صفحة تسجيل الحرفيين.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="h-11 rounded-xl bg-primary px-4 text-sm font-extrabold text-white hover:bg-primary-light disabled:opacity-60"
                    :disabled="exporting || !craftsmen.length"
                    @click="exportList"
                >
                    {{ exporting ? 'جاري التصدير...' : 'تصدير كملف وورد' }}
                </button>
                <label class="relative block">
                    <span class="sr-only">تصفية الحالة</span>
                    <select
                        v-model="filter"
                        class="h-11 min-w-[13.5rem] appearance-none rounded-xl border border-slate-200 bg-white pe-10 ps-4 text-sm font-extrabold text-primary shadow-sm outline-none transition hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/15"
                        @change="load"
                    >
                        <option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 end-0 flex w-10 items-center justify-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </label>
            </div>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-right text-sm">
                <thead class="bg-slate-50 text-primary">
                    <tr>
                        <th class="px-4 py-3 font-extrabold">الاسم</th>
                        <th class="px-4 py-3 font-extrabold">الجوال</th>
                        <th class="px-4 py-3 font-extrabold">المنطقة</th>
                        <th class="px-4 py-3 font-extrabold">التخصص</th>
                        <th class="px-4 py-3 font-extrabold">الخبرة</th>
                        <th class="px-4 py-3 font-extrabold">معدات</th>
                        <th class="px-4 py-3 font-extrabold">نبذة</th>
                        <th class="px-4 py-3 font-extrabold">الحالة</th>
                        <th class="px-4 py-3 font-extrabold"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in craftsmen" :key="item.id" class="border-t border-slate-100">
                        <td class="px-4 py-3 font-bold">{{ item.name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ item.phone }}</td>
                        <td class="px-4 py-3">{{ item.city }}</td>
                        <td class="px-4 py-3">{{ item.specialty }}</td>
                        <td class="px-4 py-3">{{ item.experience_years }} سنة</td>
                        <td class="px-4 py-3">{{ item.has_tools ? 'نعم' : 'لا' }}</td>
                        <td class="max-w-xs px-4 py-3 text-slate-500">{{ item.bio }}</td>
                        <td class="px-4 py-3">
                            <select :value="item.status" class="min-w-[8.5rem] rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold" @change="changeStatus(item, $event.target.value)">
                                <option v-for="option in statuses.filter((s) => s.value)" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="text-xs font-bold text-primary" @click="exportOne(item)">وورد</button>
                                <button type="button" class="text-xs font-bold text-red-600" @click="remove(item)">حذف</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!craftsmen.length">
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400">لا توجد طلبات حرفيين بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
