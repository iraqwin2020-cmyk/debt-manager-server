<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import DatePicker from '@/Components/DatePicker.vue';
import Icon from '@/Components/Icon.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const props = defineProps({
    tenant: { type: Object, required: true },
    debtorsCount: { type: Number, required: true },
    devicesCount: { type: Number, required: true },
    logs: { type: Array, required: true },
});

const showCancelConfirm = ref(false);
const showDeleteConfirm = ref(false);
const showLogoutDevicesConfirm = ref(false);
const editingDate = ref(false);
const subscriptionDate = ref(props.tenant.subscription_ends_at?.slice(0, 10) ?? '');

const statusLabels = {
    active: 'نشط',
    trial: 'تجربة',
    expired: 'منتهي',
    suspended: 'معطّل',
    cancelled: 'ملغى',
};

function setStatus(status) {
    router.patch(route('platform.subscribers.update-status', props.tenant.id), { status }, { preserveScroll: true });
}

function cancelSubscription() {
    showCancelConfirm.value = false;
    setStatus('cancelled');
}

function destroyTenant() {
    showDeleteConfirm.value = false;
    router.delete(route('platform.subscribers.destroy', props.tenant.id));
}

function logoutAllDevices() {
    showLogoutDevicesConfirm.value = false;
    router.post(route('platform.subscribers.logout-devices', props.tenant.id), {}, { preserveScroll: true });
}

function saveSubscriptionDate() {
    router.patch(
        route('platform.subscribers.update-subscription-date', props.tenant.id),
        { subscription_ends_at: subscriptionDate.value },
        { preserveScroll: true, onSuccess: () => (editingDate.value = false) }
    );
}
</script>

<template>
    <PlatformLayout>
        <Link :href="route('platform.subscribers.index')" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline"><Icon name="back" style="transform: scaleX(-1)" /> رجوع</Link>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ tenant.name }}</h1>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="space-y-3 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <p><span class="font-semibold">الهاتف:</span> <bdi class="bdi-ltr">{{ tenant.phone }}</bdi></p>
                <p><span class="font-semibold">النوع:</span> {{ tenant.type }}</p>
                <p><span class="font-semibold">الباقة:</span> {{ tenant.plan?.name }}</p>
                <p><span class="font-semibold">الحالة:</span> {{ statusLabels[tenant.status] }}</p>
                <p v-if="tenant.trial_ends_at"><span class="font-semibold">انتهاء التجربة:</span> <FormattedDate :value="tenant.trial_ends_at" /></p>
                <div v-if="tenant.subscription_ends_at || editingDate">
                    <span class="font-semibold">انتهاء الاشتراك:</span>
                    <FormattedDate v-if="!editingDate" :value="tenant.subscription_ends_at" />
                    <button v-if="!editingDate" type="button" class="ms-2 text-xs font-bold text-brand-700 hover:underline" @click="editingDate = true">تعديل يدوي</button>
                    <div v-else class="mt-2 flex items-center gap-2">
                        <DatePicker v-model="subscriptionDate" />
                        <button type="button" class="rounded-pill bg-brand-600 px-3 py-1.5 text-xs font-bold text-white" @click="saveSubscriptionDate">حفظ</button>
                        <button type="button" class="text-xs font-semibold" @click="editingDate = false">إلغاء</button>
                    </div>
                </div>
                <p><span class="font-semibold">عدد العملاء:</span> {{ debtorsCount }} / {{ tenant.plan?.max_debtors }}</p>
                <p><span class="font-semibold">عدد الأجهزة:</span> {{ devicesCount }} / {{ tenant.plan?.max_devices }}</p>

                <div class="flex flex-wrap gap-2 pt-4">
                    <button v-if="tenant.status === 'suspended'" type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="setStatus('active')">تفعيل</button>
                    <button v-else-if="tenant.status !== 'cancelled'" type="button" class="rounded-pill border-2 border-red-500 px-5 py-2 text-sm font-bold text-red-600" @click="setStatus('suspended')">تعطيل</button>
                    <button v-if="tenant.status !== 'cancelled'" type="button" class="rounded-pill bg-black px-5 py-2 text-sm font-bold text-white" @click="showCancelConfirm = true">إلغاء الاشتراك</button>
                    <button v-if="tenant.status === 'cancelled'" type="button" class="rounded-pill border-2 border-red-500 px-5 py-2 text-sm font-bold text-red-600" @click="showDeleteConfirm = true">حذف نهائي</button>
                    <Link :href="route('platform.activation-codes.index', { tenant: tenant.id })" class="rounded-pill border-2 px-5 py-2 text-sm font-bold" style="border-color: var(--border-subtle)">توليد كود تفعيل له</Link>
                    <button type="button" class="rounded-pill border-2 px-5 py-2 text-sm font-bold" style="border-color: var(--border-subtle)" @click="showLogoutDevicesConfirm = true">تسجيل خروج من كل الأجهزة</button>
                </div>
            </div>

            <div class="rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-3 font-bold">سجل الحركات</h2>
                <ul class="space-y-2 text-sm">
                    <li v-for="log in logs" :key="log.id" class="border-b pb-2" style="border-color: var(--border-subtle)">
                        <span class="font-semibold">{{ log.action }}</span> — {{ log.description }}
                        <div style="color: var(--text-secondary)"><FormattedDate :value="log.created_at" /></div>
                    </li>
                    <li v-if="logs.length === 0" style="color: var(--text-secondary)">لا توجد حركات بعد.</li>
                </ul>
            </div>
        </div>

        <ConfirmModal
            :show="showCancelConfirm"
            title="إلغاء الاشتراك"
            message="هذا يضع الحساب بحالة «ملغى» ويفتح إمكانية حذفه لاحقاً. متابعة؟"
            confirm-label="إلغاء الاشتراك"
            danger
            @confirm="cancelSubscription"
            @cancel="showCancelConfirm = false"
        />
        <ConfirmModal
            :show="showDeleteConfirm"
            title="حذف نهائي"
            message="سيُنقل هذا المشترك لسلة المحذوفات. متابعة؟"
            confirm-label="حذف"
            danger
            @confirm="destroyTenant"
            @cancel="showDeleteConfirm = false"
        />
        <ConfirmModal
            :show="showLogoutDevicesConfirm"
            title="تسجيل خروج من كل الأجهزة"
            message="سيُطلَب من هذا المشترك تسجيل الدخول من جديد بكل أجهزته. متابعة؟"
            confirm-label="تسجيل خروج"
            danger
            @confirm="logoutAllDevices"
            @cancel="showLogoutDevicesConfirm = false"
        />
    </PlatformLayout>
</template>
