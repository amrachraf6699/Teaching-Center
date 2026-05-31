import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { applyDocumentLocale, createTeachifyI18n, normalizeLocale } from './i18n';

const appName = document.documentElement.dataset.appName || document.title || 'Teachify';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const serverLocale = normalizeLocale(props.initialPage.props.locale);
        const storedLocale = normalizeLocale(window.localStorage.getItem('teachify.locale') || serverLocale);
        const i18n = createTeachifyI18n(storedLocale);

        applyDocumentLocale(storedLocale);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);

        const localeRoute = props.initialPage.props.routes?.localeSwitch;
        if (storedLocale !== serverLocale && localeRoute) {
            router.post(localeRoute, { locale: storedLocale }, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        }
    },
    progress: {
        color: '#2563eb',
    },
});
