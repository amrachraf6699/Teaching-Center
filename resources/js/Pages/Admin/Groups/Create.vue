<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ students: Array, action: String });
const form = useForm({ name: '', subject: '', level: '', description: '', student_ids: [] });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head title="Add Group" />
    <AppShell title="Add Group">
        <form class="teachify-card max-w-3xl space-y-5 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.name" label="Name" required :error="form.errors.name" />
                <TextInput v-model="form.subject" label="Subject" :error="form.errors.subject" />
                <TextInput v-model="form.level" label="Level" :error="form.errors.level" />
            </div>
            <TextareaInput v-model="form.description" label="Description" :error="form.errors.description" />

            <section>
                <h2 class="text-sm font-black">Students</h2>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    <label v-for="student in students" :key="student.id" class="flex items-center gap-3 rounded-2xl border border-teachify-line bg-white px-4 py-3 text-sm font-bold">
                        <input v-model="form.student_ids" type="checkbox" :value="student.id" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                        <span>{{ student.name }} <span class="text-teachify-muted">{{ student.code }}</span></span>
                    </label>
                </div>
                <p v-if="form.errors.student_ids" class="mt-2 text-sm font-semibold text-teachify-coral">{{ form.errors.student_ids }}</p>
            </section>

            <Button type="submit" :disabled="form.processing">Create Group</Button>
        </form>
    </AppShell>
</template>
