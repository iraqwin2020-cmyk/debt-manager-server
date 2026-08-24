<script setup>
defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'تأكيد' },
    message: { type: String, required: true },
    confirmLabel: { type: String, default: 'متابعة' },
    cancelLabel: { type: String, default: 'إلغاء' },
    danger: { type: Boolean, default: false },
});
const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="emit('cancel')"></div>
        <div class="relative w-full max-w-sm rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <h2 class="mb-2 text-lg font-extrabold">{{ title }}</h2>
            <p class="mb-6 text-sm" style="color: var(--text-secondary)">{{ message }}</p>
            <div class="flex justify-end gap-3">
                <button type="button" class="rounded-pill border-2 px-5 py-2 text-sm font-bold" style="border-color: var(--border-subtle)" @click="emit('cancel')">
                    {{ cancelLabel }}
                </button>
                <button
                    type="button"
                    class="rounded-pill px-5 py-2 text-sm font-bold text-white"
                    :class="danger ? 'bg-red-600' : 'bg-brand-600'"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
