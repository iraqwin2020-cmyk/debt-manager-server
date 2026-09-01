<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashToast from '@/Components/FlashToast.vue';
import Icon from '@/Components/Icon.vue';
import FormattedDate from '@/Components/FormattedDate.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const platformTheme = computed(() => page.props.platformTheme ?? 'light');
const pendingPlanRequestsCount = computed(() => page.props.pendingPlanRequestsCount ?? 0);
const notifications = computed(() => page.props.notifications ?? { unreadCount: 0, recent: [] });

const navItems = computed(() => [
    { label: 'الرئيسية', route: 'platform.dashboard', match: ['platform.dashboard'] },
    { label: 'المشتركون', route: 'platform.subscribers.index', match: ['platform.subscribers.*'] },
    { label: 'الباقات', route: 'platform.plans.index', match: ['platform.plans.*'] },
    { label: 'أكواد التفعيل', route: 'platform.activation-codes.index', match: ['platform.activation-codes.*'] },
    { label: 'طلبات الخطط', route: 'platform.plan-requests.index', match: ['platform.plan-requests.*'], badge: pendingPlanRequestsCount.value },
    { label: 'الإشعارات', route: 'platform.notifications.index', match: ['platform.notifications.*'], badge: notifications.value.unreadCount },
    { label: 'سجل الحركات', route: 'platform.activity-logs.index', match: ['platform.activity-logs.*'] },
    { label: 'الإعدادات', route: 'platform.settings.edit', match: ['platform.settings.*'] },
]);

function isActive(item) {
    return item.match.some((pattern) => route().current(pattern));
}

const menuOpen = ref(false);
const notifOpen = ref(false);

const desktopQuery = window.matchMedia('(min-width: 768px)');
function closeMobileMenuIfDesktop() {
    if (desktopQuery.matches) menuOpen.value = false;
}
onMounted(() => desktopQuery.addEventListener('change', closeMobileMenuIfDesktop));

function openNotification(n) {
    notifOpen.value = false;
    if (!n.read_at) {
        router.patch(route('platform.notifications.read', n.id), {}, { preserveScroll: true });
    }
    if (n.link) {
        router.visit(n.link);
    }
}

const theme = ref(platformTheme.value);
function applyTheme(value) {
    document.documentElement.setAttribute('data-theme', value);
}
function toggleTheme() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
    applyTheme(theme.value);
    router.patch(route('platform.settings.theme'), { theme: theme.value }, { preserveScroll: true, preserveState: true });
}
onMounted(() => applyTheme(theme.value));

function logout() {
    router.post(route('platform.logout'));
}
</script>

