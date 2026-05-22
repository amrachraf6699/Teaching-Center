<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ exams: Object, createUrl: String });

const columns = [
    { key: 'title', label: 'Exam' },
    { key: 'group', label: 'Group' },
    { key: 'exam_date', label: 'Date' },
    { key: 'max_score', label: 'Max Score' },
    { key: 'actions', label: 'Actions' },
];
</script>

<template>
    <Head title="Exams" />
    <AppShell title="Exams" subtitle="Group exams and grade entry foundations.">
        <div class="mb-4 flex justify-end"><Button :href="createUrl">Add Exam</Button></div>
        <DataTable v-if="exams.data.length" :columns="columns" :rows="exams.data">
            <template #group="{ row }">{{ row.group?.name || '-' }}</template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.title" />
            </template>
        </DataTable>
        <EmptyState v-else title="No exams yet" message="Create group exams before entering grades." />
        <Pagination :links="exams.links" />
    </AppShell>
</template>
