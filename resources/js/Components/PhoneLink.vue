<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    phone: { type: String, required: true },
    countryCode: { type: String, default: null },
});

const open = ref(false);
const triggerRef = ref(null);
const menuStyle = ref({});

function international() {
    const code = props.countryCode ?? usePage().props.countryCode ?? '964';
    return code + props.phone.replace(/^0/, '');
}

const options = [
    { label: 'اتصال عادي', icon: 'phone', href: () => `tel:${props.phone}` },
    { label: 'اتصال واتساب', icon: 'whatsapp', href: () => `https://wa.me/${international()}` },
    { label: 'رسالة نصية (SMS)', icon: 'sms', href: () => `sms:${props.phone}` },
    { label: 'رسالة واتساب', icon: 'whatsapp', href: () => `https://wa.me/${international()}?text=${encodeURIComponent('تذكير بخصوص دينكم')}` },
];

function toggle() {
    if (open.value) {
        open.value = false;
        return;
    }

    const rect = triggerRef.value.getBoundingClientRect();
    const menuWidth = 192;
    const menuHeight = 160;

    let left = rect.left;
    left = Math.min(left, window.innerWidth - menuWidth - 8);
    left = Math.max(left, 8);

    let top = rect.bottom + 4;
    if (top + menuHeight > window.innerHeight - 8) {
        top = rect.top - menuHeight - 4;
    }

    menuStyle.value = { top: `${top}px`, left: `${left}px` };
    open.value = true;
}
</script>

<template>
    <span class="relative inline-block">
        <button ref="triggerRef" type="button" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline" @click="toggle">
            <Icon name="phone" /> <bdi class="bdi-ltr">{{ phone }}</bdi>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                class="fixed z-50 w-48 overflow-hidden rounded-xl border shadow-lg"
                :style="{ top: menuStyle.top, left: menuStyle.left, background: 'var(--surface-solid)', borderColor: 'var(--border-subtle)' }"
            >
                <a
                    v-for="opt in options"
                    :key="opt.label"
                    :href="opt.href()"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center gap-2 px-3 py-2 text-sm text-[var(--text-primary)] transition hover:bg-brand-600 hover:text-white"
                    @click="open = false"
                >
                    <Icon :name="opt.icon" /> {{ opt.label }}
                </a>
            </div>
            <div v-if="open" class="fixed inset-0 z-40" @click="open = false"></div>
        </Teleport>
    </span>
</template>
