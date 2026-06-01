<script setup>
import { useForm } from '@inertiajs/vue3';
import { Download, UploadCloud } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import SelectInput from './SelectInput.vue';

const props = defineProps({
    imports: {
        type: Array,
        default: () => [],
    },
});

const { t } = useI18n();
const selectedType = ref(props.imports[0]?.value ?? '');
const form = useForm({
    file: null,
});

const localizedImports = computed(() => props.imports.map((item) => ({
    ...item,
    label: t(`admin.imports.types.${item.value}`),
})));
const selectedImport = computed(() => props.imports.find((item) => item.value === selectedType.value));
const fileInputId = computed(() => `import-file-${selectedType.value || 'default'}`);

function submit() {
    if (!selectedImport.value || !form.file) {
        return;
    }

    form.post(selectedImport.value.store_url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            const input = document.getElementById(fileInputId.value);
            if (input) {
                input.value = '';
            }
        },
    });
}
</script>

<template>
    <form v-if="imports.length" class="rounded-2xl border border-teachify-line bg-white/80 p-3 shadow-sm" @submit.prevent="submit">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)_auto] lg:items-end">
            <SelectInput
                v-model="selectedType"
                :label="t('admin.imports.import')"
                :options="localizedImports"
                required
            />

            <label class="block">
                <span class="text-sm font-bold text-teachify-ink">{{ t('fields.file') }}</span>
                <input
                    :id="fileInputId"
                    type="file"
                    accept=".csv,.xlsx,.xls"
                    class="mt-2 block min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 py-2 text-sm font-semibold text-teachify-ink file:mr-4 file:rounded-xl file:border-0 file:bg-teachify-blue-soft file:px-3 file:py-2 file:text-sm file:font-bold file:text-teachify-blue"
                    required
                    @input="form.file = $event.target.files[0]"
                />
                <span v-if="form.errors.file" class="mt-1 block text-sm font-semibold text-teachify-coral">{{ form.errors.file }}</span>
            </label>

            <div class="flex flex-wrap gap-2">
                <a
                    v-if="selectedImport"
                    :href="selectedImport.template_csv_url"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-teachify-line bg-white px-4 py-2.5 text-sm font-bold text-teachify-ink shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
                >
                    <Download class="h-4 w-4" />
                    {{ t('actions.template') }}
                </a>
                <Button type="submit" :disabled="form.processing">
                    <UploadCloud class="h-4 w-4" />
                    {{ t('actions.upload') }}
                </Button>
            </div>
        </div>
    </form>
</template>
