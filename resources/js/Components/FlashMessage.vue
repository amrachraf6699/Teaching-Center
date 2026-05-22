<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const status = computed(() => page.props.flash?.status);
</script>

<template>
    <div v-if="status" class="mb-5 rounded-2xl border border-teachify-mint bg-teachify-mint-soft px-4 py-3 text-sm font-semibold text-teachify-ink">
        {{ status }}
    </div>
</template>
