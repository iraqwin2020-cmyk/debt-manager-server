<script setup>
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { RTL_LOCALES } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import DatePicker from '@/Components/DatePicker.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';

const { t, locale } = useI18n();
const isRtl = computed(() => RTL_LOCALES.includes(locale.value));

const props = defineProps({
    debt: { type: Object, required: true },
});

const showPayForm = ref(false);
const selectedInstallment = ref(null);

const remaining = computed(() => props.debt.amount - props.debt.paid_amount);

const openInstallments = computed(() =>
    (props.debt.installments ?? []).filter((i) => i.paid_amount < i.amount).sort((a, b) => a.seq_number - b.seq_number)
);

const form = useForm({
    debt_id: props.debt.id,
    installment_id: null,
    amount: null,
    paid_at: new Date().toISOString().slice(0, 10),
    note: '',
});

function goBack() {
    window.history.back();
}

function openPayForm() {
    showPayForm.value = true;
    if (props.debt.payment_type === 'installments') {
        selectedInstallment.value = openInstallments.value[0] ?? null;
        form.installment_id = selectedInstallment.value?.id ?? null;
        form.amount = selectedInstallment.value ? selectedInstallment.value.amount - selectedInstallment.value.paid_amount : null;
    } else {
        selectedInstallment.value = null;
        form.installment_id = null;
        form.amount = remaining.value;
    }
}

function selectInstallment(inst) {
    selectedInstallment.value = inst;
    form.installment_id = inst.id;
    form.amount = inst.amount - inst.paid_amount;
}

function submit() {
    form.post(route('app.payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showPayForm.value = false;
            selectedInstallment.value = null;
            form.reset('installment_id', 'amount', 'note');
        },
    });
}
</script>

