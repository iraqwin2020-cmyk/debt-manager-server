<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { RTL_LOCALES } from '@/i18n';
import Icon from '@/Components/Icon.vue';

const { t, locale } = useI18n();
const isRtl = computed(() => RTL_LOCALES.includes(locale.value));

const props = defineProps({ paginator: { type: Object, required: true } });

const links = computed(() => props.paginator.links);
const prevLink = computed(() => links.value[0]);
const nextLink = computed(() => links.value[links.value.length - 1]);
const currentPage = computed(() => props.paginator.current_page);
const lastPage = computed(() => props.paginator.last_page);
const firstPageUrl = computed(() => props.paginator.first_page_url);
const lastPageUrl = computed(() => props.paginator.last_page_url);
</script>

<template>
    <nav v-if="lastPage > 1" class="flex items-center justify-center gap-2 pt-4">
        <component
            :is="currentPage > 1 ? Link : 'span'"
            :href="currentPage > 1 ? firstPageUrl : undefined"
            preserve-scroll
            preserve-state
            :title="t('pagination.first')"
            class="flex h-9 w-9 items-center justify-center rounded-full border transition"
            :class="currentPage > 1 ? 'hover:border-brand-400' : 'opacity-30'"
            style="border-color: var(--border-subtle); color: var(--text-primary)"
        >
            <Icon name="skip" :style="isRtl ? 'transform: scaleX(-1)' : ''" />
        </component>

        <component
            :is="prevLink.url ? Link : 'span'"
            :href="prevLink.url ?? undefined"
            preserve-scroll
            preserve-state
            :title="t('pagination.previous')"
            class="flex h-9 w-9 items-center justify-center rounded-full border transition"
            :class="prevLink.url ? 'hover:border-brand-400' : 'opacity-30'"
            style="border-color: var(--border-subtle); color: var(--text-primary)"
        >
            <Icon name="back" :style="isRtl ? 'transform: scaleX(-1)' : ''" />
        </component>

        <span class="min-w-20 text-center text-sm font-bold" style="color: var(--text-primary)">{{ t('pagination.pageOf', { current: currentPage, last: lastPage }) }}</span>

        <component
            :is="nextLink.url ? Link : 'span'"
            :href="nextLink.url ?? undefined"
            preserve-scroll
            preserve-state
            :title="t('pagination.next')"
            class="flex h-9 w-9 items-center justify-center rounded-full border transition"
            :class="nextLink.url ? 'hover:border-brand-400' : 'opacity-30'"
            style="border-color: var(--border-subtle); color: var(--text-primary)"
        >
            <Icon name="back" :style="isRtl ? '' : 'transform: scaleX(-1)'" />
        </component>

        <component
            :is="currentPage < lastPage ? Link : 'span'"
            :href="currentPage < lastPage ? lastPageUrl : undefined"
            preserve-scroll
            preserve-state
            :title="t('pagination.last')"
            class="flex h-9 w-9 items-center justify-center rounded-full border transition"
            :class="currentPage < lastPage ? 'hover:border-brand-400' : 'opacity-30'"
            style="border-color: var(--border-subtle); color: var(--text-primary)"
        >
            <Icon name="skip" :style="isRtl ? '' : 'transform: scaleX(-1)'" />
        </component>
    </nav>
</template>
