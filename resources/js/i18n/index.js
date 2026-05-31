import { createI18n } from 'vue-i18n';
import messages from './messages';

export const supportedLocales = ['en', 'ar'];

export function normalizeLocale(locale) {
    return supportedLocales.includes(locale) ? locale : 'en';
}

export function localeDirection(locale) {
    return normalizeLocale(locale) === 'ar' ? 'rtl' : 'ltr';
}

export function applyDocumentLocale(locale) {
    const normalized = normalizeLocale(locale);

    document.documentElement.lang = normalized;
    document.documentElement.dir = localeDirection(normalized);
}

export function createTeachifyI18n(locale) {
    return createI18n({
        legacy: false,
        globalInjection: true,
        locale: normalizeLocale(locale),
        fallbackLocale: 'en',
        messages,
    });
}
