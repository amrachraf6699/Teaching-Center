<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ students: Object, createUrl: String });

const columns = [
    { key: 'name', label: 'Student' },
    { key: 'code', label: 'Code' },
    { key: 'parent', label: 'Parent' },
    { key: 'phone', label: 'Phone' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions' },
];
</script>

<template>
    <Head title="Students" />
    <AppShell title="Students" subtitle="Student records and parent links.">
        <div class="mb-4 flex justify-end"><Button :href="createUrl">Add Student</Button></div>
        <DataTable v-if="students.data.length" :columns="columns" :rows="students.data">
            <template #parent="{ row }">{{ row.parent?.name || '-' }}</template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.name" />
            </template>
        </DataTable>
        <EmptyState v-else title="No students yet" message="Add students and connect them to parent accounts." />
        <Pagination :links="students.links" />
    </AppShell>
</template>
