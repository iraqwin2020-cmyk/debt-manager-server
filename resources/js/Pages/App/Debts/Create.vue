<script setup>
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonCombobox from '@/Components/PersonCombobox.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Icon from '@/Components/Icon.vue';
import DatePicker from '@/Components/DatePicker.vue';
import SelectMenu from '@/Components/SelectMenu.vue';

const showNoGuarantorConfirm = ref(false);

const form = useForm({
    debtor: { id: null, name: '', phone: '', address: '', note: '' },
    guarantors: [],
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

function addGuarantor() {
    form.guarantors.push({ id: null, name: '', phone: '', address: '', note: '' });
}
function removeGuarantor(index) {
    form.guarantors.splice(index, 1);
}

function submit() {
    if (form.guarantors.length === 0) {
        showNoGuarantorConfirm.value = true;
        return;
    }
    form.post(route('app.debts.store'));
}

function confirmWithoutGuarantor() {
    showNoGuarantorConfirm.value = false;
    form.post(route('app.debts.store'));
}

const unitLabel = computed(() => (form.currency === 'USD' ? '50' : '1000'));
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">تسجيل دين جديد</h1>

            <form class="space-y-6 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="submit">
                <PersonCombobox v-model="form.debtor" type="debtor" label="العميل" />
                <p v-if="form.errors['debtor.name'] || form.errors['debtor.phone']" class="text-sm text-red-600">بيانات العميل غير مكتملة.</p>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-sm font-semibold">الكفيل (اختياري، يمكن أكثر من كفيل)</label>
                        <button type="button" class="text-sm font-bold text-brand-700" @click="addGuarantor">+ إضافة كفيل</button>
                    </div>
                    <div v-for="(g, i) in form.guarantors" :key="i" class="mb-3 flex items-start gap-2">
                        <div class="flex-1">
                            <PersonCombobox v-model="form.guarantors[i]" type="guarantor" label="" />
                        </div>
                        <button type="button" class="mt-2 text-red-600" @click="removeGuarantor(i)"><Icon name="close" /></button>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">العملة</label>
                    <SelectMenu
                        v-model="form.currency"
                        full
                        :options="[
                            { value: 'IQD', label: 'دينار عراقي' },
                            { value: 'USD', label: 'دولار' },
                        ]"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">المبلغ (مضاعفات {{ unitLabel }})</label>
                    <MoneyInput v-model="form.amount" :currency="form.currency" />
                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">الوصف (اختياري)</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">نوع السداد</label>
                    <SelectMenu
                        v-model="form.payment_type"
                        full
                        :options="[
                            { value: 'lump_sum', label: 'دفعة واحدة' },
                            { value: 'installments', label: 'أقساط' },
                        ]"
                    />
                </div>

                <div v-if="form.payment_type === 'lump_sum'">
                    <label class="mb-1 block text-sm font-semibold">تاريخ الاستحقاق</label>
                    <DatePicker v-model="form.due_date" />
                    <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                </div>

                <div v-else class="space-y-4 rounded-xl border p-4" style="border-color: var(--border-subtle)">
                    <p class="text-sm font-bold">حاسبة الأقساط الذكية</p>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="rounded-pill px-4 py-1.5 text-xs font-bold"
                            :class="form.installment_method === 'count' ? 'bg-brand-600 text-white' : 'border'"
                            @click="form.installment_method = 'count'"
                        >بعدد الأقساط</button>
                        <button
                            type="button"
                            class="rounded-pill px-4 py-1.5 text-xs font-bold"
                            :class="form.installment_method === 'amount' ? 'bg-brand-600 text-white' : 'border'"
                            @click="form.installment_method = 'amount'"
                        >بمبلغ القسط</button>
                    </div>

                    <div v-if="form.installment_method === 'count'">
                        <label class="mb-1 block text-sm font-semibold">عدد الأقساط</label>
                        <input
                            :value="form.installment_count"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="form.installment_count = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                        <p v-if="form.errors.installment_count" class="mt-1 text-sm text-red-600">{{ form.errors.installment_count }}</p>
                    </div>
                    <div v-else>
                        <label class="mb-1 block text-sm font-semibold">مبلغ القسط</label>
                        <MoneyInput v-model="form.installment_amount" :currency="form.currency" />
                        <p v-if="form.errors.installment_amount" class="mt-1 text-sm text-red-600">{{ form.errors.installment_amount }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">الفترة بين كل قسط (بالأيام)</label>
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
                        <label class="mb-1 block text-sm font-semibold">تاريخ أول قسط</label>
                        <DatePicker v-model="form.first_due_date" />
                        <p v-if="form.errors.first_due_date" class="mt-1 text-sm text-red-600">{{ form.errors.first_due_date }}</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white disabled:opacity-50">حفظ الدين</button>
                    <Link :href="route('app.debts.index')" class="rounded-pill border-2 px-6 py-2.5 font-bold" style="border-color: var(--border-subtle)">إلغاء</Link>
                </div>
            </form>
        </div>

        <ConfirmModal
            :show="showNoGuarantorConfirm"
            title="بدون كفيل"
            message="هذا الدين بدون كفيل، هل تريد المتابعة؟"
            @confirm="confirmWithoutGuarantor"
            @cancel="showNoGuarantorConfirm = false"
        />
    </AppLayout>
</template>
