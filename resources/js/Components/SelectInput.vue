<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    label: String,
    modelValue: [String, Number],
    error: String,
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);

const { t } = useI18n();
const placeholderLabel = computed(() => props.placeholder || t('forms.selectOption'));
</script>

<template>
    <label class="block">
        <span class="text-sm font-bold text-teachify-ink">{{ label }}</span>
        <select
            :value="modelValue"
            :required="required"
            class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium outline-none transition focus:border-teachify-blue focus:ring-4 focus:ring-teachify-blue-soft"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="">{{ placeholderLabel }}</option>
            <option v-for="option in options" :key="option.value ?? option.id" :value="option.value ?? option.id">
                {{ option.label ?? option.name }}
            </option>
        </select>
        <span v-if="error" class="mt-1 block text-sm font-semibold text-teachify-coral">{{ error }}</span>
    </label>
</template>
