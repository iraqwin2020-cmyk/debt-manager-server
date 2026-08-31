import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createAppI18n, applyDirection } from '@/i18n';

createInertiaApp({
    title: (title) => (title ? `${title} — مدير الديون` : 'مدير الديون'),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const initialLocale =
            props.initialPage.props.auth?.tenant?.locale ??
            props.initialPage.props.auth?.user?.locale ??
            localStorage.getItem('locale') ??
            undefined;
        const i18n = createAppI18n(initialLocale);

        router.on('success', (event) => {
            const locale =
                event.detail.page.props.auth?.tenant?.locale ??
                event.detail.page.props.auth?.user?.locale ??
                localStorage.getItem('locale') ??
                'ar';
            if (i18n.global.locale.value !== locale) {
                i18n.global.locale.value = locale;
                applyDirection(locale);
            }
        });

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#e8600c',
    },
});

if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js');
    });
}
