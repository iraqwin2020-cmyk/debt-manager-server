<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

const page = usePage();
const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
const receiptUrl = computed(() => page.props.flash?.receiptUrl);
const shareUrl = computed(() => page.props.flash?.shareUrl);
const generatedCode = computed(() => page.props.flash?.generatedCode);

const codeCopied = ref(false);
function copyGeneratedCode() {
    navigator.clipboard?.writeText(generatedCode.value.code);
    codeCopied.value = true;
    setTimeout(() => (codeCopied.value = false), 2000);
}

const generatedCodeWhatsappHref = computed(() => {
    if (!generatedCode.value) return '';
    const g = generatedCode.value;
    const intl = (page.props.countryCode ?? '964') + g.tenantPhone.replace(/^0/, '');
    const text = encodeURIComponent(`مرحباً ${g.tenantName}، كود تفعيل باقة "${g.planName}": ${g.code}\nأدخله من إعدادات حسابك > الاشتراك لتفعيل الباقة.`);
    return `https://wa.me/${intl}?text=${text}`;
});
</script>

<template>
    <div v-if="success || error" class="fixed inset-x-0 top-20 z-40 mx-auto flex w-fit max-w-[90%] flex-wrap items-center justify-center gap-x-3 gap-y-1 rounded-pill px-5 py-2.5 text-sm font-semibold shadow-lg" :style="error ? 'background: var(--status-danger-bg); color: var(--status-danger-text)' : 'background: var(--status-success-bg); color: var(--status-success-text)'">
        <span>{{ error || success }}</span>
        <a v-if="receiptUrl" :href="receiptUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 underline"><Icon name="print" /> طباعة الوصل</a>
        <a v-if="shareUrl" :href="shareUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 underline"><Icon name="whatsapp" /> مشاركة واتساب</a>
        <button
            v-if="generatedCode"
            type="button"
            class="inline-flex items-center gap-1 underline"
            :style="codeCopied ? 'color: #16a34a' : ''"
            @click="copyGeneratedCode"
        >
            <Icon :name="codeCopied ? 'check' : 'copy'" />
            <span v-if="codeCopied">تم النسخ</span>
            <template v-else>نسخ الكود: <bdi class="bdi-ltr">{{ generatedCode.code }}</bdi></template>
        </button>
        <a v-if="generatedCode" :href="generatedCodeWhatsappHref" target="_blank" rel="noopener" class="inline-flex items-center gap-1 underline"><Icon name="whatsapp" /> إرسال للمشترك</a>
    </div>
</template>
