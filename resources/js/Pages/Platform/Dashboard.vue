<script setup>
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ stats: { type: Object, required: true } });
</script>

<template>
    <PlatformLayout>
        <div class="mb-6">
            <h1 class="text-lg font-extrabold sm:text-2xl">الصفحة الرئيسية</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary)">نظرة سريعة على المنصة</p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <StatCard label="إجمالي المشتركين" :value="stats.total" :href="route('platform.subscribers.index')">
                <template #icon><Icon name="users" /></template>
            </StatCard>
            <StatCard label="مقتربون من الانتهاء" :value="stats.expiringSoon" :href="route('platform.subscribers.index')">
                <template #icon><Icon name="warning" /></template>
            </StatCard>
            <StatCard label="طلبات خطط معلّقة" :value="stats.pendingPlanRequests" :href="route('platform.plan-requests.index', { status: 'pending' })">
                <template #icon><Icon name="document" /></template>
            </StatCard>
            <StatCard label="أكواد غير مستخدمة" :value="stats.unusedCodes" :href="route('platform.activation-codes.index', { status: 'unused' })">
                <template #icon><Icon name="copy" /></template>
            </StatCard>
        </div>

        <div class="mt-8">
            <h2 class="mb-3 text-sm font-bold" style="color: var(--text-secondary)">توزيع حالات الاشتراك</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                <StatCard label="نشط" :value="stats.active" :href="route('platform.subscribers.index', { status: 'active' })">
                    <template #icon><Icon name="dot" style="color: #16a34a" /></template>
                </StatCard>
                <StatCard label="تجربة" :value="stats.trial" :href="route('platform.subscribers.index', { status: 'trial' })">
                    <template #icon><Icon name="dot" style="color: #2563eb" /></template>
                </StatCard>
                <StatCard label="منتهي" :value="stats.expired" :href="route('platform.subscribers.index', { status: 'expired' })">
                    <template #icon><Icon name="dot" style="color: #dc2626" /></template>
                </StatCard>
                <StatCard label="معطّل" :value="stats.suspended" :href="route('platform.subscribers.index', { status: 'suspended' })">
                    <template #icon><Icon name="dot" style="color: #9ca3af" /></template>
                </StatCard>
                <StatCard label="ملغى" :value="stats.cancelled" :href="route('platform.subscribers.index', { status: 'cancelled' })">
                    <template #icon><Icon name="dot" style="color: #374151" /></template>
                </StatCard>
            </div>
        </div>

        <div class="mt-8 hidden flex-wrap gap-3 md:flex">
            <Link :href="route('platform.activation-codes.index')" class="rounded-pill bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">توليد كود تفعيل</Link>
            <Link :href="route('platform.plans.index')" class="rounded-pill border-2 border-brand-600 px-6 py-2.5 text-sm font-bold text-brand-700 transition hover:-translate-y-0.5 hover:bg-brand-50">إدارة الباقات</Link>
        </div>
    </PlatformLayout>
</template>
