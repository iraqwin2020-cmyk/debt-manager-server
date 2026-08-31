<script setup>
import { useI18n } from 'vue-i18n';
import { applyDirection } from '@/i18n';

const { locale } = useI18n();

const languages = [
    { value: 'ar', label: 'العربية' },
    { value: 'en', label: 'English' },
    { value: 'ku', label: 'کوردی' },
];

function change(value) {
    locale.value = value;
    applyDirection(value);
    localStorage.setItem('locale', value);
}
</script>

<template>
    <div class="flex justify-center gap-1.5">
        <button
            v-for="lang in languages"
            :key="lang.value"
            type="button"
            class="rounded-pill border-2 px-3 py-1 text-xs font-bold transition"
            :style="locale === lang.value
                ? 'border-color: var(--color-brand-600); background: var(--color-brand-600); color: #fff'
                : 'border-color: var(--border-subtle); color: var(--text-secondary)'"
            @click="change(lang.value)"
        >
            {{ lang.label }}
        </button>
    </div>
</template>
