<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { applyDocumentLocale, localeDirection, normalizeLocale } from '../i18n';

const page = usePage();
const { locale, t } = useI18n({ useScope: 'global' });

const currentLocale = computed(() => normalizeLocale(locale.value || page.props.locale));
const nextLocale = computed(() => (currentLocale.value === 'ar' ? 'en' : 'ar'));
const nextLabel = computed(() => (nextLocale.value === 'ar' ? 'AR' : 'EN'));

function toggleLanguage() {
    const next = nextLocale.value;

    locale.value = next;
    window.localStorage.setItem('teachify.locale', next);
    applyDocumentLocale(next);

    const route = page.props.routes?.localeSwitch;
    if (route) {
        router.post(route, { locale: next }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }
}
</script>

<template>
    <button
        type="button"
        class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-teachify-line bg-white px-3 text-sm font-black uppercase text-teachify-muted shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
        :aria-label="t('common.languageToggle')"
        :title="t('common.languageToggle')"
        :dir="localeDirection(currentLocale)"
        @click="toggleLanguage"
    >
        <Languages class="h-5 w-5" />
        <span>{{ nextLabel }}</span>
    </button>
</template>
