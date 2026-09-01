<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PlatformLayout from '@/Layouts/PlatformLayout.vue';

const props = defineProps({
    settings: { type: Object, required: true },
    about: { type: Object, required: true },
    privacyPolicy: { type: String, default: '' },
});

const tab = ref('general');
const tabs = [
    { key: 'general', label: 'عام' },
    { key: 'about', label: 'حول' },
    { key: 'privacy', label: 'سياسة الخصوصية' },
];

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

const privacyForm = useForm({
    privacy_policy: props.privacyPolicy,
});
function savePrivacy() {
    privacyForm.patch(route('platform.settings.privacy-policy'), { preserveScroll: true });
}

</script>

<template>
    <PlatformLayout>
        <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">الإعدادات</h1>

        <div class="flex flex-col gap-6 md:flex-row">
            <div class="flex gap-1 overflow-x-auto md:w-48 md:flex-col md:gap-2">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    type="button"
                    class="min-w-0! whitespace-nowrap rounded-pill border border-transparent px-2.5 py-2.5 text-sm font-semibold transition-[border-color] md:px-4 md:min-w-24!"
                    :class="tab === t.key ? 'bg-brand-600 text-white' : 'hover:border-brand-400'"
                    :style="tab === t.key ? '' : 'color: var(--text-primary)'"
                    @click="tab = t.key"
                >
                    {{ t.label }}
                </button>
            </div>

            <div class="flex-1 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <form v-if="tab === 'general'" class="space-y-4" @submit.prevent="saveGeneral">
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
                    <button type="submit" :disabled="generalForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">حفظ</button>
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
                    <button type="submit" :disabled="aboutForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">حفظ</button>
                </form>

                <form v-else-if="tab === 'privacy'" class="space-y-4" @submit.prevent="savePrivacy">
                    <p class="text-sm" style="color: var(--text-secondary)">
                        هذا النص يظهر في صفحة سياسة الخصوصية العامة
                        (<a :href="route('privacy-policy')" target="_blank" rel="noopener" class="font-semibold text-brand-700 hover:underline">عرض الصفحة</a>)
                        وهي متاحة بدون تسجيل دخول.
                    </p>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">نص سياسة الخصوصية</label>
                        <textarea v-model="privacyForm.privacy_policy" rows="16" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                        <p v-if="privacyForm.errors.privacy_policy" class="mt-1 text-sm text-red-600">{{ privacyForm.errors.privacy_policy }}</p>
                    </div>
                    <button type="submit" :disabled="privacyForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">حفظ</button>
                </form>
            </div>
        </div>
    </PlatformLayout>
</template>
