<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { RTL_LOCALES } from '@/i18n';
import Icon from '@/Components/Icon.vue';

const { t, locale } = useI18n();
const isRtl = computed(() => RTL_LOCALES.includes(locale.value));

const props = defineProps({
    show: { type: Boolean, default: false },
    images: { type: Array, default: () => [] },
    title: { type: String, default: null },
});

const resolvedTitle = computed(() => props.title ?? t('documentViewer.title'));
defineEmits(['close']);

const activeIndex = ref(0);
watch(() => props.show, (value) => {
    if (value) activeIndex.value = 0;
});

const currentUrl = computed(() => props.images[activeIndex.value] ?? null);

function prev() {
    activeIndex.value = (activeIndex.value - 1 + props.images.length) % props.images.length;
}
function next() {
    activeIndex.value = (activeIndex.value + 1) % props.images.length;
}

function printImage() {
    if (!currentUrl.value) return;
    const win = window.open(currentUrl.value, '_blank');
    if (win) {
        win.onload = () => {
            win.focus();
            win.print();
        };
    }
}

async function shareImage() {
    if (!currentUrl.value) return;
    try {
        const response = await fetch(currentUrl.value);
        const blob = await response.blob();
        const file = new File([blob], `document.${blob.type.split('/')[1] || 'jpg'}`, { type: blob.type });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file], title: props.title });
            return;
        }
    } catch {
        // تجاهل وانتقل للحل البديل
    }
    window.open(currentUrl.value, '_blank');
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-auto rounded-2xl p-4 shadow-lg" style="background: var(--surface-solid)">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-bold" style="color: var(--text-primary)">
                    {{ resolvedTitle }}
                    <span v-if="images.length > 1" class="text-sm font-normal" style="color: var(--text-secondary)">({{ activeIndex + 1 }}/{{ images.length }})</span>
                </h3>
                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full border border-transparent transition-[border-color] hover:border-brand-400" style="color: var(--text-primary)" @click="$emit('close')">
                    <Icon name="close" />
                </button>
            </div>

            <template v-if="images.length">
                <div class="relative">
                    <img :src="currentUrl" alt="" class="w-full rounded-xl" />
                    <template v-if="images.length > 1">
                        <button type="button" class="absolute top-1/2 start-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-white" style="background: rgba(0,0,0,0.5)" @click="prev">
                            <Icon name="back" :style="isRtl ? 'transform: scaleX(-1)' : ''" />
                        </button>
                        <button type="button" class="absolute top-1/2 end-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-white" style="background: rgba(0,0,0,0.5)" @click="next">
                            <Icon name="back" :style="isRtl ? '' : 'transform: scaleX(-1)'" />
                        </button>
                    </template>
                </div>

                <div class="mt-3 flex justify-center gap-3">
                    <button type="button" class="flex items-center gap-1.5 rounded-pill border-2 border-brand-600 px-4 py-1.5 text-sm font-bold text-brand-700" @click="shareImage">
                        <Icon name="share" /> {{ t('common.share') }}
                    </button>
                    <button type="button" class="flex items-center gap-1.5 rounded-pill bg-brand-600 px-4 py-1.5 text-sm font-bold text-white" @click="printImage">
                        <Icon name="print" /> {{ t('common.print') }}
                    </button>
                </div>
            </template>
            <p v-else class="py-10 text-center text-sm" style="color: var(--text-secondary)">{{ t('documentViewer.noDocuments') }}</p>
        </div>
    </div>
</template>
