<script setup>
defineProps({
    label: String,
    modelValue: Boolean,
    error: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label class="flex items-start gap-3 rounded-2xl border border-teachify-line bg-white px-4 py-3">
        <input
            type="checkbox"
            :checked="modelValue"
            class="mt-1 h-4 w-4 rounded border-teachify-line text-teachify-blue"
            @change="$emit('update:modelValue', $event.target.checked)"
        />
        <span>
            <span class="block text-sm font-bold text-teachify-ink">{{ label }}</span>
            <span v-if="error" class="mt-1 block text-sm font-semibold text-teachify-coral">{{ error }}</span>
        </span>
    </label>
</template>
