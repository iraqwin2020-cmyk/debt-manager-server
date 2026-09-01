<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const { t } = useI18n();

const props = defineProps({
    tenant: { type: Object, required: true },
    plans: { type: Array, default: () => [] },
    planRequests: { type: Array, default: () => [] },
});

const codeForm = useForm({ code: '' });
function redeem() {
    codeForm.post(route('app.settings.redeem-code'), { preserveScroll: true, onSuccess: () => codeForm.reset() });
}

const requestForm = useForm({ plan_id: null });
function requestPlan(planId) {
    requestForm.plan_id = planId;
    requestForm.post(route('app.settings.request-plan'), { preserveScroll: true });
}

const planRequestStatusLabels = { pending: 'قيد الانتظار', approved: 'مقبول', rejected: 'مرفوض' };
const tenantStatusLabels = { active: 'نشط', trial: 'تجربة', expired: 'منتهي', suspended: 'معطّل', cancelled: 'ملغى' };
</script>

<template>
    <AppLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ t('nav.subscriptions') }}</h1>

        <div class="max-w-2xl space-y-6 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <div>
                <p class="text-sm" style="color: var(--text-secondary)">الحالة الحالية</p>
                <p class="text-lg font-bold">{{ tenantStatusLabels[tenant.status] }}</p>
                <p v-if="tenant.trial_ends_at" class="text-sm">انتهاء التجربة: <FormattedDate :value="tenant.trial_ends_at" /></p>
                <p v-if="tenant.subscription_ends_at" class="text-sm">انتهاء الاشتراك: <FormattedDate :value="tenant.subscription_ends_at" /></p>
            </div>

            <form class="space-y-3 border-t pt-6" style="border-color: var(--border-subtle)" @submit.prevent="redeem">
                <label class="block text-sm font-semibold">إدخال كود تفعيل</label>
                <input v-model="codeForm.code" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <p v-if="codeForm.errors.code" class="text-sm text-red-600">{{ codeForm.errors.code }}</p>
                <button type="submit" :disabled="codeForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">تفعيل</button>
            </form>

            <div class="border-t pt-6" style="border-color: var(--border-subtle)">
                <h2 class="mb-3 font-bold">الباقات المتاحة</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="rounded-xl border p-4"
                        :class="tenant.plan_id === plan.id ? 'border-brand-600' : ''"
                        style="border-color: var(--border-subtle)"
                    >
                        <p class="font-bold">{{ plan.name }}</p>
                        <p class="text-sm" style="color: var(--text-secondary)">
                            <CurrencyAmount currency="IQD" :amount="plan.price" /> / {{ plan.duration_days }} يوم
                        </p>
                        <p class="text-xs" style="color: var(--text-secondary)">حد العملاء: {{ plan.max_debtors }} — حد الأجهزة: {{ plan.max_devices }}</p>
                        <span v-if="tenant.plan_id === plan.id" class="mt-2 inline-block rounded-pill bg-brand-600 px-3 py-1 text-xs font-bold text-white">باقتك الحالية</span>
                        <button
                            v-else
                            type="button"
                            :disabled="requestForm.processing"
                            class="mt-2 rounded-pill border-2 border-brand-600 px-4 py-1.5 text-xs font-bold text-brand-700"
                            @click="requestPlan(plan.id)"
                        >
                            طلب هذه الباقة
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="planRequests.length" class="border-t pt-6" style="border-color: var(--border-subtle)">
                <h2 class="mb-3 font-bold">طلباتي السابقة</h2>
                <ul class="space-y-2 text-sm">
                    <li v-for="r in planRequests" :key="r.id" class="flex items-center justify-between rounded-xl border p-3" style="border-color: var(--border-subtle)">
                        <span>{{ r.plan?.name }}</span>
                        <span
                            class="rounded-pill px-3 py-1 text-xs font-bold"
                            :style="r.status === 'approved' ? 'background: var(--status-success-bg); color: var(--status-success-text)' : r.status === 'rejected' ? 'background: var(--status-danger-bg); color: var(--status-danger-text)' : 'background: var(--status-muted-bg); color: var(--status-muted-text)'"
                        >
                            {{ planRequestStatusLabels[r.status] }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
