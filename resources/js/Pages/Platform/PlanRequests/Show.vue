<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    planRequest: { type: Object, required: true },
    debtorsCount: { type: Number, required: true },
});

const showRejectForm = ref(false);
const note = ref('');
const processing = ref(false);

function approve() {
    processing.value = true;
    router.patch(route('platform.plan-requests.approve', props.planRequest.id), {}, {
        onFinish: () => (processing.value = false),
    });
}

function reject() {
    processing.value = true;
    router.patch(route('platform.plan-requests.reject', props.planRequest.id), { note: note.value }, {
        onFinish: () => (processing.value = false),
    });
}

function goBack() {
    window.history.back();
}
</script>

<template>
    <PlatformLayout>
        <button type="button" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline" @click="goBack">
            <Icon name="back" style="transform: scaleX(-1)" /> رجوع
        </button>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">طلب باقة — {{ planRequest.tenant?.name }}</h1>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="space-y-3 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-2 font-bold">بيانات المشترك</h2>
                <p><span class="font-semibold">الاسم:</span> {{ planRequest.tenant?.name }}</p>
                <p><span class="font-semibold">الهاتف:</span> <bdi class="bdi-ltr">{{ planRequest.tenant?.phone }}</bdi></p>
                <p><span class="font-semibold">باقته الحالية:</span> {{ planRequest.tenant?.plan?.name ?? '—' }}</p>
                <p><span class="font-semibold">عدد عملائه الحاليين:</span> {{ debtorsCount }} / {{ planRequest.tenant?.plan?.max_debtors ?? '—' }}</p>
            </div>

            <div class="space-y-3 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h2 class="mb-2 font-bold">الباقة المطلوبة</h2>
                <p><span class="font-semibold">الاسم:</span> {{ planRequest.plan?.name }}</p>
                <p><span class="font-semibold">السعر:</span> <bdi class="bdi-ltr">{{ planRequest.plan?.price }}</bdi> د.ع</p>
                <p><span class="font-semibold">المدة:</span> {{ planRequest.plan?.duration_days }} يوم</p>
                <p><span class="font-semibold">حد العملاء:</span> {{ planRequest.plan?.max_debtors }}</p>
                <p><span class="font-semibold">حد الأجهزة:</span> {{ planRequest.plan?.max_devices }}</p>
            </div>
        </div>

        <div v-if="planRequest.status === 'pending'" class="mt-6 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <div class="flex flex-wrap gap-3">
                <button type="button" :disabled="processing" class="rounded-pill bg-brand-600 px-6 py-2 font-bold text-white" @click="approve">موافقة وتوليد كود تفعيل</button>
                <button type="button" class="rounded-pill border-2 border-red-500 px-6 py-2 font-bold text-red-600" @click="showRejectForm = !showRejectForm">رفض الطلب</button>
            </div>
            <div v-if="showRejectForm" class="mt-4 space-y-2">
                <label class="block text-sm font-semibold">سبب الرفض</label>
                <textarea v-model="note" rows="2" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                <button type="button" :disabled="processing || !note" class="rounded-pill bg-red-600 px-6 py-2 font-bold text-white" @click="reject">تأكيد الرفض</button>
            </div>
        </div>

        <div v-else class="mt-6 rounded-card p-6 text-sm" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <p class="font-bold">{{ planRequest.status === 'approved' ? 'تمت الموافقة على هذا الطلب.' : 'تم رفض هذا الطلب.' }}</p>
            <p v-if="planRequest.note" class="mt-2"><span class="font-semibold">السبب:</span> {{ planRequest.note }}</p>
        </div>
    </PlatformLayout>
</template>
