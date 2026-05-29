<script setup>
import { Head, Link } from '@inertiajs/vue3';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({
    batch: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Import #${batch.id}`" />

    <AppShell :title="`Import #${batch.id}`">
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-teachify-blue">Import batch</p>
                    <h2 class="mt-2 text-2xl font-black text-teachify-ink">{{ batch.label }}</h2>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ batch.file_name }} · {{ batch.created_at }}</p>
                </div>

                <Link :href="$page.props.routes.adminImports" class="rounded-2xl border border-teachify-line px-4 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
                    Back to imports
                </Link>
            </div>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">Status</p>
                    <p class="mt-2 text-xl font-black capitalize">{{ batch.status.replaceAll('_', ' ') }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">Rows</p>
                    <p class="mt-2 text-xl font-black">{{ batch.total_rows }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">Imported</p>
                    <p class="mt-2 text-xl font-black text-green-700">{{ batch.imported_rows }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">Failed</p>
                    <p class="mt-2 text-xl font-black text-red-700">{{ batch.failed_rows }}</p>
                </div>
            </section>

            <section class="rounded-[2rem] border border-teachify-line bg-white p-6 shadow-sm">
                <h3 class="text-xl font-black text-teachify-ink">Row errors</h3>

                <div v-if="batch.errors?.length" class="mt-5 overflow-hidden rounded-2xl border border-teachify-line">
                    <table class="min-w-full divide-y divide-teachify-line text-sm">
                        <thead class="bg-teachify-blue-soft/50 text-left text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">
                            <tr>
                                <th class="px-4 py-3">Row</th>
                                <th class="px-4 py-3">Field</th>
                                <th class="px-4 py-3">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teachify-line bg-white">
                            <tr v-for="(error, index) in batch.errors" :key="`${error.row}-${index}`">
                                <td class="px-4 py-3 font-bold">{{ error.row ?? 'File' }}</td>
                                <td class="px-4 py-3">{{ error.field ?? '-' }}</td>
                                <td class="px-4 py-3 text-red-700">{{ error.message }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState
                    v-else
                    title="No failed rows"
                    description="Every row in this import batch was processed successfully."
                />
            </section>
        </div>
    </AppShell>
</template>
