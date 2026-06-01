<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppShell from '../../../Layouts/AppShell.vue';
import ExamForm from './Form.vue';

const props = defineProps({ exam: Object, groups: Array, action: String, showUrl: String, questionTypes: Array, reviewModes: Array });
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
    <Head :title="$t('admin.exams.edit')" />
    <AppShell :title="$t('admin.exams.edit')">
        <ExamForm
            :form="form"
            :groups="groups"
            :question-types="questionTypes"
            :review-modes="reviewModes"
            :submit-label="$t('admin.exams.save')"
            :cancel-url="showUrl"
            :heading="$t('admin.exams.editHeading')"
            :description="$t('admin.exams.editDescription')"
            @submit="submit"
        />
    </AppShell>
</template>
