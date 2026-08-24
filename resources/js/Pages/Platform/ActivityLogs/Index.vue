<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectMenu from '@/Components/SelectMenu.vue';

const props = defineProps({
    logs: { type: Object, required: true },
    actions: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');
const action = ref(props.filters.action ?? '');

function search() {
    router.get(route('platform.activity-logs.index'), { q: q.value, action: action.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">سجل الحركات</h1>

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
                v-model="action"
                :options="[{ value: '', label: 'كل الإجراءات' }, ...actions.map((a) => ({ value: a, label: a }))]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">التاريخ</th>
                        <th class="p-3 text-start">الإجراء</th>
                        <th class="p-3 text-start">المشترك</th>
                        <th class="p-3 text-start">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3"><bdi class="bdi-date" dir="rtl">{{ log.created_at }}</bdi></td>
                        <td class="p-3">{{ log.action }}</td>
                        <td class="p-3">{{ log.tenant?.name ?? '—' }}</td>
                        <td class="p-3">{{ log.description }}</td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="4" class="p-6 text-center" style="color: var(--text-secondary)">لا توجد حركات بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="logs.links" />
    </PlatformLayout>
</template>
