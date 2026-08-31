<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';

const { t } = useI18n();

const form = useForm({
    name: '',
    phone: '',
    address: '',
    note: '',
    new_images: [],
});

const previews = ref([]);
const fileInput = ref(null);

function addFiles(event) {
    const files = [...event.target.files].slice(0, 5 - form.new_images.length);
    form.new_images.push(...files);
    previews.value.push(...files.map((f) => URL.createObjectURL(f)));
    event.target.value = '';
}

function removeImage(index) {
    URL.revokeObjectURL(previews.value[index]);
    form.new_images.splice(index, 1);
    previews.value.splice(index, 1);
}

function submit() {
    form.post(route('app.debtors.store'));
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">{{ t('debtors.addNew') }}</h1>

            <form class="space-y-4 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('common.name') }}</label>
                    <input v-model="form.name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('common.phone') }}</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        inputmode="numeric"
                        maxlength="11"
                        dir="ltr"
                        class="w-full rounded-xl border px-4 py-2.5"
                        style="border-color: var(--border-subtle)"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debtors.addressOptional') }}</label>
                    <input v-model="form.address" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debtors.docsOptional') }}</label>
                    <div v-if="previews.length" class="mb-2 flex flex-wrap gap-2">
                        <div v-for="(src, i) in previews" :key="i" class="relative">
                            <img :src="src" alt="" class="h-16 w-16 rounded-lg object-cover" />
                            <button type="button" class="absolute -top-1.5 -left-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white" @click="removeImage(i)">
                                <Icon name="close" />
                            </button>
                        </div>
                    </div>
                    <div v-if="form.new_images.length < 5" class="flex items-center gap-3">
                        <button type="button" class="rounded-pill border-2 border-brand-600 px-4 py-1.5 text-sm font-bold text-brand-700" @click="fileInput.click()">{{ t('common.chooseImages') }}</button>
                        <span class="text-xs" style="color: var(--text-secondary)">{{ previews.length ? t('common.imagesChosen', { count: previews.length }) : t('common.noFileChosen') }}</span>
                        <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="addFiles" />
                    </div>
                    <p v-if="form.errors.new_images" class="mt-1 text-sm text-red-600">{{ form.errors.new_images }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('debtors.notesOptional') }}</label>
                    <textarea v-model="form.note" rows="3" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white disabled:opacity-50">{{ t('common.save') }}</button>
                    <Link :href="route('app.debtors.index')" class="rounded-pill border-2 px-6 py-2.5 font-bold" style="border-color: var(--border-subtle)">{{ t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
