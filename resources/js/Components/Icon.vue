<script setup>
const props = defineProps({
    name: { type: String, required: true },
    filled: { type: Boolean, default: false },
});
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        :fill="filled ? 'currentColor' : 'none'"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="inline-block h-[1em] w-[1em] shrink-0 align-[-0.15em]"
    >
        <template v-if="name === 'wallet'">
            <path d="M3 7.5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2V9H5a2 2 0 0 1-2-2Z" />
            <path d="M3 7.5V17a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H8" />
            <circle cx="17" cy="13.5" r="1.1" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'users'">
            <circle cx="9" cy="8" r="3" />
            <path d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5" />
            <circle cx="17" cy="9" r="2.3" />
            <path d="M15.7 14.2c2.4.4 4.3 2.2 4.3 4.8" />
        </template>

        <template v-else-if="name === 'warning'">
            <path d="M12 3.5 21.5 20h-19L12 3.5Z" />
            <path d="M12 10v4" />
            <circle cx="12" cy="17" r="0.15" fill="currentColor" stroke-width="2.4" />
        </template>

        <template v-else-if="name === 'calendar'">
            <rect x="3.5" y="5" width="17" height="16" rx="2.2" />
            <path d="M3.5 9.8h17" />
            <path d="M8 3v3.4" />
            <path d="M16 3v3.4" />
            <circle cx="8.2" cy="14" r="0.15" fill="currentColor" stroke-width="2.2" />
            <circle cx="12" cy="14" r="0.15" fill="currentColor" stroke-width="2.2" />
        </template>

        <template v-else-if="name === 'sun'">
            <circle cx="12" cy="12" r="4.2" />
            <path d="M12 2.5v2.2M12 19.3v2.2M4.4 4.4l1.6 1.6M18 18l1.6 1.6M2.5 12h2.2M19.3 12h2.2M4.4 19.6 6 18M18 6l1.6-1.6" />
        </template>

        <template v-else-if="name === 'moon'">
            <path d="M20.5 14.5a8.5 8.5 0 1 1-9-11 7 7 0 0 0 9 11Z" />
        </template>

        <template v-else-if="name === 'home'">
            <path d="M4 11.5 12 4l8 7.5" />
            <path d="M6 10v9a1 1 0 0 0 1 1h3v-5h4v5h3a1 1 0 0 0 1-1v-9" />
        </template>

        <template v-else-if="name === 'menu'">
            <path d="M4 6.5h16M4 12h16M4 17.5h16" />
        </template>

        <template v-else-if="name === 'plus'">
            <path d="M12 5v14M5 12h14" />
        </template>

        <template v-else-if="name === 'cash'">
            <rect x="2.5" y="6.5" width="19" height="11" rx="1.8" />
            <circle cx="12" cy="12" r="2.6" />
            <path d="M5.5 9v0M18.5 15v0" />
        </template>

        <template v-else-if="name === 'share'">
            <circle cx="18" cy="5.5" r="2.3" />
            <circle cx="6" cy="12" r="2.3" />
            <circle cx="18" cy="18.5" r="2.3" />
            <path d="m8 10.8 8-4.3M8 13.2l8 4.3" />
        </template>

        <template v-else-if="name === 'print'">
            <path d="M7 8.5V4h10v4.5" />
            <rect x="4.5" y="8.5" width="15" height="7" rx="1.5" />
            <path d="M7 15h10v5H7z" />
        </template>

        <template v-else-if="name === 'whatsapp'">
            <path d="M6.5 17.5 4.5 20l2.6-.7A8 8 0 1 0 4.5 12 7.9 7.9 0 0 0 6.5 17.5Z" />
            <path d="M9 10.2c.2 2.6 2.2 4.6 4.8 4.8.8 0 .9-.5.9-1v-.5c0-.4-.3-.6-.6-.7l-1.4-.4c-.3-.1-.6 0-.8.2l-.3.3a5.6 5.6 0 0 1-2.3-2.3l.3-.3c.2-.2.3-.5.2-.8l-.4-1.4c-.1-.3-.3-.6-.7-.6H8.9c-.5 0-1 .1-1 .9Z" fill="currentColor" stroke="none" />
        </template>

        <template v-else-if="name === 'phone'">
            <path d="M6 3.5h2.5l1.5 4-2 1.5a12 12 0 0 0 6.5 6.5l1.5-2 4 1.5V18a2 2 0 0 1-2 2C10.5 20 4 13.5 4 6a2 2 0 0 1 2-2.5Z" />
        </template>

        <template v-else-if="name === 'sms'">
            <rect x="3" y="5" width="18" height="13" rx="2" />
            <path d="m3.5 6 8.5 6 8.5-6" />
        </template>

        <template v-else-if="name === 'document'">
            <path d="M6.5 3.5h7L18.5 8v11.5a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" />
            <path d="M13.5 3.5V8h5" />
            <path d="M8.5 12.5h7M8.5 16h7" />
        </template>

        <template v-else-if="name === 'close'">
            <path d="M6 6l12 12M18 6 6 18" />
        </template>

        <template v-else-if="name === 'copy'">
            <rect x="8.5" y="8.5" width="12" height="12" rx="1.8" />
            <path d="M15.5 8.5V5.8a1.8 1.8 0 0 0-1.8-1.8H5.8A1.8 1.8 0 0 0 4 5.8v7.9a1.8 1.8 0 0 0 1.8 1.8h2.7" />
        </template>

        <template v-else-if="name === 'check'">
            <path d="M5 13l4.5 4.5L19 8" />
        </template>

        <template v-else-if="name === 'bell'">
            <path d="M6 10a6 6 0 0 1 12 0c0 4 1.5 5.5 1.5 5.5H4.5S6 14 6 10Z" />
            <path d="M10 19a2 2 0 0 0 4 0" />
        </template>

        <template v-else-if="name === 'star'">
            <path d="M12 3.5l2.6 5.4 5.9.8-4.3 4.1 1 5.9-5.2-2.8-5.2 2.8 1-5.9-4.3-4.1 5.9-.8Z" />
        </template>

        <template v-else-if="name === 'chart'">
            <path d="M4 20V10M10 20V4M16 20v-7M22 20H2" />
        </template>

        <template v-else-if="name === 'chevron-down'">
            <path d="m6 9 6 6 6-6" />
        </template>

        <template v-else-if="name === 'trash'">
            <path d="M4 7h16" />
            <path d="M9 7V4.8c0-.4.4-.8.9-.8h4.2c.5 0 .9.4.9.8V7" />
            <path d="M6.5 7 7.3 19a2 2 0 0 0 2 1.8h5.4a2 2 0 0 0 2-1.8L17.5 7" />
            <path d="M10 11v6M14 11v6" />
        </template>

        <template v-else-if="name === 'edit'">
            <path d="M4 20h4.2L18.8 9.4a2 2 0 0 0 0-2.8L17.4 5.2a2 2 0 0 0-2.8 0L4 15.8Z" />
            <path d="M13 7.5 16.5 11" />
        </template>

        <template v-else-if="name === 'back'">
            <path d="M15 5 8 12l7 7" />
        </template>

        <template v-else-if="name === 'skip'">
            <path d="M17 5 10 12l7 7" />
            <path d="M10 5 3 12l7 7" />
        </template>

        <template v-else-if="name === 'search'">
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path d="m20 20-4.5-4.5" />
        </template>

        <template v-else-if="name === 'logout'">
            <path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3" />
            <path d="M14 16l4-4-4-4" />
            <path d="M18 12H9" />
        </template>

        <template v-else-if="name === 'dot'">
            <circle cx="12" cy="12" r="6" fill="currentColor" stroke="none" />
        </template>
    </svg>
</template>
