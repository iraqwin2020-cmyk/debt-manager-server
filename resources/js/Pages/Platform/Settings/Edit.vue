<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    settings: { type: Object, required: true },
    about: { type: Object, required: true },
});

const tab = ref('account');
const tabs = [
    { key: 'account', label: 'إدارة الحساب' },
    { key: 'general', label: 'عام' },
    { key: 'about', label: 'حول' },
];

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

const generalForm = useForm({
    trial_days: props.settings.trial_days,
    country_code: props.settings.country_code,
    rows_per_page: props.settings.rows_per_page,
});
function saveGeneral() {
    generalForm.patch(route('platform.settings.general'), { preserveScroll: true });
}

const aboutForm = useForm({
    about_description: props.about.about_description,
    about_whatsapp: props.about.about_whatsapp,
    about_email: props.about.about_email,
    about_company_name: props.about.about_company_name,
});
function saveAbout() {
    aboutForm.patch(route('platform.settings.about'), { preserveScroll: true });
}

function logout() {
    useForm({}).post(route('logout'));
}
</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">الإعدادات</h1>

        <div class="flex flex-col gap-6 md:flex-row">
            <div class="flex gap-2 overflow-x-auto md:w-48 md:flex-col">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    type="button"
                    class="whitespace-nowrap rounded-pill border border-transparent px-4 py-2.5 text-sm font-semibold transition-[border-color]"
                    :class="tab === t.key ? 'bg-brand-600 text-white' : 'hover:border-brand-400'"
                    :style="tab === t.key ? '' : 'color: var(--text-primary)'"
                    @click="tab = t.key"
                >
                    {{ t.label }}
                </button>
            </div>

            <div class="flex-1 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <div v-if="tab === 'account'" class="space-y-8">
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
                        <button type="submit" :disabled="accountForm.processing" class="rounded-pill bg-brand-600 px-6 py-2 font-bold text-white">حفظ</button>
                    </form>

                    <form class="space-y-4 border-t pt-6" style="border-color: var(--border-subtle)" @submit.prevent="savePassword">
                        <h2 class="font-bold">تغيير كلمة المرور</h2>
                        <input v-model="passwordForm.current_password" type="password" placeholder="كلمة المرور الحالية" autocomplete="current-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                        <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                        <input v-model="passwordForm.password" type="password" placeholder="كلمة المرور الجديدة" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                        <input v-model="passwordForm.password_confirmation" type="password" placeholder="تأكيد كلمة المرور" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                        <button type="submit" :disabled="passwordForm.processing" class="rounded-pill bg-brand-600 px-6 py-2 font-bold text-white">تغيير</button>
                    </form>

                    <div class="border-t pt-6" style="border-color: var(--border-subtle)">
                        <button type="button" class="rounded-pill border-2 border-red-500 px-6 py-2 font-bold text-red-600" @click="logout">تسجيل الخروج</button>
                    </div>
                </div>

                <form v-else-if="tab === 'general'" class="space-y-4" @submit.prevent="saveGeneral">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">مدة التجربة المجانية الافتراضية (بالأيام)</label>
                        <input
                            :value="generalForm.trial_days"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.trial_days = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                        <p class="mt-1 text-xs" style="color: var(--text-secondary)">يُطبَّق على الحسابات الجديدة فقط، لا يُعاد حسابه على حسابات قائمة.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">كود الدولة الدولي (لروابط واتساب)</label>
                        <input
                            :value="generalForm.country_code"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.country_code = $event.target.value.replace(/[^0-9]/g, '').slice(0, 4)"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">عدد الصفوف بكل صفحة</label>
                        <input
                            :value="generalForm.rows_per_page"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="w-full rounded-xl border px-4 py-2.5 text-end"
                            style="border-color: var(--border-subtle)"
                            @input="generalForm.rows_per_page = $event.target.value.replace(/[^0-9]/g, '').slice(0, 3)"
                        />
                    </div>
                    <button type="submit" :disabled="generalForm.processing" class="rounded-pill bg-brand-600 px-6 py-2 font-bold text-white">حفظ</button>
                </form>

                <form v-else-if="tab === 'about'" class="space-y-4" @submit.prevent="saveAbout">
                    <p class="text-sm" style="color: var(--text-secondary)">هذا المحتوى يظهر لكل المشتركين بتبويب "حول" في تطبيقهم.</p>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">شرح التطبيق</label>
                        <textarea v-model="aboutForm.about_description" rows="5" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                        <p v-if="aboutForm.errors.about_description" class="mt-1 text-sm text-red-600">{{ aboutForm.errors.about_description }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">رقم واتساب للتواصل</label>
                        <input v-model="aboutForm.about_whatsapp" type="text" dir="ltr" class="w-full rounded-xl border px-4 py-2.5 text-end" style="border-color: var(--border-subtle)" />
                        <p v-if="aboutForm.errors.about_whatsapp" class="mt-1 text-sm text-red-600">{{ aboutForm.errors.about_whatsapp }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">البريد الإلكتروني للتواصل</label>
                        <input v-model="aboutForm.about_email" type="email" dir="ltr" class="w-full rounded-xl border px-4 py-2.5 text-end" style="border-color: var(--border-subtle)" />
                        <p v-if="aboutForm.errors.about_email" class="mt-1 text-sm text-red-600">{{ aboutForm.errors.about_email }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">اسم الشركة المطوّرة</label>
                        <input v-model="aboutForm.about_company_name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                        <p v-if="aboutForm.errors.about_company_name" class="mt-1 text-sm text-red-600">{{ aboutForm.errors.about_company_name }}</p>
                    </div>
                    <button type="submit" :disabled="aboutForm.processing" class="rounded-pill bg-brand-600 px-6 py-2 font-bold text-white">حفظ</button>
                </form>
            </div>
        </div>
    </PlatformLayout>
</template>
