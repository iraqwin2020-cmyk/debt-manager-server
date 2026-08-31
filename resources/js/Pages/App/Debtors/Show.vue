<script setup>
import { computed, ref } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { RTL_LOCALES } from '@/i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DocumentViewerModal from '@/Components/DocumentViewerModal.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';

const { t, locale } = useI18n();
const isRtl = computed(() => RTL_LOCALES.includes(locale.value));

const props = defineProps({
    debtor: { type: Object, required: true },
    documentCount: { type: Number, default: 0 },
    guarantors: { type: Array, required: true },
    debts: { type: Array, required: true },
});

const documentUrls = Array.from({ length: props.documentCount }, (_, i) => route('app.debtors.id-document', [props.debtor.id, i]));

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

function shareOnWhatsapp() {
    const total = props.debts.reduce((sum, d) => sum + (d.amount - d.paid_amount), 0);
    const text = encodeURIComponent(`مرحباً ${props.debtor.name}، ملخص حسابك: المتبقي ${total.toLocaleString('en-US')}. شكراً لتعاملكم معنا.`);
    const intl = (usePage().props.countryCode ?? '964') + props.debtor.phone.replace(/^0/, '');
    window.open(`https://wa.me/${intl}?text=${text}`, '_blank');
}
</script>

<template>
    <AppLayout>
        <button type="button" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline lg:mb-2 lg:text-xs" @click="goBack"><Icon name="back" :style="isRtl ? 'transform: scaleX(-1)' : ''" /> {{ t('common.back') }}</button>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 lg:mb-3">
            <h1 class="text-lg font-extrabold sm:text-2xl lg:text-xl">{{ debtor.name }}</h1>
            <button type="button" class="text-3xl lg:text-2xl" @click="toggleFavorite">
                <Icon name="star" :filled="debtor.is_favorite" :style="debtor.is_favorite ? 'color: #f59e0b' : 'color: var(--text-secondary)'" />
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-3 rounded-card p-6 lg:col-span-1 lg:space-y-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <p class="lg:text-xs"><span class="font-semibold">{{ t('common.phone') }}:</span> <PhoneLink :phone="debtor.phone" /></p>
                <p class="lg:text-xs"><span class="font-semibold">{{ t('common.address') }}:</span> {{ debtor.address ?? '—' }}</p>
                <p v-if="debtor.note" class="lg:text-xs"><span class="font-semibold">{{ t('common.notes') }}:</span> {{ debtor.note }}</p>

                <div class="flex flex-col gap-2 pt-4 lg:gap-1.5 lg:pt-2">
                    <Link :href="route('app.debtors.edit', debtor.id)" class="rounded-pill border-2 px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)">{{ t('debtors.editData') }}</Link>
                    <Link :href="route('app.debts.create')" class="rounded-pill bg-brand-600 px-4 py-2 text-center text-sm font-bold text-white lg:px-3 lg:py-1.5 lg:text-xs">{{ t('debtors.newDebtFor') }}</Link>
                    <a :href="route('app.debtors.statement', debtor.id)" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 px-4 py-2 text-center text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)"><Icon name="print" /> {{ t('debtors.printStatement') }}</a>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 px-4 py-2 text-sm font-bold lg:px-3 lg:py-1.5 lg:text-xs" style="border-color: var(--border-subtle)" @click="showDocument = true"><Icon name="document" /> {{ t('debtors.documents') }}</button>
                    <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-pill border-2 border-green-600 px-4 py-2 text-sm font-bold text-green-700 lg:px-3 lg:py-1.5 lg:text-xs" @click="shareOnWhatsapp"><Icon name="whatsapp" /> {{ t('debtors.shareWhatsapp') }}</button>
                    <button type="button" class="rounded-pill border-2 border-red-500 px-4 py-2 text-sm font-bold text-red-600 lg:px-3 lg:py-1.5 lg:text-xs" @click="showDeleteConfirm = true">{{ t('debtors.deleteCustomer') }}</button>
                </div>

                <div v-if="guarantors.length" class="pt-4 lg:pt-2">
                    <h2 class="mb-2 font-bold lg:text-sm">{{ t('debtors.linkedGuarantors') }}</h2>
                    <ul class="space-y-1 text-sm lg:text-xs">
                        <li v-for="g in guarantors" :key="g.id">{{ g.name }} — <PhoneLink :phone="g.phone" /></li>
                    </ul>
                </div>
            </div>

            <div class="rounded-card p-6 lg:col-span-2 lg:p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-3 font-bold">{{ t('debtors.debtsCount', { count: debts.length }) }}</h2>
                <div class="overflow-x-auto">
                    <table class="data-table w-full text-sm">
                        <thead>
                            <tr style="color: var(--text-secondary)">
                                <th class="p-2 text-start">{{ t('debtors.colReceiptNo') }}</th>
                                <th class="p-2 text-start">{{ t('debtors.colAmount') }}</th>
                                <th class="p-2 text-start">{{ t('debtors.colRemaining') }}</th>
                                <th class="p-2 text-start">{{ t('debtors.colType') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debts" :key="debt.id" class="border-t" style="border-color: var(--border-subtle)">
                                <td class="p-2">
                                    <a :href="route('app.debts.receipt', debt.id)" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline">
                                        <Icon name="print" /> <bdi class="bdi-ltr">#{{ debt.receipt_number }}</bdi>
                                    </a>
                                </td>
                                <td class="p-2"><CurrencyAmount :currency="debt.currency" :amount="debt.amount" /></td>
                                <td class="p-2"><CurrencyAmount :currency="debt.currency" :amount="debt.amount - debt.paid_amount" /></td>
                                <td class="p-2">{{ debt.payment_type === 'installments' ? t('debtors.typeInstallments') : t('debtors.typeLumpSum') }}</td>
                            </tr>
                            <tr v-if="debts.length === 0">
                                <td colspan="4" class="p-4 text-center" style="color: var(--text-secondary)">{{ t('debtors.noDebtsYet') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showDeleteConfirm"
            :title="t('modal.deleteDebtorTitle')"
            :message="t('modal.deleteDebtorMsg', { name: debtor.name })"
            :confirm-label="t('common.delete')"
            danger
            @confirm="confirmDestroy"
            @cancel="showDeleteConfirm = false"
        />

        <DocumentViewerModal
            :show="showDocument"
            :images="documentUrls"
            :title="t('debtors.documents')"
            @close="showDocument = false"
        />
    </AppLayout>
</template>
