<script setup>
import { ref } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DocumentViewerModal from '@/Components/DocumentViewerModal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    debtor: { type: Object, required: true },
    hasIdDocument: { type: Boolean, default: false },
    guarantors: { type: Array, required: true },
    debts: { type: Array, required: true },
});

const showDeleteConfirm = ref(false);
const showDocument = ref(false);

function goBack() {
    window.history.back();
}

function toggleFavorite() {
    router.patch(route('app.debtors.toggle-favorite', props.debtor.id), {}, { preserveScroll: true });
}

function confirmDestroy() {
    showDeleteConfirm.value = false;
    router.delete(route('app.debtors.destroy', props.debtor.id));
}

function fmt(amount, cur) {
    return `${new Intl.NumberFormat('en-US').format(amount)} ${cur === 'USD' ? '$' : 'د.ع'}`;
}

function shareOnWhatsapp() {
    const total = props.debts.reduce((sum, d) => sum + (d.amount - d.paid_amount), 0);
    const text = encodeURIComponent(`مرحباً ${props.debtor.name}، ملخص حسابك: المتبقي ${total.toLocaleString('en-US')}. شكراً لتعاملكم معنا.`);
    const intl = (usePage().props.countryCode ?? '964') + props.debtor.phone.replace(/^0/, '');
    window.open(`https://wa.me/${intl}?text=${text}`, '_blank');
}
</script>

<template>
    <AppLayout>
        <button type="button" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline lg:mb-2 lg:text-xs" @click="goBack"><Icon name="back" style="transform: scaleX(-1)" /> رجوع</button>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 lg:mb-3">
            <h1 class="text-lg font-extrabold sm:text-2xl lg:text-xl">{{ debtor.name }}</h1>
            <button type="button" class="text-3xl lg:text-2xl" @click="toggleFavorite">
                <Icon name="star" :filled="debtor.is_favorite" :style="debtor.is_favorite ? 'color: #f59e0b' : 'color: var(--text-secondary)'" />
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-3 rounded-card p-6 lg:col-span-1 lg:space-y-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <p class="lg:text-xs"><span class="font-semibold">الهاتف:</span> <PhoneLink :phone="debtor.phone" /></p>
                <p class="lg:text-xs"><span class="font-semibold">العنوان:</span> {{ debtor.address ?? '—' }}</p>
                <p v-if="debtor.note" class="lg:text-xs"><span class="font-semibold">ملاحظات:</span> {{ debtor.note }}</p>

                <div class="flex flex-col gap-2 pt-4 lg:gap-1.5 lg:pt-2">
                    <Link :href="route('app.debtors.edit', debtor.id)" class="rounded-pill border-2 px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)">تعديل البيانات</Link>
                    <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-4 py-2 text-center text-sm font-bold text-white lg:px-3 lg:py-1.5 lg:text-xs">+ دين جديد له</Link>
                    <a :href="route('app.debtors.statement', debtor.id)" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)"><Icon name="print" /> طباعة كشف حساب</a>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 px-4 py-2 text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)" @click="showDocument = true"><Icon name="document" /> الوثائق</button>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 border-green-600 px-4 py-2 text-sm font-bold text-green-700 lg:px-3 lg:py-1.5 lg:text-xs" @click="shareOnWhatsapp"><Icon name="whatsapp" /> مشاركة عبر واتساب</button>
                    <button type="button" class="rounded-pill border-2 border-red-500 px-4 py-2 text-sm font-bold text-red-600 lg:px-3 lg:py-1.5 lg:text-xs" @click="showDeleteConfirm = true">حذف العميل</button>
                </div>

                <div v-if="guarantors.length" class="pt-4 lg:pt-2">
                    <h2 class="mb-2 font-bold lg:text-sm">الكفلاء المرتبطون</h2>
                    <ul class="space-y-1 text-sm lg:text-xs">
                        <li v-for="g in guarantors" :key="g.id">{{ g.name }} — <PhoneLink :phone="g.phone" /></li>
                    </ul>
                </div>
            </div>

            <div class="rounded-card p-6 lg:col-span-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-3 font-bold">الديون ({{ debts.length }})</h2>
                <div class="overflow-x-auto">
                    <table class="data-table w-full text-sm">
                        <thead>
                            <tr style="color: var(--text-secondary)">
                                <th class="p-2 text-start">رقم الوصل</th>
                                <th class="p-2 text-start">المبلغ</th>
                                <th class="p-2 text-start">المتبقي</th>
                                <th class="p-2 text-start">النوع</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debts" :key="debt.id" class="border-t" style="border-color: var(--border-subtle)">
                                <td class="p-2">
                                    <a :href="route('app.debts.receipt', debt.id)" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline">
                                        <Icon name="print" /> <bdi class="bdi-ltr">#{{ debt.receipt_number }}</bdi>
                                    </a>
                                </td>
                                <td class="p-2"><bdi class="bdi-ltr">{{ fmt(debt.amount, debt.currency) }}</bdi></td>
                                <td class="p-2"><bdi class="bdi-ltr">{{ fmt(debt.amount - debt.paid_amount, debt.currency) }}</bdi></td>
                                <td class="p-2">{{ debt.payment_type === 'installments' ? 'أقساط' : 'دفعة واحدة' }}</td>
                            </tr>
                            <tr v-if="debts.length === 0">
                                <td colspan="4" class="p-4 text-center" style="color: var(--text-secondary)">لا توجد ديون بعد.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showDeleteConfirm"
            title="حذف عميل"
            :message="`حذف العميل «${debtor.name}»؟ سينتقل لسلة المحذوفات.`"
            confirm-label="حذف"
            danger
            @confirm="confirmDestroy"
            @cancel="showDeleteConfirm = false"
        />

        <DocumentViewerModal
            :show="showDocument"
            :has-document="hasIdDocument"
            :image-url="route('app.debtors.id-document', debtor.id)"
            title="وثائق العميل"
            @close="showDocument = false"
        />
    </AppLayout>
</template>
