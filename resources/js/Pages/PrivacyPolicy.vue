<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();

defineProps({
    content: { type: String, default: '' },
});
</script>

<template>
    <div class="min-h-screen px-6 py-10" style="background: var(--surface-page)">
        <div class="mx-auto max-w-2xl">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('home')" class="text-sm font-semibold text-brand-700 hover:underline">{{ t('auth.welcome.appName') }}</Link>
                <LanguageSwitcher />
            </div>

            <div class="rounded-card p-6 sm:p-8" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
                <h1 class="mb-4 text-lg font-extrabold sm:text-2xl">{{ t('auth.welcome.privacyPolicy') }}</h1>
                <p v-if="content" class="whitespace-pre-wrap text-sm leading-relaxed">{{ content }}</p>
                <p v-else class="text-sm" style="color: var(--text-secondary)">{{ t('privacyPolicy.empty') }}</p>
            </div>
        </div>
    </div>
</template>
