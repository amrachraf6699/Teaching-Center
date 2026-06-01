<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ action: String });
const form = useForm({ name: '', email: '', password: '' });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head :title="$t('admin.parents.add')" />
    <AppShell :title="$t('admin.parents.add')">
        <form class="teachify-card max-w-2xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <TextInput v-model="form.name" :label="$t('fields.name')" required :error="form.errors.name" />
            <TextInput v-model="form.email" :label="$t('fields.email')" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="$t('fields.password')" type="password" required :error="form.errors.password" />
            <Button type="submit" :disabled="form.processing">{{ $t('admin.parents.create') }}</Button>
        </form>
    </AppShell>
</template>
