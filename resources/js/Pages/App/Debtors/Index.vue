<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import Icon from '@/Components/Icon.vue';
import CurrencyAmount from '@/Components/CurrencyAmount.vue';
import StatCard from '@/Components/StatCard.vue';

const { t } = useI18n();

const props = defineProps({
    debtors: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');
const filterValue = ref(props.filters.filter ?? '');

function search() {
    router.get(route('app.debtors.index'), { q: q.value, filter: filterValue.value }, { preserveState: true, replace: true });
}

function toggleFavorite(debtor) {
    router.patch(route('app.debtors.toggle-favorite', debtor.id), {}, { preserveScroll: true });
}

function remainingEntries(remaining) {
    if (!remaining || Object.keys(remaining).length === 0) return [];
    return Object.entries(remaining).map(([cur, val]) => ({ cur, val }));
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('debtors.title') }}</h1>
            <Link :href="route('app.debtors.create')" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">{{ t('common.add') }}</Link>
        </div>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard :label="t('debtors.countLabel')" :value="debtors.total">
                <template #icon><Icon name="users" /></template>
            </StatCard>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            <input
                v-model="q"
                type="text"
                :placeholder="t('debtors.searchPlaceholder')"
                class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm"
                style="border-color: var(--border-subtle)"
                @keyup.enter="search"
            />
            <SelectMenu
                v-model="filterValue"
                :options="[{ value: '', label: t('debtors.filterAll') }, { value: 'favorites', label: t('debtors.filterFavoritesOnly') }]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">{{ t('common.search') }}</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">{{ t('debtors.colName') }}</th>
                        <th class="hidden p-3 text-start md:table-cell">{{ t('debtors.colPhone') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debtors.colAddress') }}</th>
                        <th class="hidden p-3 text-start lg:table-cell">{{ t('debtors.colNotes') }}</th>
                        <th class="p-3 text-start">{{ t('debtors.colRemaining') }}</th>
                        <th class="hidden p-3 text-center md:table-cell"><Icon name="star" filled style="color: #f59e0b" /></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="debtor in debtors.data" :key="debtor.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <Link :href="route('app.debtors.show', debtor.id)" class="font-semibold hover:underline">{{ debtor.name }}</Link>
                        </td>
                        <td class="hidden p-3 md:table-cell"><PhoneLink :phone="debtor.phone" /></td>
                        <td class="hidden max-w-[14rem] truncate p-3 lg:table-cell">{{ debtor.address || '—' }}</td>
                        <td class="hidden max-w-[14rem] truncate p-3 lg:table-cell">{{ debtor.note || '—' }}</td>
                        <td class="p-3">
                            <template v-if="remainingEntries(debtor.remaining).length === 0">—</template>
                            <template v-for="(entry, i) in remainingEntries(debtor.remaining)" :key="entry.cur">
                                <span v-if="i > 0"> + </span>
                                <CurrencyAmount :currency="entry.cur" :amount="entry.val" />
                            </template>
                        </td>
                        <td class="hidden p-3 text-center md:table-cell">
                            <button type="button" @click="toggleFavorite(debtor)">
                                <Icon name="star" :filled="debtor.is_favorite" :style="debtor.is_favorite ? 'color: #f59e0b' : 'color: var(--text-secondary)'" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="debtors.data.length === 0">
                        <td colspan="6" class="p-6 text-center" style="color: var(--text-secondary)">{{ t('debtors.empty') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="debtors" />
    </AppLayout>
</template>
