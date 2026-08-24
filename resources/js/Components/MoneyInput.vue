<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    currency: { type: String, default: 'IQD' },
    placeholder: { type: String, default: '0' },
});
const emit = defineEmits(['update:modelValue']);

const unit = computed(() => (props.currency === 'USD' ? 50 : 1000));

function formatDisplay(value) {
    if (value === null || value === undefined || value === '') return '';
    return new Intl.NumberFormat('en-US').format(value);
}

const display = ref(formatDisplay(props.modelValue));

watch(
    () => props.modelValue,
    (v) => {
        display.value = formatDisplay(v);
    }
);

function onInput(e) {
    const raw = e.target.value.replace(/[^\d]/g, '');
    const num = raw === '' ? null : parseInt(raw, 10);
    display.value = raw === '' ? '' : new Intl.NumberFormat('en-US').format(num);
    emit('update:modelValue', num);
}

function onBlur() {
    if (props.modelValue === null) return;
    const rounded = Math.round(props.modelValue / unit.value) * unit.value;
    if (rounded !== props.modelValue) {
        emit('update:modelValue', rounded);
    }
}
</script>

<template>
    <div class="relative">
        <input
            type="text"
            inputmode="numeric"
            dir="ltr"
            class="w-full rounded-xl border ps-4 pe-10 py-2.5 text-end font-semibold"
            style="border-color: var(--border-subtle); background: var(--surface-card); color: var(--text-primary)"
            :placeholder="placeholder"
            :value="display"
            @input="onInput"
            @blur="onBlur"
        />
        <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-xs font-bold" style="color: var(--text-secondary)">
            {{ currency === 'USD' ? '$' : 'د.ع' }}
        </span>
    </div>
</template>
