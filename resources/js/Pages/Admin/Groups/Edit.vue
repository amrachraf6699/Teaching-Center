<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ group: Object, students: Array, action: String, showUrl: String });
const form = useForm({
    name: props.group.name,
    subject: props.group.subject,
    level: props.group.level,
    description: props.group.description,
    is_active: props.group.is_active,
    student_ids: props.group.student_ids,
});

function submit() {
    form.put(props.action);
}
</script>

<template>
    <Head title="Edit Group" />
    <AppShell title="Edit Group" subtitle="Update group details and enrollment.">
        <form class="teachify-card max-w-3xl space-y-5 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.name" label="Name" required :error="form.errors.name" />
                <TextInput v-model="form.subject" label="Subject" :error="form.errors.subject" />
                <TextInput v-model="form.level" label="Level" :error="form.errors.level" />
            </div>
            <label class="flex items-center gap-3 text-sm font-bold text-teachify-muted">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                Active
            </label>
            <TextareaInput v-model="form.description" label="Description" :error="form.errors.description" />
            <section>
                <h2 class="text-sm font-black">Students</h2>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    <label v-for="student in students" :key="student.id" class="flex items-center gap-3 rounded-2xl border border-teachify-line bg-white px-4 py-3 text-sm font-bold">
                        <input v-model="form.student_ids" type="checkbox" :value="student.id" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                        <span>{{ student.name }} <span class="text-teachify-muted">{{ student.code }}</span></span>
                    </label>
                </div>
            </section>
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">Save Group</Button>
                <Button variant="secondary" :href="showUrl">Cancel</Button>
            </div>
        </form>
    </AppShell>
</template>