<template>
    <div class="min-h-screen" style="background: var(--surface-page)">
        <FlashToast />
        <header
            class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between px-4 shadow-sm md:px-6"
            style="background: var(--surface-solid); border-bottom: 1px solid var(--border-solid)"
        >
            <span class="font-extrabold text-brand-700">لوحة مدير المشروع</span>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-transparent transition-[border-color] hover:border-brand-400"
                    style="color: var(--text-primary)"
                    :title="theme === 'dark' ? 'الوضع الفاتح' : 'الوضع الداكن'"
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
                        <span v-if="notifications.unreadCount" class="absolute -top-1 -left-1 text-[0.7rem] font-extrabold" style="color: #dc2626">{{ notifications.unreadCount }}</span>
                    </button>
                    <div v-if="notifOpen" class="fixed inset-0 z-10" @click="notifOpen = false"></div>
                    <div v-if="notifOpen" class="absolute start-0 z-20 mt-2 w-72 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border shadow-lg" style="background: var(--surface-solid); border-color: var(--border-subtle)">
                        <button
                            v-for="n in notifications.recent"
                            :key="n.id"
                            type="button"
                            class="block w-full border-b p-3 text-start text-xs transition-[background-color]"
                            style="border-color: var(--border-subtle)"
                            :style="!n.read_at ? 'background: var(--status-success-bg)' : ''"
                            @click="openNotification(n)"
                        >
                            <span :class="!n.read_at ? 'font-bold' : ''">{{ n.title }}</span>
                            <span class="mt-0.5 block" style="color: var(--text-secondary)"><FormattedDate :value="n.created_at" /></span>
                        </button>
                        <p v-if="notifications.recent.length === 0" class="p-4 text-center text-xs" style="color: var(--text-secondary)">لا توجد إشعارات بعد.</p>
                        <Link :href="route('platform.notifications.index')" class="block p-2.5 text-center text-xs font-bold text-brand-700 hover:underline" @click="notifOpen = false">عرض كل الإشعارات</Link>
                    </div>
                </div>
                <Link :href="route('platform.settings.account.edit')" class="rounded-pill border border-transparent px-3 py-1.5 text-sm font-semibold transition-[border-color] hover:border-brand-400" style="color: var(--text-primary)">
                    {{ user?.name }}
                </Link>
            </div>
        </header>

        <aside
            class="fixed inset-y-0 start-0 top-16 z-20 hidden w-48 flex-col gap-1 overflow-y-auto p-3 shadow-sm md:flex"
            style="background: var(--surface-solid); border-inline-end: 1px solid var(--border-solid)"
        >
            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="route(item.route)"
                class="flex items-center justify-between rounded-pill border border-transparent px-4 py-2.5 text-sm font-semibold shadow-sm transition-[border-color]"
                :class="isActive(item) ? '' : 'hover:border-brand-400'"
                :style="isActive(item) ? 'background: var(--color-ink); color: var(--color-paper)' : 'color: var(--text-primary)'"
            >
                <span>{{ item.label }}</span>
                <span v-if="item.badge" class="text-xs font-extrabold" style="color: #dc2626">{{ item.badge }}</span>
            </Link>
            <button type="button" class="mt-4 rounded-pill border border-transparent px-4 py-2.5 text-start text-sm font-semibold text-red-600 transition-[border-color] hover:border-red-400" @click="logout">
                تسجيل الخروج
            </button>
        </aside>

        <main class="p-4 pt-20 pb-20 md:ms-48 md:pb-4">
            <slot />
        </main>

        <footer
            class="fixed inset-x-0 bottom-0 z-20 flex items-center justify-around border-t p-2 shadow-lg md:hidden"
            style="background: var(--surface-solid); border-color: var(--border-solid)"
        >
            <button type="button" class="flex flex-col items-center gap-0.5 px-6 py-1 text-xs font-semibold" style="color: var(--text-primary)" @click="menuOpen = true">
                <span class="text-2xl"><Icon name="menu" /></span>
                القائمة
            </button>
            <Link :href="route('platform.dashboard')" class="flex flex-col items-center gap-0.5 px-6 py-1 text-xs font-semibold" style="color: var(--text-primary)">
                <span class="text-2xl"><Icon name="home" /></span>
                الرئيسية
            </Link>
        </footer>

        <!-- القائمة المنسدلة من الأسفل (هاتف) -->
        <div v-if="menuOpen" class="fixed inset-0 z-40 md:hidden">
            <div class="absolute inset-0 bg-black/10" @click="menuOpen = false"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl p-4 pb-6 shadow-lg" style="background: var(--surface-solid)">
                <div class="grid grid-cols-3 gap-2.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="relative rounded-pill border border-transparent text-center font-semibold shadow-sm transition-[border-color]"
                        :class="isActive(item) ? '' : 'hover:border-brand-400'"
                        :style="isActive(item) ? 'background: var(--color-ink); color: var(--color-paper)' : 'color: var(--text-primary)'"
                        @click="menuOpen = false"
                    >
                        {{ item.label }}
                        <span v-if="item.badge" class="absolute -top-1 -left-1 text-xs font-extrabold" style="color: #dc2626">{{ item.badge }}</span>
                    </Link>
                </div>
                <div class="mt-3 flex items-center justify-center">
                    <button
                        type="button"
                        class="rounded-pill border border-transparent px-4 py-2 text-sm font-semibold text-red-600 transition-[border-color] hover:border-red-400"
                        @click="logout"
                    >
                        تسجيل الخروج
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
