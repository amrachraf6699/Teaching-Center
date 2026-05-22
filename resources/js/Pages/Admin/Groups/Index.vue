<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ groups: Object, createUrl: String });

const columns = [
    { key: 'name', label: 'Group' },
    { key: 'subject', label: 'Subject' },
    { key: 'level', label: 'Level' },
    { key: 'students_count', label: 'Students' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions' },
];
</script>

<template>
    <Head title="Groups" />
    <AppShell title="Groups" subtitle="Class groups and enrollment counts.">
        <div class="mb-4 flex justify-end"><Button :href="createUrl">Add Group</Button></div>
        <DataTable v-if="groups.data.length" :columns="columns" :rows="groups.data">
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.name" />
            </template>
        </DataTable>
        <EmptyState v-else title="No groups yet" message="Create a group and enroll students into it." />
        <Pagination :links="groups.links" />
    </AppShell>
</template>
