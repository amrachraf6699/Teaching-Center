<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ parents: Array, action: String });
const form = useForm({ parent_id: '', name: '', phone: '', date_of_birth: '', notes: '', password: '' });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head :title="$t('admin.students.add')" />
    <AppShell :title="$t('admin.students.add')">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SelectInput v-model="form.parent_id" :label="$t('fields.parent')" required :options="parents" :error="form.errors.parent_id" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.name" :label="$t('fields.name')" required :error="form.errors.name" />
                <TextInput v-model="form.phone" :label="$t('fields.phone')" :error="form.errors.phone" />
                <TextInput v-model="form.date_of_birth" :label="$t('fields.dateOfBirth')" type="date" :error="form.errors.date_of_birth" />
            </div>
            <TextInput v-model="form.password" :label="$t('fields.studentPassword')" type="password" :error="form.errors.password" />
            <p class="text-sm font-medium text-teachify-muted">{{ $t('admin.students.codePasswordHelp') }}</p>
            <TextareaInput v-model="form.notes" :label="$t('fields.notes')" :error="form.errors.notes" />
            <Button type="submit" :disabled="form.processing">{{ $t('admin.students.create') }}</Button>
        </form>
    </AppShell>
</template>
