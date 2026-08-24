<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ debtor: { type: Object, required: true } });

const form = useForm({
    name: props.debtor.name,
    phone: props.debtor.phone,
    address: props.debtor.address ?? '',
    note: props.debtor.note ?? '',
    id_document_image: null,
    _method: 'put',
});

function submit() {
    form.post(route('app.debtors.update', props.debtor.id));
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-lg">
            <h1 class="mb-6 text-lg font-extrabold sm:text-2xl">تعديل بيانات العميل</h1>

            <form class="space-y-4 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-semibold">الاسم</label>
                    <input v-model="form.name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">رقم الهاتف</label>
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
                    <label class="mb-1 block text-sm font-semibold">العنوان</label>
                    <input v-model="form.address" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">صورة المستمسك</label>
                    <img v-if="debtor.id_document_image" src="#" class="mb-2 hidden h-20 rounded-lg" />
                    <input type="file" accept="image/*" class="w-full text-sm" @input="form.id_document_image = $event.target.files[0]" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">ملاحظات</label>
                    <textarea v-model="form.note" rows="3" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="form.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white disabled:opacity-50">حفظ التعديلات</button>
                    <Link :href="route('app.debtors.index')" class="rounded-pill border-2 px-6 py-2.5 font-bold" style="border-color: var(--border-subtle)">رجوع</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
