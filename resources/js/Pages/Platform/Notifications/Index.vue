<script setup>
import { router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Icon from '@/Components/Icon.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

defineProps({
    notifications: { type: Object, required: true },
    expiringSoon: { type: Array, required: true },
});

const typeIcons = {
    plan_request: 'document',
    code_used: 'copy',
    new_tenant: 'users',
};

function open(notification) {
    if (!notification.read_at) {
        router.patch(route('platform.notifications.read', notification.id), {}, { preserveScroll: true });
    }
    if (notification.link) {
        router.visit(notification.link);
    }
}

function markAllRead() {
    router.patch(route('platform.notifications.read-all'), {}, { preserveScroll: true });
}
</script>

<template>
    <PlatformLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">الإشعارات</h1>
            <button type="button" class="rounded-pill border-2 px-4 py-1.5 text-sm font-bold" style="border-color: var(--border-subtle)" @click="markAllRead">تحديد الكل كمقروء</button>
        </div>

        <div v-if="expiringSoon.length" class="mb-6 rounded-card p-4" style="background: var(--status-danger-bg)">
            <h2 class="mb-2 text-sm font-bold" style="color: var(--status-danger-text)">اشتراكات مقتربة من الانتهاء</h2>
            <ul class="space-y-1 text-sm">
                <li v-for="t in expiringSoon" :key="t.id">
                    <Link :href="route('platform.subscribers.show', t.id)" class="font-semibold hover:underline" style="color: var(--status-danger-text)">{{ t.name }}</Link>
                    <span style="color: var(--status-danger-text)">
                        — ينتهي <FormattedDate :value="t.subscription_ends_at ?? t.trial_ends_at" />
                    </span>
                </li>
            </ul>
        </div>

        <div class="overflow-hidden rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <button
                v-for="n in notifications.data"
                :key="n.id"
                type="button"
                class="flex w-full items-start gap-3 border-b p-4 text-start transition-[background-color]"
                style="border-color: var(--border-subtle)"
                :style="!n.read_at ? 'background: var(--status-success-bg)' : ''"
                @click="open(n)"
            >
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <Icon :name="typeIcons[n.type] ?? 'bell'" />
                </span>
                <span class="flex-1">
                    <span class="block text-sm" :class="!n.read_at ? 'font-bold' : ''">{{ n.title }}</span>
                    <span class="mt-0.5 block text-xs" style="color: var(--text-secondary)"><FormattedDate :value="n.created_at" /></span>
                </span>
            </button>
            <p v-if="notifications.data.length === 0" class="p-6 text-center text-sm" style="color: var(--text-secondary)">لا توجد إشعارات بعد.</p>
        </div>

        <Pagination :paginator="notifications" />
    </PlatformLayout>
</template>
