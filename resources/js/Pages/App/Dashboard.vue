<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ stats: { type: Object, required: true } });

function fmt(obj) {
    if (!obj || Object.keys(obj).length === 0) return '0';
    return Object.entries(obj)
        .map(([cur, val]) => `${new Intl.NumberFormat('en-US').format(val)} ${cur === 'USD' ? '$' : 'د.ع'}`)
        .join(' + ');
}

const currencyOrder = ['IQD', 'USD'];
function amounts(obj) {
    if (!obj || Object.keys(obj).length === 0) return [{ cur: 'IQD', text: '0 د.ع' }];
    return Object.entries(obj)
        .sort(([a], [b]) => currencyOrder.indexOf(a) - currencyOrder.indexOf(b))
        .map(([cur, val]) => ({ cur, text: `${new Intl.NumberFormat('en-US').format(val)} ${cur === 'USD' ? '$' : 'د.ع'}` }));
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-lg font-extrabold sm:text-2xl">الصفحة الرئيسية</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary)">نظرة سريعة على حسابك</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <StatCard label="الديون" :value="fmt(stats.owedToMe)">
                <template #icon><Icon name="wallet" /></template>
                <div v-for="a in amounts(stats.owedToMe)" :key="a.cur">
                    <bdi class="bdi-ltr">{{ a.text }}</bdi>
                </div>
            </StatCard>
            <StatCard :href="route('app.debtors.index')" label="عدد العملاء" :value="stats.debtorsCount">
                <template #icon><Icon name="users" /></template>
            </StatCard>
            <StatCard :href="route('app.debts.index', { status: 'overdue' })" label="المتأخرات" :value="stats.overdueCount">
                <template #icon><Icon name="warning" /></template>
            </StatCard>
            <StatCard :href="route('app.debts.index')" label="استحقاقات اليوم" :value="stats.dueTodayCount">
                <template #icon><Icon name="calendar" /></template>
            </StatCard>
        </div>

        <div class="mt-8 hidden flex-wrap gap-3 md:flex">
            <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">+ دين جديد</Link>
            <Link :href="route('app.payments.create')" class="rounded-pill border-2 border-brand-600 px-6 py-2.5 text-sm font-bold text-brand-700 transition hover:-translate-y-0.5 hover:bg-brand-50">تسديد دفعة</Link>
        </div>
    </AppLayout>
</template>
