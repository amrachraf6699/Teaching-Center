<script setup>
defineProps({
    label: String,
    value: [String, Number],
    tone: {
        type: String,
        default: 'blue',
    },
});
</script>

<template>
    <article class="teachify-card rounded-[1.4rem] p-4">
        <div
            class="mb-4 h-2 w-12 rounded-full"
            :class="{
                'bg-teachify-blue': tone === 'blue',
                'bg-teachify-mint': tone === 'mint',
                'bg-teachify-yellow': tone === 'yellow',
                'bg-teachify-coral': tone === 'coral',
            }"
        />
        <div class="text-3xl font-black">{{ value }}</div>
        <div class="mt-1 text-sm font-bold text-teachify-muted">{{ label }}</div>
    </article>
</template>