<template>
    <AppLayout>
        <button type="button" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline lg:mb-2 lg:text-xs" @click="goBack"><Icon name="back" :style="isRtl ? 'transform: scaleX(-1)' : ''" /> {{ t('common.back') }}</button>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 lg:mb-3">
            <h1 class="text-lg font-extrabold sm:text-2xl lg:text-xl">{{ t('debts.debtNumber') }} <bdi class="bdi-ltr">#{{ debt.receipt_number }}</bdi></h1>
            <a :href="route('app.debts.receipt', debt.id)" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-pill border-2 px-4 py-2 text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)"><Icon name="print" /> {{ t('debts.printReceipt') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-3 rounded-card p-6 lg:col-span-1 lg:space-y-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <p class="lg:text-xs">
                    <span class="font-semibold">{{ t('debts.customerLabel') }}</span>
                    <Link :href="route('app.debtors.show', debt.debtor.id)" class="hover:underline">{{ debt.debtor?.name }}</Link>
                    — <PhoneLink :phone="debt.debtor?.phone" />
                </p>
                <p class="lg:text-xs"><span class="font-semibold">{{ t('debtors.colAmount') }}:</span> <CurrencyAmount :currency="debt.currency" :amount="debt.amount" /></p>
                <p class="lg:text-xs"><span class="font-semibold">{{ t('debtors.colRemaining') }}:</span> <CurrencyAmount :currency="debt.currency" :amount="remaining" /></p>
                <p class="lg:text-xs"><span class="font-semibold">{{ t('debtors.colType') }}:</span> {{ debt.payment_type === 'installments' ? t('debtors.typeInstallments') : t('debtors.typeLumpSum') }}</p>
                <p v-if="debt.due_date" class="lg:text-xs"><span class="font-semibold">{{ t('debts.colDueDate') }}:</span> <bdi class="bdi-date" dir="rtl">{{ debt.due_date }}</bdi></p>
                <p v-if="debt.description" class="lg:text-xs"><span class="font-semibold">{{ t('debts.colDescription') }}:</span> {{ debt.description }}</p>

                <div v-if="debt.guarantors?.length" class="pt-2">
                    <p class="mb-1 text-sm font-semibold lg:text-xs">{{ t('debts.guarantorsLabel') }}</p>
                    <ul class="space-y-1 text-sm lg:text-xs">
                        <li v-for="g in debt.guarantors" :key="g.id">{{ g.name }} — <PhoneLink :phone="g.phone" /></li>
                    </ul>
                </div>

                <div class="pt-4 lg:pt-2">
                    <button
                        v-if="remaining > 0 && !showPayForm"
                        type="button"
                        class="w-full rounded-pill bg-brand-600 px-4 py-2 text-center text-sm font-bold text-white lg:px-3 lg:py-1.5 lg:text-xs"
                        @click="openPayForm"
                    >
                        {{ t('common.pay') }}
                    </button>
                    <p v-else-if="remaining <= 0" class="rounded-pill px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="background: var(--status-success-bg); color: var(--status-success-text)">{{ t('payment.fullyPaid') }}</p>
                </div>

                <form v-if="showPayForm" class="space-y-3 border-t pt-4 lg:pt-2" style="border-color: var(--border-subtle)" @submit.prevent="submit">
                    <div v-if="debt.payment_type === 'installments'">
                        <p class="mb-2 text-sm font-semibold lg:text-xs">{{ t('payment.chooseInstallment') }}</p>
                        <button
                            v-for="inst in openInstallments"
                            :key="inst.id"
                            type="button"
                            class="mb-1.5 block w-full rounded-xl border p-2 text-start text-sm transition lg:text-xs"
                            :style="selectedInstallment?.id === inst.id ? 'border-color: var(--color-brand-600); background: var(--status-success-bg)' : 'border-color: var(--border-subtle)'"
                            @click="selectInstallment(inst)"
                        >
                            {{ t('payment.installmentNumber', { num: inst.seq_number }) }} — <CurrencyAmount :currency="debt.currency" :amount="inst.amount - inst.paid_amount" />
                        </button>
                        <p v-if="openInstallments.length === 0" class="text-sm" style="color: var(--text-secondary)">{{ t('payment.noOpenInstallments') }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold lg:text-xs">{{ t('payment.amount') }}</label>
                        <MoneyInput v-model="form.amount" :currency="debt.currency" />
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold lg:text-xs">{{ t('payment.date') }}</label>
                        <DatePicker v-model="form.paid_at" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold lg:text-xs">{{ t('debtors.notesOptional') }}</label>
                        <textarea v-model="form.note" rows="2" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: var(--border-subtle)"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="form.processing" class="flex-1 rounded-pill bg-brand-600 px-4 py-2 text-sm font-bold text-white disabled:opacity-50 lg:px-3 lg:py-1.5 lg:text-xs">{{ t('payment.record') }}</button>
                        <button type="button" class="rounded-pill border-2 px-4 py-2 text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)" @click="showPayForm = false">{{ t('common.cancel') }}</button>
                    </div>
                </form>
            </div>

            <div class="rounded-card p-6 lg:col-span-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-3 font-bold">{{ t('payment.paymentsCount', { count: debt.payments?.length ?? 0 }) }}</h2>
                <div class="overflow-x-auto">
                    <table class="data-table w-full text-sm">
                        <thead>
                            <tr style="color: var(--text-secondary)">
                                <th class="p-2 text-start">{{ t('payment.colReceiptNo') }}</th>
                                <th class="p-2 text-start">{{ t('payment.colDate') }}</th>
                                <th class="p-2 text-start">{{ t('payment.colAmount') }}</th>
                                <th class="p-2 text-start">{{ t('payment.colNotes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in debt.payments" :key="payment.id" class="border-t" style="border-color: var(--border-subtle)">
                                <td class="p-2">
                                    <a :href="route('app.payments.receipt', payment.id)" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline">
                                        <Icon name="print" /> <bdi class="bdi-ltr">#{{ payment.receipt_number }}</bdi>
                                    </a>
                                </td>
                                <td class="p-2"><bdi class="bdi-date" dir="rtl">{{ payment.paid_at }}</bdi></td>
                                <td class="p-2"><CurrencyAmount :currency="debt.currency" :amount="payment.amount" /></td>
                                <td class="p-2">{{ payment.note || '—' }}</td>
                            </tr>
                            <tr v-if="!debt.payments || debt.payments.length === 0">
                                <td colspan="4" class="p-4 text-center" style="color: var(--text-secondary)">{{ t('payment.noPaymentsYet') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
