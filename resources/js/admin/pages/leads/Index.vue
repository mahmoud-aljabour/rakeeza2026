<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useApiError } from '../../composables/useApiError';
import { useToast } from '../../composables/useToast';

const { message } = useApiError();
const toast = useToast();
const leads = ref([]);
const filter = ref('');
const error = ref('');

const statuses = [
    { value: '', label: 'الكل' },
    { value: 'pending', label: 'جديد' },
    { value: 'contacted', label: 'تم التواصل' },
    { value: 'converted', label: 'تم التحويل' },
    { value: 'closed', label: 'مغلق' },
];

async function load() {
    const { data } = await axios.get('/api/admin/leads', { params: filter.value ? { status: filter.value } : {} });
    leads.value = data;
}

async function changeStatus(lead, status) {
    error.value = '';
    try {
        const { data } = await axios.patch(`/api/admin/leads/${lead.id}`, { status });
        lead.status = data.status;
        toast.fromResponse(data, 'تم تحديث حالة الطلب.');
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    }
}

async function remove(lead) {
    if (!confirm(`حذف طلب ${lead.name}؟`)) return;
    error.value = '';
    try {
        const { data } = await axios.delete(`/api/admin/leads/${lead.id}`);
        toast.fromResponse(data, 'تم حذف الطلب بنجاح.');
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
                <h2 class="text-2xl font-black text-primary">طلبات العملاء</h2>
                <p class="text-sm text-slate-500">الطلبات القادمة من نموذج الصفحة الرئيسية.</p>
            </div>
            <select v-model="filter" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold" @change="load">
                <option v-for="item in statuses" :key="item.value" :value="item.value">{{ item.label }}</option>
            </select>
        </div>

        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ error }}</p>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-right text-sm">
                <thead class="bg-slate-50 text-primary">
                    <tr>
                        <th class="px-4 py-3 font-extrabold">الاسم</th>
                        <th class="px-4 py-3 font-extrabold">الجوال</th>
                        <th class="px-4 py-3 font-extrabold">الخدمة</th>
                        <th class="px-4 py-3 font-extrabold">التفاصيل</th>
                        <th class="px-4 py-3 font-extrabold">الحالة</th>
                        <th class="px-4 py-3 font-extrabold"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lead in leads" :key="lead.id" class="border-t border-slate-100">
                        <td class="px-4 py-3 font-bold">{{ lead.name }}</td>
                        <td class="px-4 py-3" dir="ltr">{{ lead.phone }}</td>
                        <td class="px-4 py-3">{{ lead.service?.title || 'استفسار عام' }}</td>
                        <td class="max-w-xs px-4 py-3 text-slate-500">{{ lead.message }}</td>
                        <td class="px-4 py-3">
                            <select :value="lead.status" class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold" @change="changeStatus(lead, $event.target.value)">
                                <option v-for="item in statuses.filter((s) => s.value)" :key="item.value" :value="item.value">{{ item.label }}</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" class="text-xs font-bold text-red-600" @click="remove(lead)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="!leads.length">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">لا توجد طلبات بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
