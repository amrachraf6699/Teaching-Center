<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    showUrl: String,
    editUrl: String,
    deleteUrl: String,
    label: {
        type: String,
        default: '',
    },
});

const { t } = useI18n();

function destroy() {
    if (!props.deleteUrl || !window.confirm(t('actions.confirmDelete', { name: props.label }))) {
        return;
    }

    router.delete(props.deleteUrl);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Link
            v-if="showUrl"
            :href="showUrl"
            :title="t('actions.view', { name: label })"
            :aria-label="t('actions.view', { name: label })"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-teachify-line text-teachify-blue transition hover:bg-teachify-blue-soft"
        >
            <Eye class="h-4 w-4" />
        </Link>
        <Link
            v-if="editUrl"
            :href="editUrl"
            :title="t('actions.edit', { name: label })"
            :aria-label="t('actions.edit', { name: label })"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-teachify-line text-teachify-ink transition hover:border-teachify-blue hover:text-teachify-blue"
        >
            <Pencil class="h-4 w-4" />
        </Link>
        <button
            v-if="deleteUrl"
            type="button"
            :title="t('actions.delete', { name: label })"
            :aria-label="t('actions.delete', { name: label })"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-teachify-coral-soft text-teachify-coral transition hover:bg-rose-100"
            @click="destroy"
        >
            <Trash2 class="h-4 w-4" />
        </button>
    </div>
</template>
