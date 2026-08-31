<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';

const { t } = useI18n();

defineProps({
    overdue: { type: Array, required: true },
    dueToday: { type: Array, required: true },
});

function remaining(debt) {
    return debt.amount - debt.paid_amount;
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('notifications.title') }}</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary)">{{ t('notifications.subtitle') }}</p>
        </div>

        <div class="mb-6">
            <h2 class="mb-2 text-sm font-bold" style="color: var(--status-danger-text)">{{ t('notifications.overdueCount', { count: overdue.length }) }}</h2>
            <div class="overflow-hidden rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <Link
                    v-for="d in overdue"
                    :key="d.id"
                    :href="route('app.debtors.show', d.debtor_id)"
                    class="flex w-full items-center gap-3 border-b p-4 text-start transition-[background-color] hover:bg-brand-50"
                    style="border-color: var(--border-subtle)"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm text-white" style="background: var(--status-danger-text)">
                        <Icon name="warning" />
                    </span>
                    <span class="flex-1">
                        <span class="block text-sm font-bold">{{ d.debtor?.name ?? '—' }}</span>
                        <span class="mt-0.5 block text-xs" style="color: var(--text-secondary)">
                            <CurrencyAmount :currency="d.currency" :amount="remaining(d)" /> — {{ t('notifications.dueOn') }} <bdi class="bdi-date" dir="rtl">{{ d.due_date }}</bdi>
                        </span>
                    </span>
                </Link>
                <p v-if="overdue.length === 0" class="p-6 text-center text-sm" style="color: var(--text-secondary)">{{ t('notifications.noOverdue') }}</p>
            </div>
        </div>

        <div>
            <h2 class="mb-2 text-sm font-bold" style="color: var(--status-warning-text)">{{ t('notifications.dueTodayCount', { count: dueToday.length }) }}</h2>
            <div class="overflow-hidden rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <Link
                    v-for="d in dueToday"
                    :key="d.id"
                    :href="route('app.debtors.show', d.debtor_id)"
                    class="flex w-full items-center gap-3 border-b p-4 text-start transition-[background-color] hover:bg-brand-50"
                    style="border-color: var(--border-subtle)"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm text-white" style="background: var(--status-warning-text)">
                        <Icon name="calendar" />
                    </span>
                    <span class="flex-1">
                        <span class="block text-sm font-bold">{{ d.debtor?.name ?? '—' }}</span>
                        <span class="mt-0.5 block text-xs" style="color: var(--text-secondary)">
                            <CurrencyAmount :currency="d.currency" :amount="remaining(d)" />
                        </span>
                    </span>
                </Link>
                <p v-if="dueToday.length === 0" class="p-6 text-center text-sm" style="color: var(--text-secondary)">{{ t('notifications.noDueToday') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
