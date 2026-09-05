<script setup>
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormattedDate from '@/Components/FormattedDate.vue';
import PhoneLink from '@/Components/PhoneLink.vue';

const { t } = useI18n();

const props = defineProps({
    tenant: { type: Object, required: true },
    planRequests: { type: Array, default: () => [] },
    contactPhone: { type: String, default: '' },
});

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

            <div v-if="contactPhone" class="space-y-2 border-t pt-6" style="border-color: var(--border-subtle)">
                <p class="text-sm font-semibold">للتواصل مع الإدارة لطلب التفعيل</p>
                <PhoneLink :phone="contactPhone" />
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
