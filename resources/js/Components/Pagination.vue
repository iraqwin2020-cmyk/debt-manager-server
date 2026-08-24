<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ links: { type: Array, required: true } });
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-center gap-1 pt-4">
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="link.url === null"
                class="rounded-pill px-3 py-1.5 text-sm"
                style="color: var(--text-secondary)"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                preserve-state
                class="rounded-pill border border-transparent px-3 py-1.5 text-sm font-semibold transition"
                :class="link.active ? 'bg-brand-600 text-white' : 'hover:border-brand-400'"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
