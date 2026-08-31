<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();

const form = useForm({
    office_name: '',
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    agree_privacy: false,
});

function submit() {
    form.post(route('register'));
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center px-4 py-10" style="background: var(--surface-page)">
        <form
            class="w-full max-w-sm space-y-5 rounded-card p-8"
            style="background: var(--surface-card); box-shadow: var(--shadow-card)"
            @submit.prevent="submit"
        >
            <LanguageSwitcher />

            <h1 class="text-center text-lg font-extrabold text-brand-700 sm:text-2xl">{{ t('auth.register.title') }}</h1>
            <p class="text-center text-sm" style="color: var(--text-secondary)">{{ t('auth.register.subtitle') }}</p>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ t('account.officeName') }}</label>
                <input v-model="form.office_name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <p v-if="form.errors.office_name" class="mt-1 text-sm text-red-600">{{ form.errors.office_name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ t('auth.register.yourName') }}</label>
                <input v-model="form.name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ t('common.phone') }}</label>
                <input
                    v-model="form.phone"
                    type="text"
                    inputmode="numeric"
                    maxlength="11"
                    dir="ltr"
                    autocomplete="username"
                    class="w-full rounded-xl border px-4 py-2.5"
                    style="border-color: var(--border-subtle)"
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ t('auth.password') }}</label>
                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-xl border px-4 py-2.5"
                    style="border-color: var(--border-subtle)"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold">{{ t('account.confirmPassword') }}</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-xl border px-4 py-2.5"
                    style="border-color: var(--border-subtle)"
                />
            </div>

            <div>
                <label class="flex items-start gap-2 text-sm" style="color: var(--text-secondary)">
                    <input v-model="form.agree_privacy" type="checkbox" class="mt-1 shrink-0" />
                    <span>
                        {{ t('auth.register.agreePrivacyPrefix') }}
                        <a :href="route('privacy-policy')" target="_blank" rel="noopener" class="font-semibold text-brand-700 hover:underline">{{ t('auth.welcome.privacyPolicy') }}</a>
                    </span>
                </label>
                <p v-if="form.errors.agree_privacy" class="mt-1 text-sm text-red-600">{{ form.errors.agree_privacy }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing || !form.agree_privacy"
                class="w-full rounded-pill bg-brand-600 py-3 font-bold text-white transition hover:bg-brand-700 disabled:opacity-50"
            >
                {{ t('auth.register.submit') }}
            </button>

            <p class="text-center text-sm" style="color: var(--text-secondary)">
                {{ t('auth.register.haveAccount') }}
                <Link :href="route('login')" class="font-semibold text-brand-700 hover:underline">{{ t('auth.register.loginNow') }}</Link>
            </p>
        </form>
    </div>
</template>
