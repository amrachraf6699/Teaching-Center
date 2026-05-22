<script setup>
defineProps({
    title: String,
    message: String,
});
</script>

<template>
    <div class="teachify-card rounded-[1.4rem] px-5 py-8 text-center">
        <h2 class="text-lg font-black">{{ title }}</h2>
        <p class="mt-2 text-sm font-medium text-teachify-muted">{{ message }}</p>
        <div class="mx-auto mt-5 h-2 w-20 rounded-full bg-teachify-yellow" />
    </div>
</template>
