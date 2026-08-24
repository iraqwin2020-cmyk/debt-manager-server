<script setup>
import { computed, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    modelValue: { type: String, default: '' }, // YYYY-MM-DD
    placeholder: { type: String, default: 'اختر تاريخاً' },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const weekDays = ['سب', 'أح', 'إث', 'ثل', 'أر', 'خم', 'جم'];

function parse(value) {
    if (!value) return null;
    const [y, m, d] = value.split('-').map(Number);
    return { y, m, d };
}

function pad(n) {
    return String(n).padStart(2, '0');
}

const selected = computed(() => parse(props.modelValue));

const view = ref(
    selected.value
        ? { y: selected.value.y, m: selected.value.m }
        : { y: new Date().getFullYear(), m: new Date().getMonth() + 1 }
);

watch(open, (isOpen) => {
    if (isOpen) {
        view.value = selected.value
            ? { y: selected.value.y, m: selected.value.m }
            : { y: new Date().getFullYear(), m: new Date().getMonth() + 1 };
    }
});

const monthLabel = computed(() =>
    new Intl.DateTimeFormat('ar', { month: 'long', year: 'numeric' }).format(new Date(view.value.y, view.value.m - 1, 1))
);

const displayText = computed(() => {
    if (!selected.value) return '';
    return `${pad(selected.value.d)} ${pad(selected.value.m)} ${selected.value.y}`;
});

const today = new Date();
const todayY = today.getFullYear();
const todayM = today.getMonth() + 1;
const todayD = today.getDate();

const grid = computed(() => {
    const firstOfMonth = new Date(view.value.y, view.value.m - 1, 1);
    // JS getDay(): 0=Sun..6=Sat. Week starts Saturday (6) for our layout.
    const startOffset = (firstOfMonth.getDay() + 1) % 7;
    const gridStart = new Date(view.value.y, view.value.m - 1, 1 - startOffset);

    const cells = [];
    for (let i = 0; i < 42; i++) {
        const d = new Date(gridStart);
        d.setDate(gridStart.getDate() + i);
        cells.push({
            y: d.getFullYear(),
            m: d.getMonth() + 1,
            d: d.getDate(),
            inMonth: d.getMonth() + 1 === view.value.m,
            isToday: d.getFullYear() === todayY && d.getMonth() + 1 === todayM && d.getDate() === todayD,
            isSelected: !!selected.value && d.getFullYear() === selected.value.y && d.getMonth() + 1 === selected.value.m && d.getDate() === selected.value.d,
        });
    }
    return cells;
});

function prevMonth() {
    view.value = view.value.m === 1 ? { y: view.value.y - 1, m: 12 } : { y: view.value.y, m: view.value.m - 1 };
}
function nextMonth() {
    view.value = view.value.m === 12 ? { y: view.value.y + 1, m: 1 } : { y: view.value.y, m: view.value.m + 1 };
}

function pick(cell) {
    emit('update:modelValue', `${cell.y}-${pad(cell.m)}-${pad(cell.d)}`);
    open.value = false;
}

function pickToday() {
    emit('update:modelValue', `${todayY}-${pad(todayM)}-${pad(todayD)}`);
    open.value = false;
}

function clear() {
    emit('update:modelValue', '');
    open.value = false;
}
</script>

<template>
    <span class="relative block">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded-xl border px-4 py-2.5 text-sm"
            style="border-color: var(--border-subtle); background: var(--surface-card); color: var(--text-primary)"
            @click="open = !open"
        >
            <bdi v-if="selected" class="bdi-date font-semibold" dir="rtl">{{ displayText }}</bdi>
            <span v-else style="color: var(--text-secondary)">{{ placeholder }}</span>
            <Icon name="calendar" class="shrink-0" style="color: var(--text-secondary)" />
        </button>

        <div
            v-if="open"
            class="absolute z-20 mt-1 w-72 rounded-2xl border p-3 shadow-lg"
            style="background: var(--surface-card); border-color: var(--border-subtle)"
        >
            <div class="mb-2 flex items-center justify-between">
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full border border-transparent transition-[border-color] hover:border-brand-400" @click="prevMonth">
                    <Icon name="back" style="transform: scaleX(-1); color: var(--text-primary)" />
                </button>
                <span class="text-sm font-bold" style="color: var(--text-primary)">{{ monthLabel }}</span>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full border border-transparent transition-[border-color] hover:border-brand-400" @click="nextMonth">
                    <Icon name="back" style="color: var(--text-primary)" />
                </button>
            </div>

            <div class="mb-1 grid grid-cols-7 text-center text-xs font-semibold" style="color: var(--text-secondary)">
                <span v-for="wd in weekDays" :key="wd">{{ wd }}</span>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <button
                    v-for="(cell, i) in grid"
                    :key="i"
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm transition"
                    :class="!cell.inMonth ? 'opacity-35' : ''"
                    :style="cell.isSelected
                        ? 'background: var(--color-brand-600); color: #fff; font-weight: 700'
                        : cell.isToday
                            ? 'border: 1.5px solid var(--color-brand-500); color: var(--text-primary)'
                            : 'color: var(--text-primary)'"
                    @click="pick(cell)"
                >
                    {{ cell.d }}
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between border-t pt-2 text-sm font-semibold" style="border-color: var(--border-subtle)">
                <button type="button" class="text-red-600 hover:underline" @click="clear">مسح</button>
                <button type="button" class="text-brand-700 hover:underline" @click="pickToday">اليوم</button>
            </div>
        </div>
        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>
    </span>
</template>
