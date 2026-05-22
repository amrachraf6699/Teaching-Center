<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({
    parents: Object,
    createUrl: String,
});

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'children_count', label: 'Children' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions' },
];
</script>

<template>
    <Head title="Parents" />
    <AppShell title="Parents" subtitle="Parent accounts with access to the portal.">
        <div class="mb-4 flex justify-end">
            <Button :href="createUrl">Add Parent</Button>
        </div>
        <DataTable v-if="parents.data.length" :columns="columns" :rows="parents.data">
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.name" />
            </template>
        </DataTable>
        <EmptyState v-else title="No parents yet" message="Create the first parent account to connect students." />
        <Pagination :links="parents.links" />
    </AppShell>
</template>
