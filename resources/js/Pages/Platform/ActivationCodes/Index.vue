<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectMenu from '@/Components/SelectMenu.vue';
import Icon from '@/Components/Icon.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const props = defineProps({
    codes: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    tenants: { type: Array, required: true },
    plans: { type: Array, required: true },
});

const q = ref(props.filters.q ?? '');
const status = ref(props.filters.status ?? '');

function search() {
    router.get(route('platform.activation-codes.index'), { q: q.value, status: status.value }, { preserveState: true, replace: true });
}

const preselectedTenant = new URLSearchParams(window.location.search).get('tenant');
const form = useForm({ assigned_tenant_id: preselectedTenant ? Number(preselectedTenant) : '', plan_id: '', expires_at: '' });
function generate() {
    form.post(route('platform.activation-codes.store'), { preserveScroll: true, onSuccess: () => form.reset() });
}

function cancelCode(code) {
    router.patch(route('platform.activation-codes.cancel', code.id), {}, { preserveScroll: true });
}

function copyCode(code) {
    navigator.clipboard?.writeText(code);
}

const statusColors = {
    unused: 'color: var(--status-success-text); background: var(--status-success-bg)',
    used: 'color: #1d4ed8; background: #eff6ff',
    expired: 'color: var(--status-danger-text); background: var(--status-danger-bg)',
    cancelled: 'color: var(--status-muted-text); background: var(--status-muted-bg)',
};

const statusLabels = {
    unused: 'غير مستخدم',
    used: 'مستخدم',
    expired: 'منتهي',
    cancelled: 'ملغى',
};
</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">أكواد التفعيل</h1>

        <form class="mb-6 grid grid-cols-1 items-end gap-3 rounded-card p-4 sm:grid-cols-2 lg:grid-cols-[2fr_2fr_1.5fr_auto]" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="generate">
            <div>
                <label class="mb-1 block text-xs font-semibold">المشترك</label>
                <SelectMenu
                    v-model="form.assigned_tenant_id"
                    full
                    :options="[{ value: '', label: 'اختر...' }, ...tenants.map((t) => ({ value: t.id, label: t.name }))]"
                />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">الباقة</label>
                <SelectMenu
                    v-model="form.plan_id"
                    full
                    :options="[{ value: '', label: 'اختر...' }, ...plans.map((p) => ({ value: p.id, label: p.name }))]"
                />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">تاريخ انتهاء الصلاحية</label>
                <DatePicker v-model="form.expires_at" />
            </div>
            <button type="submit" class="h-fit rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">توليد كود</button>
            <p v-if="form.errors.assigned_tenant_id || form.errors.plan_id || form.errors.expires_at" class="text-sm text-red-600 sm:col-span-2 lg:col-span-4">
                تحقق من الحقول المطلوبة.
            </p>
        </form>

        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="q" type="text" placeholder="بحث بالكود..." class="min-w-[220px] flex-1 rounded-pill border px-4 py-2 text-sm" style="border-color: var(--border-subtle)" @keyup.enter="search" />
            <SelectMenu
                v-model="status"
                :options="[
                    { value: '', label: 'كل الحالات' },
                    { value: 'unused', label: 'غير مستخدم' },
                    { value: 'used', label: 'مستخدم' },
                    { value: 'expired', label: 'منتهي' },
                    { value: 'cancelled', label: 'ملغى' },
                ]"
                @change="search"
            />
            <button type="button" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white" @click="search">بحث</button>
        </div>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">الكود</th>
                        <th class="p-3 text-start">المشترك</th>
                        <th class="p-3 text-start">الباقة</th>
                        <th class="p-3 text-start">الانتهاء</th>
                        <th class="p-3 text-start">الحالة</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="code in codes.data" :key="code.id" class="border-t" style="border-color: var(--border-subtle)">
                        <td class="p-3">
                            <button type="button" class="inline-flex items-center gap-1 font-mono font-bold hover:underline" @click="copyCode(code.code)"><bdi class="bdi-ltr">{{ code.code }}</bdi> <Icon name="copy" /></button>
                        </td>
                        <td class="p-3">
                            <Link v-if="code.assigned_tenant" :href="route('platform.subscribers.show', code.assigned_tenant.id)" class="font-semibold text-brand-700 hover:underline">{{ code.assigned_tenant.name }}</Link>
                        </td>
                        <td class="p-3">{{ code.plan?.name }}</td>
                        <td class="p-3"><FormattedDate :value="code.expires_at" /></td>
                        <td class="p-3"><span class="rounded-pill px-3 py-1 text-xs font-bold" :style="statusColors[code.status]">{{ statusLabels[code.status] }}</span></td>
                        <td class="p-3 text-end">
                            <button v-if="code.status === 'unused'" type="button" class="text-red-600 hover:underline" @click="cancelCode(code)">إلغاء</button>
                        </td>
                    </tr>
                    <tr v-if="codes.data.length === 0">
                        <td colspan="6" class="p-6 text-center" style="color: var(--text-secondary)">لا توجد أكواد بعد.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginator="codes" />
    </PlatformLayout>
</template>
