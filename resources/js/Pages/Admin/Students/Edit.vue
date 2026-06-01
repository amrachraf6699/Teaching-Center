<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ student: Object, parents: Array, action: String, showUrl: String });
const form = useForm({
    parent_id: props.student.parent_id,
    name: props.student.name,
    phone: props.student.phone,
    date_of_birth: props.student.date_of_birth,
    notes: props.student.notes,
    is_active: props.student.is_active,
    password: '',
});

function submit() {
    form.put(props.action);
}
</script>

<template>
    <Head :title="$t('admin.students.edit')" />
    <AppShell :title="$t('admin.students.edit')">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SelectInput v-model="form.parent_id" :label="$t('fields.parent')" required :options="parents" :error="form.errors.parent_id" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.name" :label="$t('fields.name')" required :error="form.errors.name" />
                <TextInput v-model="form.phone" :label="$t('fields.phone')" :error="form.errors.phone" />
                <TextInput v-model="form.date_of_birth" :label="$t('fields.dateOfBirth')" type="date" :error="form.errors.date_of_birth" />
            </div>
            <TextInput v-model="form.password" :label="$t('fields.resetStudentPassword')" type="password" :error="form.errors.password" />
            <p class="text-sm font-medium text-teachify-muted">{{ $t('admin.students.keepPasswordHelp') }}</p>
            <label class="flex items-center gap-3 text-sm font-bold text-teachify-muted">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                {{ $t('common.active') }}
            </label>
            <TextareaInput v-model="form.notes" :label="$t('fields.notes')" :error="form.errors.notes" />
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">{{ $t('admin.students.save') }}</Button>
                <Button variant="secondary" :href="showUrl">{{ $t('actions.cancel') }}</Button>
            </div>
        </form>
    </AppShell>
</template>
