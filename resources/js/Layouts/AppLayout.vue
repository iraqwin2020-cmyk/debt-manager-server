<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashToast from '@/Components/FlashToast.vue';
import Icon from '@/Components/Icon.vue';

const page = usePage();
const tenant = computed(() => page.props.auth?.tenant);
const user = computed(() => page.props.auth?.user);

const navItems = [
    { label: 'الرئيسية', route: 'app.dashboard' },
    { label: 'العملاء', route: 'app.debtors.index' },
    { label: 'الكفلاء', route: 'app.guarantors.index' },
    { label: 'الديون', route: 'app.debts.index' },
    { label: 'ديوني', route: 'app.my-debts.index' },
    { label: 'المفضلة', route: 'app.debtors.favorites' },
    { label: 'الإعدادات', route: 'app.settings.edit' },
];

const menuOpen = ref(false);

const theme = ref(tenant.value?.theme ?? 'light');
function applyTheme(value) {
    document.documentElement.setAttribute('data-theme', value);
}
function toggleTheme() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
    applyTheme(theme.value);
    router.patch(route('app.settings.theme'), { theme: theme.value }, { preserveScroll: true, preserveState: true });
}
onMounted(() => applyTheme(theme.value));

const dayName = computed(() =>
    new Intl.DateTimeFormat('ar', { weekday: 'long' }).format(new Date())
);
const dateParts = computed(() => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(d.getDate())} ${pad(d.getMonth() + 1)} ${d.getFullYear()}`;
});
</script>

<template>
    <div class="min-h-screen" style="background: var(--surface-page)">
        <FlashToast />

        <!-- الهيدر -->
        <header
            class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between px-4 md:px-6"
            style="background: var(--surface-card); border-bottom: 1px solid var(--border-subtle)"
        >
            <div class="flex items-center gap-3">
                <img v-if="tenant?.logo" :src="tenant.logo" alt="" class="h-9 w-9 rounded-full object-cover" />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">
                    {{ tenant?.name?.charAt(0) ?? 'م' }}
                </div>
            </div>

            <div class="flex flex-col-reverse items-center gap-0 text-xs font-medium sm:flex-row sm:gap-2 sm:text-sm" style="color: var(--text-secondary)">
                <span class="text-[0.65rem] leading-tight sm:text-sm">{{ dayName }}</span>
                <bdi class="bdi-date leading-tight" dir="rtl">{{ dateParts }}</bdi>
            </div>

            <Link :href="route('app.settings.edit')" class="flex items-center gap-2 rounded-pill border border-transparent px-3 py-1.5 transition-[border-color] hover:border-brand-400" style="color: var(--text-primary)">
                <span class="text-sm font-semibold">{{ user?.name }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-700 text-sm font-bold">
                    {{ user?.name?.charAt(0) }}
                </span>
            </Link>
        </header>

        <!-- القائمة الجانبية (حاسوب) -->
        <aside
            class="fixed inset-y-0 right-0 top-16 z-20 hidden w-48 flex-col gap-1 overflow-y-auto p-3 md:flex"
            style="background: var(--surface-card); border-left: 1px solid var(--border-subtle)"
        >
            <button
                type="button"
                class="mb-3 mr-auto flex h-7 w-7 items-center justify-center rounded-full border border-transparent text-sm transition-[border-color] hover:border-brand-400"
                :title="theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
                @click="toggleTheme"
            >
                <Icon :name="theme === 'dark' ? 'sun' : 'moon'" />
            </button>

            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="route(item.route)"
                class="rounded-pill border border-transparent px-4 py-2.5 text-sm font-semibold transition-[border-color]"
                :class="route().current(item.route)
                    ? 'bg-brand-600 text-white'
                    : 'hover:border-brand-400'"
                :style="route().current(item.route) ? '' : 'color: var(--text-primary)'"
            >
                {{ item.label }}
            </Link>
        </aside>

        <!-- المحتوى -->
        <main class="px-4 pt-20 pb-24 md:ms-48 md:pb-10">
            <slot />
        </main>

        <!-- فوتر الهاتف -->
        <nav
            class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 md:hidden"
            style="background: var(--surface-card); border-top: 1px solid var(--border-subtle)"
        >
            <Link :href="route('app.dashboard')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-lg"><Icon name="home" /></span> الرئيسية
            </Link>
            <button type="button" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold" @click="menuOpen = true">
                <span class="text-lg"><Icon name="menu" /></span> القائمة
            </button>
            <Link :href="route('app.debts.create')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-lg"><Icon name="plus" /></span> دين جديد
            </Link>
            <Link :href="route('app.payments.create')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-lg"><Icon name="cash" /></span> تسديد
            </Link>
        </nav>

        <!-- القائمة المنسدلة من الأسفل (هاتف) -->
        <div v-if="menuOpen" class="fixed inset-0 z-40 md:hidden">
            <div class="absolute inset-0 bg-black/40" @click="menuOpen = false"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl p-4 pb-6" style="background: var(--surface-card)">
                <div class="grid grid-cols-3 gap-2.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="rounded-xl border px-2 py-4 text-center text-sm font-semibold transition-[border-color]"
                        :class="route().current(item.route) ? 'bg-brand-600 text-white' : 'border-[var(--border-subtle)] hover:border-brand-400'"
                        :style="route().current(item.route) ? '' : 'color: var(--text-primary)'"
                        @click="menuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </div>
                <button
                    type="button"
                    class="mt-3 mr-auto flex h-8 w-8 items-center justify-center rounded-full border border-transparent text-sm transition-[border-color] hover:border-brand-400"
                    :title="theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
                    @click="toggleTheme"
                >
                    <Icon :name="theme === 'dark' ? 'sun' : 'moon'" />
                </button>
            </div>
        </div>
    </div>
</template>
