<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ exam: Object, groups: Array, action: String, showUrl: String });
const form = useForm({ ...props.exam });

function submit() {
    form.put(props.action);
}
</script>

<template>
    <Head title="Edit Exam" />
    <AppShell title="Edit Exam">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SelectInput v-model="form.teaching_group_id" label="Group" required :options="groups" :error="form.errors.teaching_group_id" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.title" label="Title" required :error="form.errors.title" />
                <TextInput v-model="form.exam_date" label="Exam Date" type="date" required :error="form.errors.exam_date" />
                <TextInput v-model="form.max_score" label="Max Score" type="number" required :error="form.errors.max_score" />
            </div>
            <TextareaInput v-model="form.notes" label="Notes" :error="form.errors.notes" />
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">Save Exam</Button>
                <Button variant="secondary" :href="showUrl">Cancel</Button>
            </div>
        </form>
    </AppShell>
</template>
