<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const props = defineProps({
    planRequests: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? '');

const statusColors = {
    pending: 'color: var(--status-muted-text); background: var(--status-muted-bg)',
    approved: 'color: var(--status-success-text); background: var(--status-success-bg)',
    rejected: 'color: var(--status-danger-text); background: var(--status-danger-bg)',
};
const statusLabels = { pending: 'معلّق', approved: 'موافَق عليه', rejected: 'مرفوض' };

function search() {
    router.get(route('platform.plan-requests.index'), { q: q.value, status: status.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">طلبات الخطط</h1>

        <div class="mb-4 flex flex-wrap gap-3">
            <input
                v-model="q"
                type="text"
                placeholder="بحث باسم المشترك..."
                class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm"
                style="border-color: var(--border-subtle)"
                @keyup.enter="search"
            />
            <SelectMenu
                v-model="status"
                :options="[
                    { value: '', label: 'كل الحالات' },
                    { value: 'pending', label: 'معلّق' },
                    { value: 'approved', label: 'موافَق عليه' },
                    { value: 'rejected', label: 'مرفوض' },
                ]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">المشترك</th>
                        <th class="p-3 text-start">الباقة المطلوبة</th>
                        <th class="p-3 text-start">تاريخ الطلب</th>
                        <th class="p-3 text-start">الحالة</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in planRequests.data" :key="r.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3 font-semibold">{{ r.tenant?.name }}</td>
                        <td class="p-3">{{ r.plan?.name }}</td>
                        <td class="p-3"><FormattedDate :value="r.created_at" /></td>
                        <td class="p-3"><span class="rounded-pill px-3 py-1 text-xs font-bold" :style="statusColors[r.status]">{{ statusLabels[r.status] }}</span></td>
                        <td class="p-3 text-end">
                            <Link :href="route('platform.plan-requests.show', r.id)" class="text-sm font-bold text-brand-700 hover:underline">عرض</Link>
                        </td>
                    </tr>
                    <tr v-if="planRequests.data.length === 0">
                        <td colspan="5" class="p-6 text-center" style="color: var(--text-secondary)">لا توجد طلبات بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="planRequests" />
    </PlatformLayout>
</template>
