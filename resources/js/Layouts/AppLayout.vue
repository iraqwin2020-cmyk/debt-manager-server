<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FlashToast from '@/Components/FlashToast.vue';
import Icon from '@/Components/Icon.vue';

const { t } = useI18n();
const page = usePage();
const tenant = computed(() => page.props.auth?.tenant);
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.tenantNotifications ?? { count: 0, recent: [] });
const notifOpen = ref(false);

const navItems = computed(() => [
    { key: 'home', label: t('nav.home'), route: 'app.dashboard', match: ['app.dashboard'] },
    {
        key: 'debtors',
        label: t('nav.debtors'),
        route: 'app.debtors.index',
        match: ['app.debtors.index', 'app.debtors.create', 'app.debtors.store', 'app.debtors.show', 'app.debtors.edit', 'app.debtors.update', 'app.debtors.destroy', 'app.debtors.id-document', 'app.debtors.debts', 'app.debtors.statement'],
    },
    { key: 'guarantors', label: t('nav.guarantors'), route: 'app.guarantors.index', match: ['app.guarantors.*'] },
    { key: 'debts', label: t('nav.debts'), route: 'app.debts.index', match: ['app.debts.*'] },
    { key: 'myDebts', label: t('nav.myDebts'), route: 'app.my-debts.index', match: ['app.my-debts.*'] },
    { key: 'favorites', label: t('nav.favorites'), route: 'app.debtors.favorites', match: ['app.debtors.favorites', 'app.debtors.toggle-favorite'] },
    { key: 'notifications', label: t('nav.notifications'), route: 'app.notifications.index', match: ['app.notifications.*'], badge: notifications.value.count },
    { key: 'subscriptions', label: t('nav.subscriptions'), route: 'app.settings.edit', params: { tab: 'subscription' }, match: ['app.settings.*'] },
    { key: 'settings', label: t('nav.settings'), route: 'app.settings.edit', match: ['app.settings.*'] },
]);

function isActive(item) {
    const routeMatches = item.match.some((pattern) => route().current(pattern));
    if (!routeMatches) return false;
    if (item.route !== 'app.settings.edit') return true;

    const currentTab = new URLSearchParams(window.location.search).get('tab');
    return item.params?.tab ? currentTab === item.params.tab : currentTab !== 'subscription';
}

const menuOpen = ref(false);

const desktopQuery = window.matchMedia('(min-width: 768px)');
function closeMobileMenuIfDesktop() {
    if (desktopQuery.matches) menuOpen.value = false;
}
onMounted(() => desktopQuery.addEventListener('change', closeMobileMenuIfDesktop));

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

