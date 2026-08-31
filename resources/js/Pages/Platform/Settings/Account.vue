<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

const accountForm = useForm({ name: props.user.name });
function saveAccount() {
    accountForm.patch(route('platform.settings.account'), { preserveScroll: true });
}

const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
function savePassword() {
    passwordForm.patch(route('platform.settings.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

function logout() {
    useForm({}).post(route('platform.logout'));
}
</script>

<template>
    <PlatformLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-lg font-extrabold sm:text-2xl">إدارة الحساب</h1>
            <Link :href="route('platform.settings.edit')" class="text-sm font-semibold text-brand-700 hover:underline">الإعدادات</Link>
        </div>

        <div class="mx-auto max-w-lg space-y-8 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <form class="space-y-4" @submit.prevent="saveAccount">
                <h2 class="font-bold">بيانات الحساب</h2>
                <div>
                    <label class="mb-1 block text-sm font-semibold">الاسم</label>
                    <input v-model="accountForm.name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold">رقم الهاتف (للعرض فقط)</label>
                    <input :value="user.phone" type="text" dir="ltr" disabled class="w-full rounded-xl border px-4 py-2.5 text-end" style="border-color: var(--border-subtle); background: var(--surface-page); color: var(--text-secondary)" />
                </div>
                <button type="submit" :disabled="accountForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">حفظ</button>
            </form>

            <form class="space-y-4 border-t pt-6" style="border-color: var(--border-subtle)" @submit.prevent="savePassword">
                <h2 class="font-bold">تغيير كلمة المرور</h2>
                <input v-model="passwordForm.current_password" type="password" placeholder="كلمة المرور الحالية" autocomplete="current-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                <input v-model="passwordForm.password" type="password" placeholder="كلمة المرور الجديدة" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <input v-model="passwordForm.password_confirmation" type="password" placeholder="تأكيد كلمة المرور" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <button type="submit" :disabled="passwordForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">تغيير</button>
            </form>

            <div class="border-t pt-6" style="border-color: var(--border-subtle)">
                <button type="button" class="rounded-pill border-2 border-red-500 px-6 py-2 font-bold text-red-600" @click="logout">تسجيل الخروج</button>
            </div>
        </div>
    </PlatformLayout>
</template>
