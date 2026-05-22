<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';
</script>

<template>
    <Head title="Imports and Exports" />
    <AppShell title="Imports and Exports" subtitle="Excel and PDF workflows will live here as the data screens mature.">
        <EmptyState title="Imports and exports are reserved" message="This module is ready for the upcoming Excel import/export and PDF report workflows." />
    </AppShell>
</template>
