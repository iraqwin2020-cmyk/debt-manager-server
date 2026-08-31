<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/Components/Icon.vue';

const { t } = useI18n();

const props = defineProps({
    type: { type: String, required: true }, // debtor | guarantor | creditor
    label: { type: String, required: true },
    modelValue: { type: Object, default: () => ({ id: null, name: '', phone: '', address: '', note: '', new_images: [] }) },
});
const emit = defineEmits(['update:modelValue']);

const query = ref(props.modelValue?.name ?? '');
const results = ref([]);
const searched = ref(false);
const showNewFields = ref(false);
const previews = ref([]);
const fileInput = ref(null);
let timer = null;

const supportsDocuments = props.type !== 'creditor';

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
            emit('update:modelValue', { id: null, name: value, phone: '', address: '', note: '', new_images: [] });
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
    emit('update:modelValue', { id: null, name: '', phone: '', address: '', note: '', new_images: [] });
    query.value = '';
    results.value = [];
    showNewFields.value = false;
    searched.value = false;
    previews.value = [];
}

function updateField(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function addFiles(event) {
    const currentImages = props.modelValue.new_images ?? [];
    const files = [...event.target.files].slice(0, 5 - currentImages.length);
    previews.value.push(...files.map((f) => URL.createObjectURL(f)));
    emit('update:modelValue', { ...props.modelValue, new_images: [...currentImages, ...files] });
    event.target.value = '';
}

function removeImage(index) {
    URL.revokeObjectURL(previews.value[index]);
    previews.value.splice(index, 1);
    const currentImages = [...(props.modelValue.new_images ?? [])];
    currentImages.splice(index, 1);
    emit('update:modelValue', { ...props.modelValue, new_images: currentImages });
}
</script>

<template>
    <div class="space-y-2">
        <label class="text-sm font-semibold" style="color: var(--text-primary)">{{ label }}</label>

        <div v-if="modelValue.id" class="flex items-center justify-between rounded-pill px-4 py-2" style="background: var(--status-success-bg); color: var(--status-success-text)">
            <span class="font-semibold">{{ modelValue.name }} — {{ modelValue.phone }}</span>
            <button type="button" class="text-xs font-bold underline" @click="clearSelection">{{ t('common.change') }}</button>
        </div>

        <template v-else>
            <input
                v-model="query"
                type="text"
                class="w-full rounded-xl border px-4 py-2.5"
                style="border-color: var(--border-subtle); background: var(--surface-card)"
                :placeholder="t('personCombobox.searchPlaceholder')"
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
                <p class="text-xs font-semibold" style="color: var(--text-secondary)">{{ t('personCombobox.noMatchLabel') }}</p>
                <input
                    :value="modelValue.name"
                    type="text"
                    :placeholder="t('common.name')"
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
                    :placeholder="t('personCombobox.phoneHint')"
                    class="w-full rounded-lg border px-3 py-2 text-sm"
                    style="border-color: var(--border-subtle)"
                    @input="updateField('phone', $event.target.value.replace(/[^0-9]/g, '').slice(0, 11))"
                />
                <input
                    :value="modelValue.address"
                    type="text"
                    :placeholder="t('personCombobox.addressOptional')"
                    class="w-full rounded-lg border px-3 py-2 text-sm"
                    style="border-color: var(--border-subtle)"
                    @input="updateField('address', $event.target.value)"
                />
                <div v-if="supportsDocuments">
                    <div v-if="previews.length" class="mb-2 flex flex-wrap gap-2">
                        <div v-for="(src, i) in previews" :key="i" class="relative">
                            <img :src="src" alt="" class="h-14 w-14 rounded-lg object-cover" />
                            <button type="button" class="absolute -top-1.5 -left-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white" @click="removeImage(i)">
                                <Icon name="close" />
                            </button>
                        </div>
                    </div>
                    <div v-if="previews.length < 5" class="flex items-center gap-3">
                        <button type="button" class="rounded-pill border-2 border-brand-600 px-3 py-1 text-xs font-bold text-brand-700" @click="fileInput.click()">{{ t('common.addDocuments') }}</button>
                        <span class="text-xs" style="color: var(--text-secondary)">{{ previews.length ? t('common.imagesChosen', { count: previews.length }) : t('common.noFileChosen') }}</span>
                        <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="addFiles" />
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
