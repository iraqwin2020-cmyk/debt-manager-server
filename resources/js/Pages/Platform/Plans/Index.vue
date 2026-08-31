<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

defineProps({ plans: { type: Array, required: true } });

const editingId = ref(null);
const planToDelete = ref(null);

const createForm = useForm({ name: '', price: 0, duration_days: 30, max_debtors: 50, max_devices: 1 });
function submitCreate() {
    createForm.post(route('platform.plans.store'), { onSuccess: () => createForm.reset() });
}

const editForm = useForm({ name: '', price: 0, duration_days: 30, max_debtors: 50, max_devices: 1 });
function startEdit(plan) {
    editingId.value = plan.id;
    editForm.name = plan.name;
    editForm.price = plan.price;
    editForm.duration_days = plan.duration_days;
    editForm.max_debtors = plan.max_debtors;
    editForm.max_devices = plan.max_devices;
}
function submitEdit(planId) {
    editForm.patch(route('platform.plans.update', planId), { onSuccess: () => (editingId.value = null) });
}

function confirmDelete() {
    router.delete(route('platform.plans.destroy', planToDelete.value.id));
    planToDelete.value = null;
}
</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">الباقات</h1>

        <form class="mb-6 flex flex-wrap items-end gap-3 rounded-card p-4" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="submitCreate">
            <div>
                <label class="mb-1 block text-xs font-semibold">الاسم</label>
                <input v-model="createForm.name" type="text" class="rounded-lg border px-3 py-2.5 text-sm" style="border-color: var(--border-subtle)" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">السعر</label>
                <input :value="createForm.price" type="text" inputmode="numeric" dir="ltr" class="w-24 rounded-lg border px-3 py-2.5 text-end text-sm" style="border-color: var(--border-subtle)" @input="createForm.price = $event.target.value.replace(/[^0-9]/g, '').slice(0, 9)" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">المدة (أيام)</label>
                <input :value="createForm.duration_days" type="text" inputmode="numeric" dir="ltr" class="w-24 rounded-lg border px-3 py-2.5 text-end text-sm" style="border-color: var(--border-subtle)" @input="createForm.duration_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 4)" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">حد العملاء</label>
                <input :value="createForm.max_debtors" type="text" inputmode="numeric" dir="ltr" class="w-24 rounded-lg border px-3 py-2.5 text-end text-sm" style="border-color: var(--border-subtle)" @input="createForm.max_debtors = $event.target.value.replace(/[^0-9]/g, '').slice(0, 5)" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold">حد الأجهزة</label>
                <input :value="createForm.max_devices" type="text" inputmode="numeric" dir="ltr" class="w-24 rounded-lg border px-3 py-2.5 text-end text-sm" style="border-color: var(--border-subtle)" @input="createForm.max_devices = $event.target.value.replace(/[^0-9]/g, '').slice(0, 2)" />
            </div>
            <button type="submit" class="rounded-pill bg-brand-600 px-5 py-2 text-sm font-bold text-white">إضافة باقة</button>
        </form>

        <div class="overflow-x-auto rounded-card" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <table class="data-table w-full text-sm">
                <thead class="sticky top-0" style="background: var(--surface-panel-dark-alt); color: var(--text-on-dark)">
                    <tr>
                        <th class="p-3 text-start">الاسم</th>
                        <th class="p-3 text-start">السعر</th>
                        <th class="p-3 text-start">المدة</th>
                        <th class="p-3 text-start">حد العملاء</th>
                        <th class="p-3 text-start">حد الأجهزة</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="plan in plans" :key="plan.id" class="border-t" style="border-color: var(--border-subtle)">
                        <template v-if="editingId === plan.id">
                            <td class="p-2"><input v-model="editForm.name" class="w-full rounded border px-2 py-1" /></td>
                            <td class="p-2"><input :value="editForm.price" type="text" inputmode="numeric" dir="ltr" class="w-20 rounded border px-2 py-1 text-end" @input="editForm.price = $event.target.value.replace(/[^0-9]/g, '').slice(0, 9)" /></td>
                            <td class="p-2"><input :value="editForm.duration_days" type="text" inputmode="numeric" dir="ltr" class="w-20 rounded border px-2 py-1 text-end" @input="editForm.duration_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 4)" /></td>
                            <td class="p-2"><input :value="editForm.max_debtors" type="text" inputmode="numeric" dir="ltr" class="w-20 rounded border px-2 py-1 text-end" @input="editForm.max_debtors = $event.target.value.replace(/[^0-9]/g, '').slice(0, 5)" /></td>
                            <td class="p-2"><input :value="editForm.max_devices" type="text" inputmode="numeric" dir="ltr" class="w-20 rounded border px-2 py-1 text-end" @input="editForm.max_devices = $event.target.value.replace(/[^0-9]/g, '').slice(0, 2)" /></td>
                            <td class="p-2 text-end">
                                <button type="button" class="text-brand-700 font-bold" @click="submitEdit(plan.id)">حفظ</button>
                                <button type="button" class="ms-2 text-gray-500" @click="editingId = null">إلغاء</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="p-3 font-semibold">{{ plan.name }} <span v-if="plan.is_default_trial" class="text-xs" style="color: var(--text-secondary)">(تجربة افتراضية)</span></td>
                            <td class="p-3"><bdi class="bdi-ltr">{{ plan.price }}</bdi></td>
                            <td class="p-3">{{ plan.duration_days ?? '—' }}</td>
                            <td class="p-3">{{ plan.max_debtors }}</td>
                            <td class="p-3">{{ plan.max_devices }}</td>
                            <td class="p-3 text-end">
                                <button type="button" class="font-bold text-brand-700" @click="startEdit(plan)">تعديل</button>
                                <button v-if="!plan.is_default_trial" type="button" class="ms-3 text-red-600" @click="planToDelete = plan">حذف</button>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>

        <ConfirmModal
            :show="!!planToDelete"
            title="حذف باقة"
            :message="`حذف الباقة «${planToDelete?.name}»؟`"
            confirm-label="حذف"
            danger
            @confirm="confirmDelete"
            @cancel="planToDelete = null"
        />
    </PlatformLayout>
</template>
