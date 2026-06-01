<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ parent: Object, action: String, showUrl: String });
const form = useForm({ name: props.parent.name, email: props.parent.email, password: '' });

function submit() {
    form.put(props.action);
}
</script>

<template>
    <Head :title="$t('admin.parents.edit')" />
    <AppShell :title="$t('admin.parents.edit')">
        <form class="teachify-card max-w-2xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <TextInput v-model="form.name" :label="$t('fields.name')" required :error="form.errors.name" />
            <TextInput v-model="form.email" :label="$t('fields.email')" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="$t('fields.newPassword')" type="password" :error="form.errors.password" />
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">{{ $t('admin.parents.save') }}</Button>
                <Button variant="secondary" :href="showUrl">{{ $t('actions.cancel') }}</Button>
            </div>
        </form>
    </AppShell>
</template>
