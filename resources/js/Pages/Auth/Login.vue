<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import FlashToast from '@/Components/FlashToast.vue';

const { t } = useI18n();

const props = defineProps({ rememberedPhone: { type: String, default: null } });

const form = useForm({
    phone: props.rememberedPhone ?? '',
    password: '',
    remember: true,
});

function submit() {
    form.post(route('login'), { onFinish: () => form.reset('password') });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center px-4" style="background: var(--surface-page)">
        <FlashToast />
        <form
            class="w-full max-w-sm space-y-5 rounded-card p-8"
            style="background: var(--surface-card); box-shadow: var(--shadow-card)"
            @submit.prevent="submit"
        >
            <LanguageSwitcher />

            <h1 class="text-center text-lg font-extrabold text-brand-700 sm:text-2xl">{{ t('auth.login.title') }}</h1>

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
                    autocomplete="current-password"
                    class="w-full rounded-xl border px-4 py-2.5"
                    style="border-color: var(--border-subtle)"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded accent-brand-600" />
                {{ t('auth.login.rememberMe') }}
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-pill bg-brand-600 py-3 font-bold text-white transition hover:bg-brand-700 disabled:opacity-50"
            >
                {{ t('auth.login.submit') }}
            </button>

            <p class="text-center text-sm" style="color: var(--text-secondary)">
                {{ t('auth.login.noAccount') }}
                <Link :href="route('register')" class="font-semibold text-brand-700 hover:underline">{{ t('auth.login.registerNow') }}</Link>
            </p>
        </form>
    </div>
</template>
