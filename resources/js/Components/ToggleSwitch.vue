<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

function toggle() {
    if (props.disabled) {
        return;
    }

    const nextValue = !props.modelValue;

    emit('update:modelValue', nextValue);
    emit('change', nextValue);
}
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        class="relative inline-flex h-8 w-14 items-center rounded-full border transition disabled:cursor-not-allowed disabled:opacity-60"
        :class="modelValue ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 bg-slate-200'"
        @click="toggle"
    >
        <span
            class="absolute left-1 grid h-6 w-6 place-items-center rounded-full bg-white text-[9px] font-black uppercase text-slate-500 shadow-sm transition"
            :class="modelValue ? 'translate-x-6 text-emerald-600' : 'translate-x-0'"
        >
            {{ modelValue ? 'On' : 'Off' }}
        </span>
    </button>
</template>
