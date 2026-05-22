<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ groups: Array, action: String });
const form = useForm({ teaching_group_id: '', title: '', starts_at: '', ends_at: '', notes: '' });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head title="Add Session" />
    <AppShell title="Add Session" subtitle="Plan the next lesson for a group.">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SelectInput v-model="form.teaching_group_id" label="Group" required :options="groups" :error="form.errors.teaching_group_id" />
            <TextInput v-model="form.title" label="Title" required :error="form.errors.title" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.starts_at" label="Starts At" type="datetime-local" required :error="form.errors.starts_at" />
                <TextInput v-model="form.ends_at" label="Ends At" type="datetime-local" :error="form.errors.ends_at" />
            </div>
            <TextareaInput v-model="form.notes" label="Notes" :error="form.errors.notes" />
            <Button type="submit" :disabled="form.processing">Create Session</Button>
        </form>
    </AppShell>
</template>
