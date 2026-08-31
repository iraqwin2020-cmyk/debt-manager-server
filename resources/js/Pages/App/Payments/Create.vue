<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import DatePicker from '@/Components/DatePicker.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';

const { t } = useI18n();

const query = ref('');
const results = ref([]);
const selectedDebtor = ref(null);
const debts = ref([]);
const selectedDebt = ref(null);
const selectedInstallment = ref(null);
let timer = null;

const form = useForm({
    debt_id: null,
    installment_id: null,
    amount: null,
    paid_at: new Date().toISOString().slice(0, 10),
    note: '',
});

watch(query, (value) => {
    clearTimeout(timer);
    if (!value) { results.value = []; return; }
    timer = setTimeout(async () => {
        const url = route('app.search.people', { type: 'debtor', q: value });
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        results.value = await res.json();
    }, 300);
});

async function selectDebtor(debtor) {
    selectedDebtor.value = debtor;
    query.value = debtor.name;
    results.value = [];
    const res = await fetch(route('app.debtors.debts', debtor.id), { headers: { Accept: 'application/json' } });
    debts.value = await res.json();
}

function selectDebt(debt) {
    selectedDebt.value = debt;
    selectedInstallment.value = null;
    form.debt_id = debt.id;
    form.installment_id = null;
}

function selectInstallment(installment) {
    selectedInstallment.value = installment;
    form.installment_id = installment.id;
}

function submit() {
    form.post(route('app.payments.store'));
}

</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ t('paymentsPage.title') }}</h1>

            <div class="space-y-4 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <div v-if="!selectedDebtor">
                    <label class="mb-1 block text-sm font-semibold">{{ t('paymentsPage.searchCustomer') }}</label>
                    <input v-model="query" type="text" :placeholder="t('paymentsPage.searchPlaceholder')" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                    <div v-if="results.length" class="mt-2 overflow-hidden rounded-xl border" style="border-color: var(--border-subtle)">
                        <button v-for="d in results" :key="d.id" type="button" class="block w-full px-4 py-2.5 text-start text-sm text-[var(--text-primary)] transition hover:bg-brand-600 hover:text-white" @click="selectDebtor(d)">
                            {{ d.name }} — <bdi class="bdi-ltr">{{ d.phone }}</bdi>
                        </button>
                    </div>
                </div>

                <template v-else>
                    <div class="flex items-center justify-between rounded-pill px-4 py-2" style="background: var(--status-success-bg); color: var(--status-success-text)">
                        <span class="font-semibold">{{ selectedDebtor.name }}</span>
                        <button type="button" class="text-xs underline" @click="selectedDebtor = null; debts = []; selectedDebt = null">{{ t('common.change') }}</button>
                    </div>

                    <div v-if="!selectedDebt">
                        <p class="mb-2 text-sm font-semibold">{{ t('paymentsPage.chooseDebt') }}</p>
                        <button
                            v-for="debt in debts"
                            :key="debt.id"
                            type="button"
                            class="mb-2 block w-full rounded-xl border p-3 text-start text-sm text-[var(--text-primary)] transition hover:bg-brand-600 hover:text-white"
                            style="border-color: var(--border-subtle)"
                            @click="selectDebt(debt)"
                        >
                            {{ t('debts.debtNumber') }} <bdi class="bdi-ltr">#{{ debt.receipt_number }}</bdi> — {{ t('paymentsPage.remainingLabel') }}
                            <CurrencyAmount :currency="debt.currency" :amount="debt.amount - debt.paid_amount" />
                        </button>
                        <p v-if="debts.length === 0" style="color: var(--text-secondary)">{{ t('paymentsPage.noOpenDebts') }}</p>
                    </div>

                    <template v-else>
                        <div v-if="selectedDebt.payment_type === 'installments' && !selectedInstallment">
                            <p class="mb-2 text-sm font-semibold">{{ t('payment.chooseInstallment') }}</p>
                            <button
                                v-for="inst in selectedDebt.installments"
                                :key="inst.id"
                                type="button"
                                class="mb-2 block w-full rounded-xl border p-3 text-start text-sm text-[var(--text-primary)] transition hover:bg-brand-600 hover:text-white"
                                style="border-color: var(--border-subtle)"
                                @click="selectInstallment(inst)"
                            >
                                {{ t('payment.installmentNumber', { num: inst.seq_number }) }} — <CurrencyAmount :currency="selectedDebt.currency" :amount="inst.amount - inst.paid_amount" />
                            </button>
                        </div>

                        <form v-else class="space-y-4" @submit.prevent="submit">
                            <div>
                                <label class="mb-1 block text-sm font-semibold">{{ t('payment.amount') }}</label>
                                <MoneyInput v-model="form.amount" :currency="selectedDebt.currency" />
                                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold">{{ t('payment.date') }}</label>
                                <DatePicker v-model="form.paid_at" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold">{{ t('debtors.notesOptional') }}</label>
                                <textarea v-model="form.note" rows="2" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                            </div>
                            <button type="submit" :disabled="form.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white disabled:opacity-50">{{ t('payment.record') }}</button>
                        </form>
                    </template>
                </template>

                <Link :href="route('app.dashboard')" class="block text-center text-sm font-semibold text-brand-700 hover:underline">{{ t('paymentsPage.backToHome') }}</Link>
            </div>
        </div>
    </AppLayout>
</template>
