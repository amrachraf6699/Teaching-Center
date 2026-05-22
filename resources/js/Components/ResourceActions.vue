<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    showUrl: String,
    editUrl: String,
    deleteUrl: String,
    label: {
        type: String,
        default: 'record',
    },
});

function destroy() {
    if (!props.deleteUrl || !window.confirm(`Delete ${props.label}?`)) {
        return;
    }

    router.delete(props.deleteUrl);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Link v-if="showUrl" :href="showUrl" class="rounded-xl border border-teachify-line px-3 py-1.5 text-xs font-black text-teachify-blue hover:bg-teachify-blue-soft">
            Show
        </Link>
        <Link v-if="editUrl" :href="editUrl" class="rounded-xl border border-teachify-line px-3 py-1.5 text-xs font-black text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue">
            Edit
        </Link>
        <button v-if="deleteUrl" type="button" class="rounded-xl bg-teachify-coral-soft px-3 py-1.5 text-xs font-black text-teachify-coral hover:bg-rose-100" @click="destroy">
            Delete
        </button>
    </div>
</template>
