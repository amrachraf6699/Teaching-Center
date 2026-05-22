<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav v-if="links.length > 3" class="mt-5 flex flex-wrap gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="link in links"
            :key="link.label"
            :href="link.url"
            class="rounded-xl border px-3 py-2 text-sm font-bold"
            :class="[
                link.active ? 'border-teachify-blue bg-teachify-blue text-white' : 'border-teachify-line bg-white text-teachify-muted',
                !link.url ? 'opacity-45' : 'hover:border-teachify-blue hover:text-teachify-blue',
            ]"
            v-html="link.label"
        />
    </nav>
</template>
