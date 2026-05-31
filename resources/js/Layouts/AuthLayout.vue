<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import LanguageToggle from '../Components/LanguageToggle.vue';

const page = usePage();
const brand = computed(() => page.props.brand ?? { name: 'Teachify' });
</script>

<template>
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <div class="fixed right-4 top-4 z-20">
            <LanguageToggle />
        </div>

        <section class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="place-items-center font-black mb-2">
                    <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="max-h-12 w-auto rounded-xl" />
                    <span v-else>T</span>
                </div>
                <h1 class="text-3xl font-black tracking-normal">{{ brand.name }}</h1>
                <p class="mt-2 text-sm font-medium text-teachify-muted">{{ brand.tagline || $t('brand.defaultTagline') }}</p>
            </div>

            <slot />
        </section>
    </main>
</template>
