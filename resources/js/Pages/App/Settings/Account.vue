<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    tenant: { type: Object, required: true },
    user: { type: Object, required: true },
});

const logoInput = ref(null);
const logoFileName = ref('');

const accountForm = useForm({
    office_name: props.tenant.name,
    name: props.user.name,
    logo: null,
    _method: 'patch',
});
function saveAccount() {
    accountForm.post(route('app.settings.account'), { preserveScroll: true });
}
function pickLogo(event) {
    const file = event.target.files[0];
    accountForm.logo = file ?? null;
    logoFileName.value = file?.name ?? '';
}

const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
function savePassword() {
    passwordForm.patch(route('app.settings.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

function logout() {
    useForm({}).post(route('logout'));
}

const showDeleteAccount = ref(false);
const deleteForm = useForm({ password: '' });
function destroyAccount() {
    deleteForm.delete(route('app.settings.account.destroy'));
}
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-lg font-extrabold sm:text-2xl">{{ t('account.title') }}</h1>
            <Link :href="route('app.settings.edit')" class="text-sm font-semibold text-brand-700 hover:underline">{{ t('nav.settings') }}</Link>
        </div>

        <div class="mx-auto max-w-lg space-y-8 rounded-card p-6" style="background: var(--surface-card); box-shadow: var(--shadow-card)">
            <form class="space-y-4" @submit.prevent="saveAccount">
                <h2 class="font-bold">{{ t('account.accountData') }}</h2>
                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('account.officeName') }}</label>
                    <input v-model="accountForm.office_name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                    <p v-if="accountForm.errors.office_name" class="mt-1 text-sm text-red-600">{{ accountForm.errors.office_name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('account.username') }}</label>
                    <input v-model="accountForm.name" type="text" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                    <p v-if="accountForm.errors.name" class="mt-1 text-sm text-red-600">{{ accountForm.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('account.phoneDisplayOnly') }}</label>
                    <input :value="user.phone" type="text" dir="ltr" disabled class="w-full rounded-xl border px-4 py-2.5 text-end" style="border-color: var(--border-subtle); background: var(--surface-page); color: var(--text-secondary)" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ t('account.logo') }}</label>
                    <img v-if="tenant.logo" :src="tenant.logo" alt="" class="mb-2 h-16 w-16 rounded-full object-cover" />
                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded-pill border-2 border-brand-600 px-4 py-1.5 text-sm font-bold text-brand-700" @click="logoInput.click()">{{ t('account.chooseImage') }}</button>
                        <span class="text-xs" style="color: var(--text-secondary)">{{ logoFileName || t('common.noFileChosen') }}</span>
                        <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="pickLogo" />
                    </div>
                    <p class="mt-1 text-xs" style="color: var(--text-secondary)">{{ t('account.logoMaxSize') }}</p>
                    <p v-if="accountForm.errors.logo" class="mt-1 text-sm text-red-600">{{ accountForm.errors.logo }}</p>
                </div>
                <button type="submit" :disabled="accountForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">{{ t('common.save') }}</button>
            </form>

            <form class="space-y-4 border-t pt-6" style="border-color: var(--border-subtle)" @submit.prevent="savePassword">
                <h2 class="font-bold">{{ t('account.changePassword') }}</h2>
                <input v-model="passwordForm.current_password" type="password" :placeholder="t('account.currentPassword')" autocomplete="current-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                <input v-model="passwordForm.password" type="password" :placeholder="t('account.newPassword')" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <input v-model="passwordForm.password_confirmation" type="password" :placeholder="t('account.confirmPassword')" autocomplete="new-password" class="w-full rounded-xl border px-4 py-2.5" style="border-color: var(--border-subtle)" />
                <button type="submit" :disabled="passwordForm.processing" class="rounded-pill bg-brand-600 px-6 py-2.5 font-bold text-white">{{ t('common.change') }}</button>
            </form>

            <div class="border-t pt-6" style="border-color: var(--border-subtle)">
                <button type="button" class="w-full rounded-pill border-2 border-brand-600 bg-brand-600 px-6 py-2.5 text-center font-bold text-white" @click="logout">{{ t('nav.logout') }}</button>
            </div>

            <div class="border-t pt-6" style="border-color: var(--border-subtle)">
                <button v-if="!showDeleteAccount" type="button" class="w-full rounded-pill border-2 border-brand-600 bg-brand-600 px-6 py-2.5 text-center font-bold text-white" @click="showDeleteAccount = true">
                    {{ t('account.deleteAccount') }}
                </button>
                <form v-else class="space-y-3" @submit.prevent="destroyAccount">
                    <p class="text-sm font-semibold text-red-600">{{ t('account.deleteAccountWarning') }}</p>
                    <input
                        v-model="deleteForm.password"
                        type="password"
                        :placeholder="t('auth.password')"
                        autocomplete="current-password"
                        class="w-full rounded-xl border px-4 py-2.5"
                        style="border-color: var(--border-subtle)"
                    />
                    <p v-if="deleteForm.errors.password" class="text-sm text-red-600">{{ deleteForm.errors.password }}</p>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="deleteForm.processing" class="flex-1 rounded-pill bg-red-600 px-4 py-2 text-sm font-bold text-white disabled:opacity-50">
                            {{ t('account.confirmDelete') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-pill border-2 px-4 py-2 text-sm font-bold"
                            style="border-color: var(--border-subtle)"
                            @click="showDeleteAccount = false; deleteForm.reset(); deleteForm.clearErrors()"
                        >
                            {{ t('common.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
