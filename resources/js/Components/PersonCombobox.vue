<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: { type: String, required: true }, // debtor | guarantor | creditor
    label: { type: String, required: true },
    modelValue: { type: Object, default: () => ({ id: null, name: '', phone: '', address: '', note: '' }) },
});
const emit = defineEmits(['update:modelValue']);

const query = ref(props.modelValue?.name ?? '');
const results = ref([]);
const searched = ref(false);
const showNewFields = ref(false);
let timer = null;

watch(query, (value) => {
    if (props.modelValue.id) return; // مقفول بشخص مختار مسبقاً
    clearTimeout(timer);
    if (!value) {
        results.value = [];
        searched.value = false;
        showNewFields.value = false;
        return;
    }
    timer = setTimeout(async () => {
        const url = route('app.search.people', { type: props.type, q: value });
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        results.value = data;
        searched.value = true;
        showNewFields.value = data.length === 0;
        if (showNewFields.value) {
            emit('update:modelValue', { id: null, name: value, phone: '', address: '', note: '' });
        }
    }, 300);
});

function select(person) {
    query.value = person.name;
    results.value = [];
    showNewFields.value = false;
    emit('update:modelValue', { id: person.id, name: person.name, phone: person.phone, address: '', note: '' });
}

function clearSelection() {
    emit('update:modelValue', { id: null, name: '', phone: '', address: '', note: '' });
    query.value = '';
    results.value = [];
    showNewFields.value = false;
    searched.value = false;
}

function updateField(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}
</script>

<template>
    <div class="space-y-2">
        <label class="text-sm font-semibold" style="color: var(--text-primary)">{{ label }}</label>

        <div v-if="modelValue.id" class="flex items-center justify-between rounded-pill px-4 py-2" style="background: var(--status-success-bg); color: var(--status-success-text)">
            <span class="font-semibold">{{ modelValue.name }} — {{ modelValue.phone }}</span>
            <button type="button" class="text-xs font-bold underline" @click="clearSelection">تغيير</button>
        </div>

        <template v-else>
            <input
                v-model="query"
                type="text"
                class="w-full rounded-xl border px-4 py-2.5"
                style="border-color: var(--border-subtle); background: var(--surface-card)"
                placeholder="اكتب الاسم أو رقم الهاتف..."
            />

            <div v-if="results.length" class="overflow-hidden rounded-xl border" style="border-color: var(--border-subtle)">
                <button
                    v-for="person in results"
                    :key="person.id"
                    type="button"
                    class="block w-full px-4 py-2.5 text-start text-sm text-[var(--text-primary)] transition hover:bg-brand-600 hover:text-white"
                    @click="select(person)"
                >
                    {{ person.name }} — <bdi class="bdi-ltr">{{ person.phone }}</bdi>
                </button>
            </div>

            <div v-if="showNewFields" class="space-y-2 rounded-xl border p-3" style="border-color: var(--border-subtle)">
                <p class="text-xs font-semibold" style="color: var(--text-secondary)">لا يوجد تطابق — إدخال بيانات جديدة:</p>
                <input
                    :value="modelValue.name"
                    type="text"
                    placeholder="الاسم"
                    class="w-full rounded-lg border px-3 py-2 text-sm"
                    style="border-color: var(--border-subtle)"
                    @input="updateField('name', $event.target.value)"
                />
                <input
                    :value="modelValue.phone"
                    type="text"
                    inputmode="numeric"
                    maxlength="11"
                    dir="ltr"
                    placeholder="رقم الهاتف (11 رقم)"
                    class="w-full rounded-lg border px-3 py-2 text-sm"
                    style="border-color: var(--border-subtle)"
                    @input="updateField('phone', $event.target.value.replace(/[^0-9]/g, '').slice(0, 11))"
                />
                <input
                    :value="modelValue.address"
                    type="text"
                    placeholder="العنوان (اختياري)"
                    class="w-full rounded-lg border px-3 py-2 text-sm"
                    style="border-color: var(--border-subtle)"
                    @input="updateField('address', $event.target.value)"
                />
            </div>
        </template>
    </div>
</template>
