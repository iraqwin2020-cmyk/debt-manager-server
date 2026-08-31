import { createI18n } from 'vue-i18n';
import ar from '@/locales/ar';
import en from '@/locales/en';
import ku from '@/locales/ku';

export const RTL_LOCALES = ['ar', 'ku'];

export function applyDirection(locale) {
    const dir = RTL_LOCALES.includes(locale) ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('dir', dir);
    document.documentElement.setAttribute('lang', locale);
}

export function createAppI18n(initialLocale) {
    const locale = initialLocale ?? 'ar';

    const i18n = createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'ar',
        messages: { ar, en, ku },
    });

    applyDirection(locale);

    return i18n;
}
