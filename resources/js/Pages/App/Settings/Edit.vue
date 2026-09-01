<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const { t } = useI18n();

const props = defineProps({
    tenant: { type: Object, required: true },
    plans: { type: Array, default: () => [] },
    planRequests: { type: Array, default: () => [] },
    about: { type: Object, required: true },
});

const validTabs = ['general', 'receipts', 'subscription', 'about'];
const requestedTab = new URLSearchParams(window.location.search).get('tab');
const tab = ref(validTabs.includes(requestedTab) ? requestedTab : 'general');
const tabs = computed(() => [
    { key: 'general', label: t('settings.tabs.general') },
    { key: 'receipts', label: t('settings.tabs.receipts') },
    { key: 'subscription', label: t('settings.tabs.subscription') },
    { key: 'about', label: t('settings.tabs.about') },
]);

const generalForm = useForm({
    locale: props.tenant.locale,
    rows_per_page: props.tenant.rows_per_page,
    due_reminder_days: props.tenant.due_reminder_days,
    overdue_grace_days: props.tenant.overdue_grace_days,
});
function saveGeneral() {
    generalForm.patch(route('app.settings.general'), { preserveScroll: true });
}

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

const contactForm = useForm({ email: '', message: '' });
function sendContactMessage() {
    contactForm.post(route('app.settings.about-message'), {
        preserveScroll: true,
        onSuccess: () => contactForm.reset(),
    });
}
</script>

<template>
    <AppLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ t('settings.title') }}</h1>

        <div class="flex flex-col gap-6 md:flex-row">
            <div class="flex justify-between gap-1 overflow-x-auto md:w-48 md:flex-col md:justify-start md:gap-2">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    type="button"
                    class="min-w-0! whitespace-nowrap rounded-pill border border-transparent px-2.5 py-2.5 text-sm font-semibold transition-[border-color] md:px-4 md:min-w-24!"
                    :class="tab === t.key ? 'bg-brand-600 text-white' : 'hover:border-brand-400'"
                    :style="tab === t.key ? '' : 'color: var(--text-primary)'"
                    @click="tab = t.key"
                >
                    {{ t.label }}
                </button>
            </div>

            <div class="flex-1 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <form v-if="tab === 'general'" class="space-y-4" @submit.prevent="saveGeneral">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('settings.language') }}</label>
                        <SelectMenu
                            v-model="generalForm.locale"
                            full
                            :options="[
                                { value: 'ar', label: t('settings.languages.ar') },
                                { value: 'en', label: t('settings.languages.en') },
                                { value: 'ku', label: t('settings.languages.ku') },
                            ]"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('settings.rowsPerPage') }}</label>
                        <input
                            :value="generalForm.rows_per_page"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.rows_per_page = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('settings.dueReminderDays') }}</label>
                        <input
                            :value="generalForm.due_reminder_days"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.due_reminder_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 2)"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">{{ t('settings.overdueGraceDays') }}</label>
                        <input
                            :value="generalForm.overdue_grace_days"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.overdue_grace_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 2)"
                        />
                    </div>
                    <button type="submit" :disabled="generalForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">{{ t('common.save') }}</button>
                </form>

                <div v-else-if="tab === 'receipts'" class="space-y-2 text-sm">
                    <p style="color: var(--text-secondary)">أرقام الوصولات تسلسلية تلقائياً ولا يمكن تعديلها بعد الإعداد الأولي.</p>
                    <p>رقم وصل الدين القادم: <bdi class="bdi-ltr font-bold">{{ tenant.next_debt_receipt_number }}</bdi></p>
                    <p>رقم وصل التسديد القادم: <bdi class="bdi-ltr font-bold">{{ tenant.next_payment_receipt_number }}</bdi></p>
                </div>

                <div v-else-if="tab === 'subscription'" class="space-y-6">
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

                <div v-else-if="tab === 'about'" class="space-y-6">
                    <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ about.description }}</p>

                    <div class="space-y-2 border-t pt-6 text-sm" style="border-color: var(--border-subtle)">
                        <p v-if="about.whatsapp"><span class="font-semibold">للتواصل عبر واتساب:</span> <PhoneLink :phone="about.whatsapp" /></p>
                        <p v-if="about.company_name"><span class="font-semibold">الشركة المطوّرة:</span> {{ about.company_name }}</p>
                        <p><a :href="route('privacy-policy')" target="_blank" rel="noopener" class="font-semibold text-brand-700 hover:underline">سياسة الخصوصية</a></p>
                    </div>

                    <form v-if="about.email" class="space-y-3 border-t pt-6" style="border-color: var(--border-subtle)" @submit.prevent="sendContactMessage">
                        <h2 class="font-bold">مراسلة الشركة عبر البريد الإلكتروني</h2>
                        <div>
                            <label class="mb-1 block text-sm font-semibold">بريدك الإلكتروني</label>
                            <input v-model="contactForm.email" type="email" dir="ltr" class="w-full rounded-xl border px-4 py-2.5 text-end" style="border-color: var(--border-subtle)" />
                            <p v-if="contactForm.errors.email" class="mt-1 text-sm text-red-600">{{ contactForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold">نص الرسالة</label>
                            <textarea v-model="contactForm.message" rows="4" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                            <p v-if="contactForm.errors.message" class="mt-1 text-sm text-red-600">{{ contactForm.errors.message }}</p>
                        </div>
                        <button type="submit" :disabled="contactForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">إرسال</button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
