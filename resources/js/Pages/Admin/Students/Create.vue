<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ parents: Array, action: String });
const form = useForm({ parent_id: '', name: '', code: '', phone: '', date_of_birth: '', notes: '' });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head title="Add Student" />
    <AppShell title="Add Student">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SelectInput v-model="form.parent_id" label="Parent" required :options="parents" :error="form.errors.parent_id" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.name" label="Name" required :error="form.errors.name" />
                <TextInput v-model="form.code" label="Code" :error="form.errors.code" />
                <TextInput v-model="form.phone" label="Phone" :error="form.errors.phone" />
                <TextInput v-model="form.date_of_birth" label="Date of Birth" type="date" :error="form.errors.date_of_birth" />
            </div>
            <TextareaInput v-model="form.notes" label="Notes" :error="form.errors.notes" />
            <Button type="submit" :disabled="form.processing">Create Student</Button>
        </form>
    </AppShell>
</template>
