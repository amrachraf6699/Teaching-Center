<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileDown, UploadCloud } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    importTypes: {
        type: Array,
        default: () => [],
    },
    batches: {
        type: Array,
        default: () => [],
    },
});

const selectedType = ref(props.importTypes[0]?.value ?? '');
const form = useForm({
    file: null,
});

const selectedImport = computed(() => props.importTypes.find((type) => type.value === selectedType.value));

function submit() {
    if (!selectedImport.value) {
        return;
    }

    form.post(selectedImport.value.store_url, {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            const input = document.getElementById('import-file');
            if (input) {
                input.value = '';
            }
        },
    });
}
</script>

<template>
    <Head title="Imports" />

    <AppShell title="Imports">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-[2rem] border border-teachify-line bg-white p-6 shadow-[0_18px_45px_rgba(37,99,235,0.07)]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-teachify-blue">CSV / XLSX</p>
                        <h2 class="mt-2 text-2xl font-black text-teachify-ink">Import workspace</h2>
                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-teachify-muted">
                            Upload teacher-managed data with row-level validation. Successful rows are committed and failed rows are reported in the import batch.
                        </p>
                    </div>
                </div>

                <form class="mt-6 grid gap-4 lg:grid-cols-[1fr_1fr_auto]" @submit.prevent="submit">
                    <SelectInput
                        v-model="selectedType"
                        label="Import type"
                        :options="importTypes"
                        required
                    />

                    <label class="block">
                        <span class="mb-1 block text-sm font-bold text-teachify-ink">File</span>
                        <input
                            id="import-file"
                            type="file"
                            accept=".csv,.xlsx,.xls"
                            class="block min-h-11 w-full rounded-2xl border border-teachify-line bg-white px-4 py-2.5 text-sm font-semibold text-teachify-ink file:mr-4 file:rounded-xl file:border-0 file:bg-teachify-blue-soft file:px-3 file:py-2 file:text-sm file:font-bold file:text-teachify-blue"
                            required
                            @input="form.file = $event.target.files[0]"
                        />
                        <span v-if="form.errors.file" class="mt-1 block text-sm font-semibold text-red-600">{{ form.errors.file }}</span>
                    </label>

                    <Button type="submit" class="self-end" :disabled="form.processing">
                        <UploadCloud class="h-4 w-4" />
                        Upload
                    </Button>
                </form>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="type in importTypes"
                    :key="type.value"
                    class="rounded-[1.5rem] border border-teachify-line bg-white p-5 shadow-sm"
                >
                    <h3 class="text-lg font-black text-teachify-ink">{{ type.label }}</h3>
                    <p class="mt-2 text-sm font-medium leading-6 text-teachify-muted">
                        Download a header template before uploading this import type.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a :href="type.template_csv_url" class="inline-flex items-center gap-2 rounded-2xl border border-teachify-line px-3 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
                            <FileDown class="h-4 w-4" />
                            CSV
                        </a>
                        <a :href="type.template_xlsx_url" class="inline-flex items-center gap-2 rounded-2xl border border-teachify-line px-3 py-2 text-sm font-bold text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
                            <FileDown class="h-4 w-4" />
                            XLSX
                        </a>
                    </div>
                </article>
            </section>

            <section class="rounded-[2rem] border border-teachify-line bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-black text-teachify-ink">Recent imports</h2>
                </div>

                <div v-if="batches.length" class="mt-5 overflow-hidden rounded-2xl border border-teachify-line">
                    <table class="min-w-full divide-y divide-teachify-line text-sm">
                        <thead class="bg-teachify-blue-soft/50 text-left text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">
                            <tr>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">File</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Rows</th>
                                <th class="px-4 py-3">Imported</th>
                                <th class="px-4 py-3">Failed</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teachify-line bg-white">
                            <tr v-for="batch in batches" :key="batch.id">
                                <td class="px-4 py-3 font-bold">{{ batch.label }}</td>
                                <td class="px-4 py-3 text-teachify-muted">{{ batch.file_name }}</td>
                                <td class="px-4 py-3 font-bold capitalize">{{ batch.status.replaceAll('_', ' ') }}</td>
                                <td class="px-4 py-3">{{ batch.total_rows }}</td>
                                <td class="px-4 py-3 text-green-700">{{ batch.imported_rows }}</td>
                                <td class="px-4 py-3 text-red-700">{{ batch.failed_rows }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="batch.show_url" class="font-bold text-teachify-blue hover:underline">Details</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <EmptyState
                    v-else
                    title="No imports yet"
                    description="Upload a CSV or Excel file to create the first import batch."
                />
            </section>
        </div>
    </AppShell>
</template>
