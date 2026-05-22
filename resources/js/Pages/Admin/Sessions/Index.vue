<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ sessions: Object, createUrl: String });

const columns = [
    { key: 'title', label: 'Session' },
    { key: 'group', label: 'Group' },
    { key: 'starts_at', label: 'Starts' },
    { key: 'ends_at', label: 'Ends' },
    { key: 'actions', label: 'Actions' },
];
</script>

<template>
    <Head title="Sessions" />
    <AppShell title="Sessions" subtitle="Scheduled lessons and attendance anchors.">
        <div class="mb-4 flex justify-end"><Button :href="createUrl">Add Session</Button></div>
        <DataTable v-if="sessions.data.length" :columns="columns" :rows="sessions.data">
            <template #group="{ row }">{{ row.group?.name || '-' }}</template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.title" />
            </template>
        </DataTable>
        <EmptyState v-else title="No sessions yet" message="Schedule lessons before recording attendance." />
        <Pagination :links="sessions.links" />
    </AppShell>
</template>
