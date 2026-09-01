<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({ stats: { type: Object, required: true } });

const currencyOrder = ['IQD', 'USD'];
function amounts(obj) {
    if (!obj || Object.keys(obj).length === 0) return [{ cur: 'IQD', val: 0 }];
    return Object.entries(obj)
        .sort(([a], [b]) => currencyOrder.indexOf(a) - currencyOrder.indexOf(b))
        .map(([cur, val]) => ({ cur, val }));
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('dashboard.title') }}</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary)">{{ t('dashboard.subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
            <StatCard :label="t('dashboard.debts')" :value="0">
                <template #icon><Icon name="wallet" /></template>
                <div v-for="a in amounts(stats.owedToMe)" :key="a.cur">
                    <CurrencyAmount :currency="a.cur" :amount="a.val" />
                </div>
            </StatCard>
            <StatCard :href="route('app.debtors.index')" :label="t('dashboard.customersCount')" :value="stats.debtorsCount">
                <template #icon><Icon name="users" /></template>
            </StatCard>
            <StatCard :href="route('app.debts.index', { status: 'overdue' })" :label="t('dashboard.overdue')" :value="stats.overdueCount">
                <template #icon><Icon name="warning" /></template>
            </StatCard>
            <StatCard :href="route('app.debts.index')" :label="t('dashboard.dueToday')" :value="stats.dueTodayCount">
                <template #icon><Icon name="calendar" /></template>
            </StatCard>
            <StatCard :href="route('app.debtors.favorites')" :label="t('dashboard.favorites')" :value="stats.favoritesCount" dark class="sm:col-span-2">
                <template #icon><Icon name="star" /></template>
            </StatCard>
        </div>

        <div class="mt-8 hidden flex-wrap gap-3 md:flex md:justify-end">
            <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">{{ t('dashboard.newDebt') }}</Link>
            <Link :href="route('app.payments.create')" class="rounded-pill border-2 border-brand-600 px-6 py-2.5 text-sm font-bold text-brand-700 transition hover:-translate-y-0.5 hover:bg-brand-50">{{ t('dashboard.payDebt') }}</Link>
        </div>
    </AppLayout>
</template>
