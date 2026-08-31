<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: String, default: null },
});

const parsed = computed(() => {
    if (!props.value) return null;

    const [datePart, timePart] = props.value.split(' ');
    const [year, month, day] = datePart.split('-');
    if (!year || !month || !day) return null;

    return { day, month, year, time: timePart ? timePart.slice(0, 5) : null };
});
</script>

<template>
    <span v-if="!parsed">—</span>
    <span v-else class="inline-flex items-center gap-1">
        <bdi class="bdi-date" dir="rtl">{{ parsed.day }} {{ parsed.month }} {{ parsed.year }}</bdi>
        <bdi v-if="parsed.time" class="bdi-ltr">{{ parsed.time }}</bdi>
    </span>
</template>
