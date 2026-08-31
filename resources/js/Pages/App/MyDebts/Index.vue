<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';

const { t } = useI18n();

const props = defineProps({
    myDebts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    iOwe: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');

function search() {
    router.get(route('app.my-debts.index'), { q: q.value }, { preserveState: true, replace: true });
}

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
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('nav.myDebts') }}</h1>
            <Link :href="route('app.my-debts.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">{{ t('common.add') }}</Link>
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard :label="t('myDebts.owedLabel')" :value="0">
                <template #icon><Icon name="wallet" /></template>
                <div v-for="a in amounts(iOwe)" :key="a.cur">
                    <CurrencyAmount :currency="a.cur" :amount="a.val" />
                </div>
            </StatCard>
        </div>

        <div class="mb-4 flex gap-3">
            <input v-model="q" type="text" :placeholder="t('myDebts.searchPlaceholder')" class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">{{ t('common.search') }}</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">{{ t('myDebts.colCreditor') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colAmount') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colRemaining') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colType') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in myDebts.data" :key="d.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <Link :href="route('app.my-debts.show', d.id)" class="font-semibold hover:underline">{{ d.creditor?.name }}</Link>
                        </td>
                        <td class="p-3"><CurrencyAmount :currency="d.currency" :amount="d.amount" /></td>
                        <td class="p-3"><CurrencyAmount :currency="d.currency" :amount="d.amount - d.paid_amount" /></td>
                        <td class="p-3">{{ d.payment_type === 'installments' ? t('debtors.typeInstallments') : t('debtors.typeLumpSum') }}</td>
                    </tr>
                    <tr v-if="myDebts.data.length === 0">
                        <td colspan="4" class="p-6 text-center" style="color: var(--text-secondary)">{{ t('myDebts.empty') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="myDebts" />
    </AppLayout>
</template>
