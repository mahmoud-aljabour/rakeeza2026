<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';

const stats = ref(null);

onMounted(async () => {
    const { data } = await axios.get('/api/admin/stats');
    stats.value = data;
});

const cards = [
    { key: 'active_services', label: 'خدمات ظاهرة', to: '/services', icon: 'fa-screwdriver-wrench' },
    { key: 'projects', label: 'الأعمال', to: '/projects', icon: 'fa-images' },
    { key: 'pending', label: 'طلبات جديدة', to: '/leads', icon: 'fa-inbox' },
    { key: 'craftsmen', label: 'طلبات حرفيين', to: '/craftsmen', icon: 'fa-user-gear' },
];
</script>

<template>
    <section class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-l from-primary-dark via-primary to-primary-light p-6 text-white shadow-lg">
            <p class="text-sm font-extrabold text-accent">ركيزة</p>
            <h2 class="mt-1 text-2xl font-black">تحكم بمحتوى الصفحة الرئيسية</h2>
            <p class="mt-2 max-w-2xl text-slate-200">عدّل الخدمات، الأعمال السابقة، نصوص الموقع، وطلبات العملاء من هنا بنفس هوية القالب.</p>
        </div>

        <div v-if="stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <RouterLink
                v-for="card in cards"
                :key="card.key"
                :to="card.to"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-accent"
            >
                <i class="fa-solid mb-3 text-accent" :class="card.icon"></i>
                <p class="text-3xl font-black text-primary">
                    {{ card.key === 'pending' ? stats.leads.pending : card.key === 'craftsmen' ? stats.craftsmen.pending : stats[card.key] }}
                </p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ card.label }}</p>
            </RouterLink>
        </div>
    </section>
</template>
