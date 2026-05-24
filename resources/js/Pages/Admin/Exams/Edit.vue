<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Layouts/AppShell.vue';
import ExamForm from './Form.vue';

const props = defineProps({ exam: Object, groups: Array, action: String, showUrl: String, questionTypes: Array });
const form = useForm({
    ...props.exam,
    questions: props.exam.questions.length
        ? props.exam.questions
        : [
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
    form.put(props.action);
}
</script>

<template>
    <Head title="Edit Exam" />
    <AppShell title="Edit Exam">
        <ExamForm
            :form="form"
            :groups="groups"
            :question-types="questionTypes"
            submit-label="Save Exam"
            :cancel-url="showUrl"
            heading="Update exam"
            description="Review the schedule and points, then adjust the questions as needed. Reordering or editing questions updates the exam score automatically."
            @submit="submit"
        />
    </AppShell>
</template>
