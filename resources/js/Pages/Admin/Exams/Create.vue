<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Layouts/AppShell.vue';
import ExamForm from './Form.vue';

const props = defineProps({ groups: Array, action: String, questionTypes: Array });

const form = useForm({
    teaching_group_id: '',
    title: '',
    start_at: '',
    end_at: '',
    max_allowed_time: 60,
    notes: '',
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
    <Head title="Add Exam" />
    <AppShell title="Add Exam">
        <ExamForm
            :form="form"
            :groups="groups"
            :question-types="questionTypes"
            submit-label="Create Exam"
            heading="Create a new exam"
            description="Fill in the exam details first, then build the questions underneath. The total score is calculated from the points you assign."
            @submit="submit"
        />
    </AppShell>
</template>
