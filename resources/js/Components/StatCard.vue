<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: 'chart' },
    href: { type: String, default: null },
    dark: { type: Boolean, default: false },
    tall: { type: Boolean, default: false },
});
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href"
        class="flex items-center gap-2 overflow-hidden rounded-card border transition sm:gap-4"
        :class="[href ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow-lg' : '', tall || dark ? 'h-28 px-3 sm:h-36 sm:px-5' : 'p-3 sm:p-5']"
        :style="dark
            ? 'background: var(--color-ink); box-shadow: var(--shadow-card); border-color: var(--color-ink)'
            : 'background: var(--surface-card); box-shadow: var(--shadow-card); border-color: var(--border-subtle)'"
    >
        <span
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-base sm:h-14 sm:w-14 sm:rounded-2xl sm:text-2xl"
            style="background: linear-gradient(135deg, var(--color-brand-400), var(--color-brand-600)); color: #fff; box-shadow: 0 8px 18px -4px rgb(232 96 12 / 0.45)"
        >
            <slot name="icon"><Icon :name="icon" /></slot>
        </span>
        <div class="min-w-0">
            <p class="truncate text-xs font-medium sm:text-sm" :style="dark ? 'color: var(--color-paper); opacity: 0.7' : 'color: var(--text-secondary)'">{{ label }}</p>
            <div class="text-base font-extrabold leading-tight sm:text-2xl" :style="dark ? 'color: var(--color-paper)' : 'color: var(--text-primary)'">
                <slot>{{ value }}</slot>
            </div>
        </div>
    </component>
</template>