const dayName = computed(() => t('weekdays.' + new Date().getDay()));
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
            class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between px-4 shadow-sm md:px-6"
            style="background: var(--surface-solid); border-bottom: 1px solid var(--border-solid)"
        >
            <div class="flex items-center gap-3">
                <img v-if="tenant?.logo" :src="tenant.logo" alt="" class="h-9 w-9 rounded-full object-cover" />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">
                    {{ tenant?.name?.charAt(0) ?? 'م' }}
                </div>

                <div class="flex flex-col-reverse items-start gap-0 text-xs font-medium sm:flex-row sm:items-center sm:gap-2 sm:text-sm" style="color: var(--text-secondary)">
                    <span class="text-[0.65rem] leading-tight sm:text-sm">{{ dayName }}</span>
                    <bdi class="bdi-date leading-tight" dir="rtl">{{ dateParts }}</bdi>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="hidden h-9 w-9 items-center justify-center rounded-full border border-transparent text-sm transition-[border-color] hover:border-brand-400 md:flex"
                    :title="theme === 'dark' ? t('header.lightMode') : t('header.darkMode')"
                    @click="toggleTheme"
                >
                    <Icon :name="theme === 'dark' ? 'sun' : 'moon'" />
                </button>
                <div class="relative">
                    <button
                        type="button"
                        class="relative flex h-9 w-9 items-center justify-center rounded-full border border-transparent text-xl transition-[border-color] hover:border-brand-400"
                        style="color: var(--text-primary)"
                        @click="notifOpen = !notifOpen"
                    >
                        <Icon name="bell" />
                        <span v-if="notifications.count" class="absolute top-0.5 left-1 text-[0.7rem] font-extrabold" style="color: #dc2626">{{ notifications.count }}</span>
                    </button>
                    <div v-if="notifOpen" class="fixed inset-0 z-10" @click="notifOpen = false"></div>
                    <div v-if="notifOpen" class="fixed inset-x-4 top-[4.5rem] z-20 overflow-hidden rounded-2xl border shadow-lg sm:inset-x-auto sm:end-4 sm:w-72" style="background: var(--surface-solid); border-color: var(--border-subtle)">
                        <button
                            v-for="n in notifications.recent"
                            :key="n.id"
                            type="button"
                            class="block w-full border-b p-3 text-start text-xs transition-[background-color]"
                            style="border-color: var(--border-subtle)"
                            @click="notifOpen = false; n.link && router.visit(n.link)"
                        >
                            <span class="font-bold">{{ n.title }}</span>
                            <span class="mt-0.5 block" style="color: var(--text-secondary)">{{ n.meta }}</span>
                        </button>
                        <p v-if="notifications.recent.length === 0" class="p-4 text-center text-xs" style="color: var(--text-secondary)">{{ t('header.noNotifications') }}</p>
                        <Link :href="route('app.notifications.index')" class="block p-2.5 text-center text-xs font-bold text-brand-700 hover:underline" @click="notifOpen = false">{{ t('header.viewAllNotifications') }}</Link>
                    </div>
                </div>
                <Link :href="route('app.settings.account.edit')" class="flex items-center gap-2 rounded-pill border border-transparent ps-3! pe-0! py-1.5 transition-[border-color] hover:border-brand-400" style="color: var(--text-primary)">
                    <span class="text-sm font-semibold">{{ user?.name }}</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold text-white" style="background: linear-gradient(135deg, var(--color-brand-300), var(--color-brand-500)); box-shadow: 0 6px 16px -4px rgb(232 96 12 / 0.4)">
                        {{ user?.name?.charAt(0) }}
                    </span>
                </Link>
            </div>
        </header>

        <!-- القائمة الجانبية (حاسوب) -->
        <aside
            class="fixed inset-y-0 start-0 top-16 z-20 hidden w-48 flex-col gap-1 overflow-y-auto p-3 shadow-sm md:flex"
            style="background: var(--surface-solid); border-inline-end: 1px solid var(--border-solid)"
        >
            <div class="mb-6 mt-4 flex flex-col items-center gap-2">
                <img v-if="tenant?.logo" :src="tenant.logo" alt="" class="h-16 w-16 rounded-full object-cover shadow-sm" />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold text-white shadow-sm" style="background: linear-gradient(135deg, var(--color-brand-300), var(--color-brand-600))">
                    {{ tenant?.name?.charAt(0) ?? 'م' }}
                </div>
                <span class="max-w-full truncate text-sm font-bold" style="color: var(--text-primary)">{{ tenant?.name }}</span>
            </div>

            <Link
                v-for="item in navItems"
                :key="item.key"
                :href="route(item.route, item.params)"
                class="flex items-center justify-between rounded-pill border border-transparent px-4 py-2.5 text-sm font-semibold shadow-sm transition-[border-color]"
                :class="isActive(item) ? '' : 'hover:border-brand-400'"
                :style="isActive(item) ? 'background: var(--color-ink); color: var(--color-paper)' : 'color: var(--text-primary)'"
            >
                <span>{{ item.label }}</span>
                <span v-if="item.badge" class="text-xs font-extrabold" style="color: #dc2626">{{ item.badge }}</span>
            </Link>
        </aside>

        <!-- المحتوى -->
        <main class="px-4 pt-20 pb-24 md:ms-48 md:px-6 md:pb-10">
            <slot />
        </main>

        <!-- فوتر الهاتف -->
        <nav
            class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 shadow-lg md:hidden"
            style="background: var(--surface-solid); border-top: 1px solid var(--border-solid)"
        >
            <Link :href="route('app.dashboard')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-2xl"><Icon name="home" /></span> {{ t('nav.home') }}
            </Link>
            <button type="button" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold" @click="menuOpen = true">
                <span class="text-2xl"><Icon name="menu" /></span> {{ t('nav.menu') }}
            </button>
            <Link :href="route('app.debts.create')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-2xl"><Icon name="plus" /></span> {{ t('nav.newDebt') }}
            </Link>
            <Link :href="route('app.payments.create')" class="flex flex-col items-center gap-1 py-2.5 text-xs font-semibold">
                <span class="text-2xl"><Icon name="cash" /></span> {{ t('nav.pay') }}
            </Link>
        </nav>

        <!-- القائمة المنسدلة من الأسفل (هاتف) -->
        <div v-if="menuOpen" class="fixed inset-0 z-40 md:hidden">
            <div class="absolute inset-0 bg-black/10" @click="menuOpen = false"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl p-4 pb-6 shadow-lg" style="background: var(--surface-solid)">
                <div class="grid grid-cols-3 gap-2.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="route(item.route, item.params)"
                        class="flex items-center justify-center gap-1 rounded-pill border border-transparent font-semibold shadow-sm transition-[border-color]"
                        :class="isActive(item) ? '' : 'hover:border-brand-400'"
                        :style="isActive(item) ? 'background: var(--color-ink); color: var(--color-paper)' : 'color: var(--text-primary)'"
                        @click="menuOpen = false"
                    >
                        <span>{{ item.label }}</span>
                        <span v-if="item.badge" class="text-xs font-extrabold" style="color: #dc2626">{{ item.badge }}</span>
                    </Link>
                </div>
                <button
                    type="button"
                    class="mt-3 mr-auto flex h-8 w-8 items-center justify-center rounded-full border border-transparent text-sm transition-[border-color] hover:border-brand-400"
                    :title="theme === 'dark' ? t('header.lightMode') : t('header.darkMode')"
                    @click="toggleTheme"
                >
                    <Icon :name="theme === 'dark' ? 'sun' : 'moon'" />
                </button>
            </div>
        </div>
    </div>
</template>
