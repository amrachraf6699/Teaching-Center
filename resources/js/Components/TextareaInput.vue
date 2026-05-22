<script setup>
defineProps({
    label: String,
    modelValue: String,
    error: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label class="block">
        <span class="text-sm font-bold text-teachify-ink">{{ label }}</span>
        <textarea
            :value="modelValue"
            rows="4"
            class="mt-2 w-full rounded-2xl border border-teachify-line bg-white px-4 py-3 text-sm font-medium outline-none transition focus:border-teachify-blue focus:ring-4 focus:ring-teachify-blue-soft"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <span v-if="error" class="mt-1 block text-sm font-semibold text-teachify-coral">{{ error }}</span>
    </label>
</template>
