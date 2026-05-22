<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brand = computed(() => page.props.brand ?? { name: 'Teachify' });
</script>

<template>
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-[1.4rem] bg-teachify-blue text-2xl font-black text-white shadow-[0_18px_35px_rgba(37,99,235,0.25)]">
                    <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="max-h-12 w-auto rounded-xl" />
                    <span v-else>T</span>
                </div>
                <h1 class="text-3xl font-black tracking-normal">{{ brand.name }}</h1>
                <p class="mt-2 text-sm font-medium text-teachify-muted">{{ brand.tagline || 'Private teaching management for every class moment.' }}</p>
            </div>

            <slot />
        </section>
    </main>
</template>
