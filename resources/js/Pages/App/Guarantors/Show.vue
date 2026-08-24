<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DocumentViewerModal from '@/Components/DocumentViewerModal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    guarantor: { type: Object, required: true },
    hasIdDocument: { type: Boolean, default: false },
    debts: { type: Array, required: true },
});

const showDeleteConfirm = ref(false);
const showDocument = ref(false);

function goBack() {
    window.history.back();
}

function confirmDestroy() {
    showDeleteConfirm.value = false;
    router.delete(route('app.guarantors.destroy', props.guarantor.id));
}

function fmt(amount, cur) {
    return `${new Intl.NumberFormat('en-US').format(amount)} ${cur === 'USD' ? '$' : 'د.ع'}`;
}
</script>

<template>
    <AppLayout>
        <button type="button" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline lg:mb-2 lg:text-xs" @click="goBack"><Icon name="back" style="transform: scaleX(-1)" /> رجوع</button>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl lg:mb-3 lg:text-xl">{{ guarantor.name }}</h1>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-3 rounded-card p-6 lg:col-span-1 lg:space-y-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <p class="lg:text-xs"><span class="font-semibold">الهاتف:</span> <PhoneLink :phone="guarantor.phone" /></p>
                <p class="lg:text-xs"><span class="font-semibold">العنوان:</span> {{ guarantor.address ?? '—' }}</p>
                <p v-if="guarantor.note" class="lg:text-xs"><span class="font-semibold">ملاحظات:</span> {{ guarantor.note }}</p>
                <p class="text-sm lg:text-xs" style="color: var(--text-secondary)">الكفيل نفسه ليس عليه دين — القائمة أدناه هي عملاء يتكفّل بهم.</p>

                <div class="flex flex-col gap-2 pt-4 lg:gap-1.5 lg:pt-2">
                    <Link :href="route('app.guarantors.edit', guarantor.id)" class="rounded-pill border-2 px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)">تعديل البيانات</Link>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 px-4 py-2 text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)" @click="showDocument = true"><Icon name="document" /> الوثائق</button>
                    <button type="button" class="rounded-pill border-2 border-red-500 px-4 py-2 text-sm font-bold text-red-600 lg:px-3 lg:py-1.5 lg:text-xs" @click="showDeleteConfirm = true">حذف الكفيل</button>
                </div>
            </div>

            <div class="rounded-card p-6 lg:col-span-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-3 font-bold lg:text-sm">العملاء الذين يتكفّل بهم ({{ debts.length }})</h2>
                <div class="overflow-x-auto">
                    <table class="data-table w-full text-sm">
                        <thead>
                            <tr style="color: var(--text-secondary)">
                                <th class="p-2 text-start">العميل</th>
                                <th class="p-2 text-start">رقم الوصل</th>
                                <th class="p-2 text-start">المبلغ</th>
                                <th class="p-2 text-start">المتبقي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debts" :key="debt.id" class="border-t" style="border-color: var(--border-subtle)">
                                <td class="p-2">
                                    <Link :href="route('app.debtors.show', debt.debtor.id)" class="font-semibold hover:underline">{{ debt.debtor?.name }}</Link>
                                </td>
                                <td class="p-2"><bdi class="bdi-ltr">#{{ debt.receipt_number }}</bdi></td>
                                <td class="p-2"><bdi class="bdi-ltr">{{ fmt(debt.amount, debt.currency) }}</bdi></td>
                                <td class="p-2"><bdi class="bdi-ltr">{{ fmt(debt.amount - debt.paid_amount, debt.currency) }}</bdi></td>
                            </tr>
                            <tr v-if="debts.length === 0">
                                <td colspan="4" class="p-4 text-center" style="color: var(--text-secondary)">لا يتكفّل بأي عميل حالياً.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showDeleteConfirm"
            title="حذف كفيل"
            :message="`حذف الكفيل «${guarantor.name}»؟ سينتقل لسلة المحذوفات.`"
            confirm-label="حذف"
            danger
            @confirm="confirmDestroy"
            @cancel="showDeleteConfirm = false"
        />

        <DocumentViewerModal
            :show="showDocument"
            :has-document="hasIdDocument"
            :image-url="route('app.guarantors.id-document', guarantor.id)"
            title="وثائق الكفيل"
            @close="showDocument = false"
        />
    </AppLayout>
</template>
