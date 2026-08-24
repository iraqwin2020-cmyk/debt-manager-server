<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PhoneLink from '@/Components/PhoneLink.vue';
import SelectMenu from '@/Components/SelectMenu.vue';

const props = defineProps({
    tenants: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? '');
const type = ref(props.filters.type ?? '');

function search() {
    router.get(route('platform.subscribers.index'), { q: q.value, status: status.value, type: type.value }, { preserveState: true, replace: true });
}

const statusColors = {
    active: 'color: var(--status-success-text); background: var(--status-success-bg)',
    trial: 'color: #1d4ed8; background: #eff6ff',
    expired: 'color: var(--status-danger-text); background: var(--status-danger-bg)',
    suspended: 'color: var(--status-muted-text); background: var(--status-muted-bg)',
    cancelled: 'color: #111; background: #e5e5e5',
};

const statusLabels = {
    active: 'نشط',
    trial: 'تجربة',
    expired: 'منتهي',
    suspended: 'معطّل',
    cancelled: 'ملغى',
};

</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">المشتركون</h1>

        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="q" type="text" placeholder="بحث بالاسم أو الهاتف..." class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <SelectMenu
                v-model="status"
                :options="[
                    { value: '', label: 'كل الحالات' },
                    { value: 'active', label: 'نشط' },
                    { value: 'trial', label: 'تجربة' },
                    { value: 'expired', label: 'منتهي' },
                    { value: 'suspended', label: 'معطّل' },
                    { value: 'cancelled', label: 'ملغى' },
                ]"
                @change="search"
            />
            <SelectMenu
                v-model="type"
                :options="[
                    { value: '', label: 'الكل' },
                    { value: 'online', label: 'أونلاين' },
                    { value: 'offline', label: 'أوفلاين' },
                ]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">الاسم</th>
                        <th class="p-3 text-start">الهاتف</th>
                        <th class="p-3 text-start">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in tenants.data" :key="tenant.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <Link :href="route('platform.subscribers.show', tenant.id)" class="font-semibold hover:underline">{{ tenant.name }}</Link>
                        </td>
                        <td class="p-3"><PhoneLink :phone="tenant.phone" /></td>
                        <td class="p-3">
                            <span class="rounded-pill px-3 py-1 text-xs font-bold" :style="statusColors[tenant.status]">{{ statusLabels[tenant.status] }}</span>
                        </td>
                    </tr>
                    <tr v-if="tenants.data.length === 0">
                        <td colspan="3" class="p-6 text-center" style="color: var(--text-secondary)">لا يوجد مشتركون.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="tenants.links" />
    </PlatformLayout>
</template>
