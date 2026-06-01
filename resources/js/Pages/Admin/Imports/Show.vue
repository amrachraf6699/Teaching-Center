<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    batch: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
const title = computed(() => t('admin.imports.batchTitle', { id: props.batch.id }));
</script>

<template>
    <Head :title="title" />

    <AppShell :title="title">
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-teachify-blue">{{ $t('admin.imports.batchLabel') }}</p>
                    <h2 class="mt-2 text-2xl font-black text-teachify-ink">{{ batch.label }}</h2>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ batch.file_name }} · {{ batch.created_at }}</p>
                </div>

                <button
                    type="button"
                    class="rounded-2xl border border-teachify-line px-4 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue"
                    @click="window.history.back()"
                >
                    {{ $t('actions.back') }}
                </button>
            </div>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.status') }}</p>
                    <p class="mt-2 text-xl font-black capitalize">{{ batch.status.replaceAll('_', ' ') }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.rows') }}</p>
                    <p class="mt-2 text-xl font-black">{{ batch.total_rows }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.imported') }}</p>
                    <p class="mt-2 text-xl font-black text-green-700">{{ batch.imported_rows }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.failed') }}</p>
                    <p class="mt-2 text-xl font-black text-red-700">{{ batch.failed_rows }}</p>
                </div>
            </section>

            <section class="rounded-[2rem] border border-teachify-line bg-white p-6 shadow-sm">
                <h3 class="text-xl font-black text-teachify-ink">{{ $t('admin.imports.rowErrors') }}</h3>

                <div v-if="batch.errors?.length" class="mt-5 overflow-hidden rounded-2xl border border-teachify-line">
                    <table class="min-w-full divide-y divide-teachify-line text-sm">
                        <thead class="bg-teachify-blue-soft/50 text-left text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">
                            <tr>
                                <th class="px-4 py-3">{{ $t('fields.row') }}</th>
                                <th class="px-4 py-3">{{ $t('fields.field') }}</th>
                                <th class="px-4 py-3">{{ $t('fields.message') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teachify-line bg-white">
                            <tr v-for="(error, index) in batch.errors" :key="`${error.row}-${index}`">
                                <td class="px-4 py-3 font-bold">{{ error.row ?? $t('fields.file') }}</td>
                                <td class="px-4 py-3">{{ error.field ?? $t('common.noData') }}</td>
                                <td class="px-4 py-3 text-red-700">{{ error.message }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState
                    v-else
                    :title="$t('admin.imports.noFailedRows')"
                    :message="$t('admin.imports.noFailedRowsMessage')"
                />
            </section>
        </div>
    </AppShell>
</template>
