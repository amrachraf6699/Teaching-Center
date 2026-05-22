<script setup>
defineProps({
    label: String,
    error: String,
    accept: {
        type: String,
        default: 'image/*',
    },
});

defineEmits(['change']);
</script>

<template>
    <label class="block">
        <span class="text-sm font-bold text-teachify-ink">{{ label }}</span>
        <input
            type="file"
            :accept="accept"
            class="mt-2 block min-h-12 w-full rounded-2xl border border-dashed border-teachify-line bg-white px-4 py-3 text-sm font-medium file:mr-4 file:rounded-xl file:border-0 file:bg-teachify-blue-soft file:px-3 file:py-2 file:text-sm file:font-bold file:text-teachify-blue"
            @change="$emit('change', $event.target.files[0] ?? null)"
        />
        <span v-if="error" class="mt-1 block text-sm font-semibold text-teachify-coral">{{ error }}</span>
    </label>
</template>
