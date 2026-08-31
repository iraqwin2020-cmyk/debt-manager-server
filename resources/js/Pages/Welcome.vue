<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import FlashToast from '@/Components/FlashToast.vue';

const { t } = useI18n();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-8 px-6 text-center" style="background: var(--surface-page)">
        <FlashToast />
        <LanguageSwitcher />

        <div>
            <h1 class="text-4xl font-extrabold text-brand-700">{{ t('auth.welcome.appName') }}</h1>
            <p class="mt-3 max-w-md text-lg" style="color: var(--text-secondary)">
                {{ t('auth.welcome.tagline') }}
            </p>
        </div>

        <div class="flex gap-3">
            <Link :href="route('register')" class="rounded-pill bg-brand-600 px-8 py-3 font-bold text-white shadow-lg transition hover:bg-brand-700">
                {{ t('auth.welcome.startTrial') }}
            </Link>
            <Link :href="route('login')" class="rounded-pill border-2 border-brand-600 px-8 py-3 font-bold text-brand-700 transition hover:bg-brand-50">
                {{ t('auth.login.title') }}
            </Link>
        </div>

        <Link :href="route('privacy-policy')" class="text-sm font-semibold hover:underline" style="color: var(--text-secondary)">
            {{ t('auth.welcome.privacyPolicy') }}
        </Link>
    </div>
</template>
