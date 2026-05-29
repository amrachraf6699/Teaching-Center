<script setup>
import { Head } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({
    exports: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Exports" />

    <AppShell title="Exports">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-[2rem] border border-teachify-line bg-white p-6 shadow-[0_18px_45px_rgba(37,99,235,0.07)]">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-teachify-blue">Downloads</p>
                <h2 class="mt-2 text-2xl font-black text-teachify-ink">Export center</h2>
                <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-teachify-muted">
                    Existing resource export URLs are preserved. Use the resource pages for filtered exports, or download complete datasets here.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="item in exports"
                    :key="item.label"
                    class="rounded-[1.5rem] border border-teachify-line bg-white p-5 shadow-sm"
                >
                    <h3 class="text-lg font-black text-teachify-ink">{{ item.label }}</h3>
                    <p class="mt-2 text-sm font-medium leading-6 text-teachify-muted">{{ item.description }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a :href="item.csv_url" class="inline-flex items-center gap-2 rounded-2xl border border-teachify-line px-3 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
                            <Download class="h-4 w-4" />
                            CSV
                        </a>
                        <a :href="item.pdf_url" class="inline-flex items-center gap-2 rounded-2xl border border-teachify-line px-3 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
                            <Download class="h-4 w-4" />
                            PDF
                        </a>
                    </div>
                </article>
            </section>
        </div>
    </AppShell>
</template>
