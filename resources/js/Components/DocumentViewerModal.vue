<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    show: { type: Boolean, default: false },
    hasDocument: { type: Boolean, default: false },
    imageUrl: { type: String, default: '' },
    title: { type: String, default: 'الوثائق' },
});
defineEmits(['close']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-auto rounded-2xl p-4 shadow-lg" style="background: var(--surface-card)">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-bold" style="color: var(--text-primary)">{{ title }}</h3>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full border border-transparent transition-[border-color] hover:border-brand-400" style="color: var(--text-primary)" @click="$emit('close')">
                    <Icon name="close" />
                </button>
            </div>
            <img v-if="hasDocument" :src="imageUrl" alt="" class="w-full rounded-xl" />
            <p v-else class="py-10 text-center text-sm" style="color: var(--text-secondary)">لا توجد وثيقة مرفوعة بعد.</p>
        </div>
    </div>
</template>
