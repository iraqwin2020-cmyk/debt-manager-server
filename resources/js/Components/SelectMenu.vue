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
const triggerRef = ref(null);
const menuStyle = ref({});

const selectedLabel = computed(() => {
    const match = props.options.find((o) => String(o.value) === String(props.modelValue));
    return match ? match.label : '';
});

function toggle() {
    if (open.value) {
        open.value = false;
        return;
    }

    const rect = triggerRef.value.getBoundingClientRect();
    const menuWidth = props.full ? rect.width : Math.max(rect.width, 160);
    const menuHeight = 256;

    let left = rect.left;
    left = Math.min(left, window.innerWidth - menuWidth - 8);
    left = Math.max(left, 8);

    let top = rect.bottom + 4;
    if (top + menuHeight > window.innerHeight - 8) {
        top = Math.max(8, rect.top - menuHeight - 4);
    }

    menuStyle.value = { top: `${top}px`, left: `${left}px`, width: `${menuWidth}px` };
    open.value = true;
}

function select(option) {
    emit('update:modelValue', option.value);
    emit('change', option.value);
    open.value = false;
}
</script>

<template>
    <span class="relative inline-block" :class="full ? 'block w-full' : ''">
        <button
            ref="triggerRef"
            type="button"
            class="flex items-center gap-2 border font-semibold"
            :class="full ? 'w-full justify-between rounded-xl px-4 py-2.5' : 'rounded-pill px-4 py-2 text-sm'"
            style="border-color: var(--border-subtle); background: var(--surface-card); color: var(--text-primary)"
            @click="toggle"
        >
            <span>{{ selectedLabel }}</span>
            <span class="text-xs transition-transform" :style="open ? 'transform: rotate(180deg)' : ''"><Icon name="chevron-down" /></span>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                class="fixed z-50 max-h-64 overflow-y-auto overflow-x-hidden rounded-xl border shadow-lg"
                :style="{ ...menuStyle, background: 'var(--surface-solid)', borderColor: 'var(--border-subtle)' }"
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
            <div v-if="open" class="fixed inset-0 z-40" @click="open = false"></div>
        </Teleport>
    </span>
</template>
