<script setup>
import { computed, ref } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true }, // [{ value, label }]
    full: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'change']);

const open = ref(false);

const selectedLabel = computed(() => {
    const match = props.options.find((o) => String(o.value) === String(props.modelValue));
    return match ? match.label : '';
});

function select(option) {
    emit('update:modelValue', option.value);
    emit('change', option.value);
    open.value = false;
}
</script>

<template>
    <span class="relative inline-block" :class="full ? 'block w-full' : ''">
        <button
            type="button"
            class="flex items-center gap-2 rounded-pill border px-4 py-2 text-sm font-semibold"
            :class="full ? 'w-full justify-between' : ''"
            style="border-color: var(--border-subtle); background: var(--surface-card); color: var(--text-primary)"
            @click="open = !open"
        >
            <span>{{ selectedLabel }}</span>
            <span class="text-xs transition-transform" :style="open ? 'transform: rotate(180deg)' : ''"><Icon name="chevron-down" /></span>
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-1 max-h-64 w-full min-w-[10rem] overflow-y-auto overflow-x-hidden rounded-xl border shadow-lg"
            style="background: var(--surface-card); border-color: var(--border-subtle)"
        >
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                class="block w-full whitespace-nowrap px-4 py-2 text-start text-sm transition hover:bg-brand-600 hover:text-white"
                :class="String(option.value) === String(modelValue) ? 'bg-brand-600 font-bold text-white' : ''"
                :style="String(option.value) === String(modelValue) ? '' : 'color: var(--text-primary)'"
                @click="select(option)"
            >
                {{ option.label }}
            </button>
        </div>
        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>
    </span>
</template>
