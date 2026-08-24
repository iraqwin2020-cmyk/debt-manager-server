<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PhoneLink from '@/Components/PhoneLink.vue';

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
            <h1 class="text-lg font-extrabold sm:text-2xl">الكفلاء</h1>
            <Link :href="route('app.guarantors.create')" class="rounded-pill bg-brand-600 px-3 py-1.5 text-xs font-bold text-white sm:px-5 sm:py-2 sm:text-sm">+ إضافة</Link>
        </div>

        <div class="mb-4 flex gap-3">
            <input
                v-model="q"
                type="text"
                placeholder="بحث بالاسم أو الهاتف..."
                class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm"
                style="border-color: var(--border-subtle)"
                @keyup.enter="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-3 py-1.5 text-xs font-bold text-white sm:px-5 sm:py-2 sm:text-sm" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">الاسم</th>
                        <th class="p-3 text-start">الهاتف</th>
                        <th class="p-3 text-start">المكفولون</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="guarantor in guarantors.data" :key="guarantor.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <Link :href="route('app.guarantors.show', guarantor.id)" class="font-semibold hover:underline">{{ guarantor.name }}</Link>
                        </td>
                        <td class="p-3"><PhoneLink :phone="guarantor.phone" /></td>
                        <td class="p-3">{{ guarantor.debtors_count }}</td>
                    </tr>
                    <tr v-if="guarantors.data.length === 0">
                        <td colspan="3" class="p-6 text-center" style="color: var(--text-secondary)">لا يوجد كفلاء بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="guarantors.links" />
    </AppLayout>
</template>
