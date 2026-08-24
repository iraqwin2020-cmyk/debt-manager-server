<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    debtors: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');

function search() {
    router.get(route('app.debtors.favorites'), { q: q.value }, { preserveState: true, replace: true });
}

function toggleFavorite(debtor) {
    router.patch(route('app.debtors.toggle-favorite', debtor.id), {}, { preserveScroll: true });
}

function fmtRemaining(remaining) {
    if (!remaining || Object.keys(remaining).length === 0) return '—';
    return Object.entries(remaining)
        .map(([cur, val]) => `${new Intl.NumberFormat('en-US').format(val)} ${cur === 'USD' ? '$' : 'د.ع'}`)
        .join(' + ');
}
</script>

<template>
    <AppLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">المفضلة</h1>

        <div class="mb-4 flex gap-3">
            <input
                v-model="q"
                type="text"
                placeholder="بحث بالاسم أو الهاتف..."
                class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm"
                style="border-color: var(--border-subtle)"
                @keyup.enter="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-center"><Icon name="star" filled style="color: #f59e0b" /></th>
                        <th class="p-3 text-start">الاسم</th>
                        <th class="p-3 text-start">الهاتف</th>
                        <th class="p-3 text-start">المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="debtor in debtors.data" :key="debtor.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3 text-center">
                            <button type="button" @click="toggleFavorite(debtor)"><Icon name="star" filled style="color: #f59e0b" /></button>
                        </td>
                        <td class="p-3">
                            <Link :href="route('app.debtors.show', debtor.id)" class="font-semibold hover:underline">{{ debtor.name }}</Link>
                        </td>
                        <td class="p-3"><PhoneLink :phone="debtor.phone" /></td>
                        <td class="p-3"><bdi class="bdi-ltr">{{ fmtRemaining(debtor.remaining) }}</bdi></td>
                    </tr>
                    <tr v-if="debtors.data.length === 0">
                        <td colspan="4" class="p-6 text-center" style="color: var(--text-secondary)">لا يوجد عملاء مفضّلون بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="debtors.links" />
    </AppLayout>
</template>
