<script setup>
defineProps({
    currency: { type: String, required: true },
    amount: { type: [Number, String], required: true },
});
</script>

<template>
    <span class="inline-flex items-center gap-1" dir="ltr">
        <span>{{ currency === 'USD' ? '$' : 'د.ع' }}</span>
        <span>{{ new Intl.NumberFormat('en-US').format(amount) }}</span>
    </span>
</template>
