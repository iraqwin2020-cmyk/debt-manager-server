<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t, locale } = useI18n();

const props = defineProps({
    content: { type: Object, default: () => ({ ar: '', en: '', ku: '' }) },
});

const currentContent = computed(() => props.content[locale.value] || props.content.ar || '');
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
                <p v-if="currentContent" class="whitespace-pre-wrap text-sm leading-relaxed">{{ currentContent }}</p>
                <p v-else class="text-sm" style="color: var(--text-secondary)">{{ t('privacyPolicy.empty') }}</p>
            </div>
        </div>
    </div>
</template>
