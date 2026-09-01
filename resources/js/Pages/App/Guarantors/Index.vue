<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import StatCard from '@/Components/StatCard.vue';
import Icon from '@/Components/Icon.vue';

const { t } = useI18n();

const props = defineProps({
    guarantors: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');

function search() {
    router.get(route('app.guarantors.index'), { q: q.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('nav.guarantors') }}</h1>
            <Link :href="route('app.guarantors.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">{{ t('common.add') }}</Link>
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard :label="t('guarantors.countLabel')" :value="guarantors.total">
                <template #icon><Icon name="users" /></template>
            </StatCard>
        </div>

        <div class="mb-4 flex gap-3">
            <input
                v-model="q"
                type="text"
                :placeholder="t('debtors.searchPlaceholder')"
                class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm"
                style="border-color: var(--border-subtle)"
                @keyup.enter="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">{{ t('common.search') }}</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">{{ t('debtors.colName') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colPhone') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debtors.colAddress') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debtors.colNotes') }}</th>
                        <th class="p-3 text-start">{{ t('guarantors.colGuaranteedCount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="guarantor in guarantors.data" :key="guarantor.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <Link :href="route('app.guarantors.show', guarantor.id)" class="font-semibold hover:underline">{{ guarantor.name }}</Link>
                        </td>
                        <td class="p-3"><PhoneLink :phone="guarantor.phone" /></td>
                        <td class="hidden max-w-[14rem] truncate p-3 lg:table-cell">{{ guarantor.address || '—' }}</td>
                        <td class="hidden max-w-[14rem] truncate p-3 lg:table-cell">{{ guarantor.note || '—' }}</td>
                        <td class="p-3">{{ guarantor.debtors_count }}</td>
                    </tr>
                    <tr v-if="guarantors.data.length === 0">
                        <td colspan="5" class="p-6 text-center" style="color: var(--text-secondary)">{{ t('guarantors.empty') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="guarantors" />
    </AppLayout>
</template>
