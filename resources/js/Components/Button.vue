<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="href ? undefined : type"
        :disabled="disabled"
        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-60"
        :class="{
            'bg-teachify-blue text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] hover:bg-blue-700': variant === 'primary',
            'border border-teachify-line bg-white text-teachify-ink hover:border-teachify-blue hover:text-teachify-blue': variant === 'secondary',
            'bg-teachify-coral-soft text-teachify-coral hover:bg-rose-100': variant === 'danger',
        }"
    >
        <slot />
    </component>
</template>
