<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectMenu from '@/Components/SelectMenu.vue';

const props = defineProps({
    debts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? '');
const currency = ref(props.filters.currency ?? '');
const paymentType = ref(props.filters.payment_type ?? '');

function search() {
    router.get(
        route('app.debts.index'),
        { q: q.value, status: status.value, currency: currency.value, payment_type: paymentType.value },
        { preserveState: true, replace: true }
    );
}

function fmt(amount, cur) {
    return `${new Intl.NumberFormat('en-US').format(amount)} ${cur === 'USD' ? '$' : 'د.ع'}`;
}

function rowClass(debt) {
    if (debt.paid_amount >= debt.amount) return '';
    if (!debt.due_date) return '';
    const due = new Date(debt.due_date);
    if (due < new Date()) return 'text-yellow-600 font-bold';
    return '';
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">الديون</h1>
            <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">+ دين جديد</Link>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="q" type="text" placeholder="بحث برقم الوصل أو اسم العميل..." class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <SelectMenu
                v-model="status"
                :options="[
                    { value: '', label: 'كل الحالات' },
                    { value: 'open', label: 'مفتوح' },
                    { value: 'paid', label: 'مسدَّد' },
                    { value: 'overdue', label: 'متأخر' },
                ]"
                @change="search"
            />
            <SelectMenu
                v-model="paymentType"
                :options="[
                    { value: '', label: 'كل الأنواع' },
                    { value: 'lump_sum', label: 'دفعة واحدة' },
                    { value: 'installments', label: 'أقساط' },
                ]"
                @change="search"
            />
            <SelectMenu
                v-model="currency"
                :options="[
                    { value: '', label: 'كل العملات' },
                    { value: 'IQD', label: 'دينار' },
                    { value: 'USD', label: 'دولار' },
                ]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">العميل</th>
                        <th class="p-3 text-start">المبلغ</th>
                        <th class="p-3 text-start">المتبقي</th>
                        <th class="p-3 text-start">النوع</th>
                        <th class="p-3 text-start">الاستحقاق</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="debt in debts.data" :key="debt.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3" :class="rowClass(debt)">
                            <Link :href="route('app.debtors.show', debt.debtor.id)" class="font-semibold hover:underline">{{ debt.debtor?.name }}</Link>
                        </td>
                        <td class="p-3"><bdi class="bdi-ltr">{{ fmt(debt.amount, debt.currency) }}</bdi></td>
                        <td class="p-3"><bdi class="bdi-ltr">{{ fmt(debt.amount - debt.paid_amount, debt.currency) }}</bdi></td>
                        <td class="p-3">{{ debt.payment_type === 'installments' ? 'أقساط' : 'دفعة واحدة' }}</td>
                        <td class="p-3"><bdi class="bdi-date" dir="rtl">{{ debt.due_date ?? '—' }}</bdi></td>
                    </tr>
                    <tr v-if="debts.data.length === 0">
                        <td colspan="5" class="p-6 text-center" style="color: var(--text-secondary)">لا توجد ديون بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="debts.links" />
    </AppLayout>
</template>
