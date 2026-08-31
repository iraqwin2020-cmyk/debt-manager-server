<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonCombobox from '@/Components/PersonCombobox.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import DatePicker from '@/Components/DatePicker.vue';
import SelectMenu from '@/Components/SelectMenu.vue';

const { t } = useI18n();

const form = useForm({
    creditor: { id: null, name: '', phone: '', address: '', note: '' },
    amount: null,
    currency: 'IQD',
    description: '',
    payment_type: 'lump_sum',
    due_date: '',
    installment_method: 'count',
    installment_count: null,
    installment_amount: null,
    interval_days: 30,
    first_due_date: '',
});

function submit() {
    form.post(route('app.my-debts.store'));
}

const unitLabel = computed(() => (form.currency === 'USD' ? '50' : '1000'));
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ t('myDebts.addNew') }}</h1>

            <form class="space-y-6 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="submit">
                <PersonCombobox v-model="form.creditor" type="creditor" :label="t('myDebts.creditorLabel')" />

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debts.currency') }}</label>
                    <SelectMenu
                        v-model="form.currency"
                        full
                        :options="[
                            { value: 'IQD', label: t('debts.iqd') },
                            { value: 'USD', label: t('debts.usd') },
                        ]"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debts.amountMultiplier', { unit: unitLabel }) }}</label>
                    <MoneyInput v-model="form.amount" :currency="form.currency" />
                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debts.descriptionOptional') }}</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debts.paymentType') }}</label>
                    <SelectMenu
                        v-model="form.payment_type"
                        full
                        :options="[
                            { value: 'lump_sum', label: t('debtors.typeLumpSum') },
                            { value: 'installments', label: t('debtors.typeInstallments') },
                        ]"
                    />
                </div>

                <div v-if="form.payment_type === 'lump_sum'">
                    <label class="mb-1 block text-sm font-semibold">{{ t('debts.dueDate') }}</label>
                    <DatePicker v-model="form.due_date" />
                    <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                </div>

                <div v-else class="space-y-4 rounded-xl border p-4" style="border-color: var(--border-subtle)">
                    <p class="text-sm font-bold">{{ t('debts.installmentCalculator') }}</p>
                    <div class="flex gap-2">
                        <button type="button" class="rounded-pill px-4 py-1.5 text-xs font-bold" :class="form.installment_method === 'count' ? 'bg-brand-600 text-white' : 'border'" @click="form.installment_method = 'count'">{{ t('debts.byCount') }}</button>
                        <button type="button" class="rounded-pill px-4 py-1.5 text-xs font-bold" :class="form.installment_method === 'amount' ? 'bg-brand-600 text-white' : 'border'" @click="form.installment_method = 'amount'">{{ t('debts.byAmount') }}</button>
                    </div>

                    <div v-if="form.installment_method === 'count'">
                        <label class="mb-1 block text-sm font-semibold">{{ t('debts.installmentCount') }}</label>
                        <input
                            :value="form.installment_count"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="form.installment_count = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                    </div>
                    <div v-else>
                        <label class="mb-1 block text-sm font-semibold">{{ t('debts.installmentAmount') }}</label>
                        <MoneyInput v-model="form.installment_amount" :currency="form.currency" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('debts.intervalDays') }}</label>
                        <input
                            :value="form.interval_days"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="form.interval_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('debts.firstInstallmentDate') }}</label>
                        <DatePicker v-model="form.first_due_date" />
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white disabled:opacity-50">{{ t('common.save') }}</button>
                    <Link :href="route('app.my-debts.index')" class="rounded-pill border-2 px-6 py-2.5 font-bold" style="border-color: var(--border-subtle)">{{ t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
