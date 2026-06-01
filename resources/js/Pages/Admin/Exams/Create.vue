<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Layouts/AppShell.vue';
import ExamForm from './Form.vue';

const props = defineProps({ groups: Array, action: String, questionTypes: Array, reviewModes: Array });

const form = useForm({
    teaching_group_id: '',
    title: '',
    start_at: '',
    end_at: '',
    max_allowed_time: 60,
    notes: '',
    student_review_mode: 'score_only',
    questions: [
        {
            type: 'true_false',
            prompt: '',
            points: 1,
            correct_boolean: 'true',
            options: [],
        },
    ],
});

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head :title="$t('admin.exams.add')" />
    <AppShell :title="$t('admin.exams.add')">
        <ExamForm
            :form="form"
            :groups="groups"
            :question-types="questionTypes"
            :review-modes="reviewModes"
            :submit-label="$t('admin.exams.create')"
            :heading="$t('admin.exams.createHeading')"
            :description="$t('admin.exams.createDescription')"
            @submit="submit"
        />
    </AppShell>
</template>
