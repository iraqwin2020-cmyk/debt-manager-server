<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    myDebts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    iOwe: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');

function search() {
    router.get(route('app.my-debts.index'), { q: q.value }, { preserveState: true, replace: true });
}

function fmt(amount, cur) {
    return `${new Intl.NumberFormat('en-US').format(amount)} ${cur === 'USD' ? '$' : 'د.ع'}`;
}

const currencyOrder = ['IQD', 'USD'];
function amounts(obj) {
    if (!obj || Object.keys(obj).length === 0) return [{ cur: 'IQD', text: '0 د.ع' }];
    return Object.entries(obj)
        .sort(([a], [b]) => currencyOrder.indexOf(a) - currencyOrder.indexOf(b))
        .map(([cur, val]) => ({ cur, text: fmt(val, cur) }));
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">ديوني</h1>
            <Link :href="route('app.my-debts.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">+ دين عليّ جديد</Link>
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard label="الديون عليه" :value="amounts(iOwe).map((a) => a.text).join(' + ')">
                <template #icon><Icon name="wallet" /></template>
                <div v-for="a in amounts(iOwe)" :key="a.cur">
                    <bdi class="bdi-ltr">{{ a.text }}</bdi>
                </div>
            </StatCard>
        </div>

        <div class="mb-4 flex gap-3">
            <input v-model="q" type="text" placeholder="بحث باسم الدائن..." class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">الدائن</th>
                        <th class="p-3 text-start">المبلغ</th>
                        <th class="p-3 text-start">المتبقي</th>
                        <th class="p-3 text-start">النوع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in myDebts.data" :key="d.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3 font-semibold">{{ d.creditor?.name }}</td>
                        <td class="p-3"><bdi class="bdi-ltr">{{ fmt(d.amount, d.currency) }}</bdi></td>
                        <td class="p-3"><bdi class="bdi-ltr">{{ fmt(d.amount - d.paid_amount, d.currency) }}</bdi></td>
                        <td class="p-3">{{ d.payment_type === 'installments' ? 'أقساط' : 'دفعة واحدة' }}</td>
                    </tr>
                    <tr v-if="myDebts.data.length === 0">
                        <td colspan="4" class="p-6 text-center" style="color: var(--text-secondary)">لا يوجد عليك ديون مسجَّلة.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="myDebts.links" />
    </AppLayout>
</template>
