<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';
import FormattedDate from '@/Components/FormattedDate.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';

const { t } = useI18n();

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
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('nav.debts') }}</h1>
            <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">{{ t('common.add') }}</Link>
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard :label="t('debts.countLabel')" :value="debts.total">
                <template #icon><Icon name="wallet" /></template>
            </StatCard>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="q" type="text" :placeholder="t('debts.searchPlaceholder')" class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <SelectMenu
                v-model="status"
                :options="[
                    { value: '', label: t('debts.allStatuses') },
                    { value: 'open', label: t('debts.statusOpen') },
                    { value: 'paid', label: t('debts.statusPaid') },
                    { value: 'overdue', label: t('debts.statusOverdue') },
                ]"
                @change="search"
            />
            <SelectMenu
                v-model="paymentType"
                :options="[
                    { value: '', label: t('debts.allTypes') },
                    { value: 'lump_sum', label: t('debtors.typeLumpSum') },
                    { value: 'installments', label: t('debtors.typeInstallments') },
                ]"
                @change="search"
            />
            <SelectMenu
                v-model="currency"
                :options="[
                    { value: '', label: t('debts.allCurrencies') },
                    { value: 'IQD', label: t('debts.currencyIQD') },
                    { value: 'USD', label: t('debts.currencyUSD') },
                ]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">{{ t('common.search') }}</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">{{ t('debts.colCustomer') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debtors.colReceiptNo') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debts.colGuarantor') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colAmount') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colRemaining') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colType') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debts.colDescription') }}</th>
                        <th class="p-3 text-start">{{ t('debts.colDueDate') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="debt in debts.data" :key="debt.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3" :class="rowClass(debt)">
                            <Link :href="route('app.debts.show', debt.id)" class="font-semibold hover:underline">{{ debt.debtor?.name }}</Link>
                        </td>
                        <td class="hidden p-3 lg:table-cell"><bdi class="bdi-ltr">{{ debt.receipt_number || '—' }}</bdi></td>
                        <td class="hidden p-3 lg:table-cell">{{ debt.guarantors?.length ? debt.guarantors.map((g) => g.name).join('، ') : '—' }}</td>
                        <td class="p-3"><CurrencyAmount :currency="debt.currency" :amount="debt.amount" /></td>
                        <td class="p-3"><CurrencyAmount :currency="debt.currency" :amount="debt.amount - debt.paid_amount" /></td>
                        <td class="p-3">{{ debt.payment_type === 'installments' ? t('debtors.typeInstallments') : t('debtors.typeLumpSum') }}</td>
                        <td class="hidden max-w-[14rem] truncate p-3 lg:table-cell">{{ debt.description || '—' }}</td>
                        <td class="p-3"><FormattedDate :value="debt.due_date" /></td>
                    </tr>
                    <tr v-if="debts.data.length === 0">
                        <td colspan="8" class="p-6 text-center" style="color: var(--text-secondary)">{{ t('debts.empty') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="debts" />
    </AppLayout>
</template>
