<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import TextInput from '../../Components/TextInput.vue';
import AppShell from '../../Layouts/AppShell.vue';

const props = defineProps({
    action: String,
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put(props.action, {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Change Password" />
    <AppShell title="Change Password">
        <form class="teachify-card mx-auto max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-lg font-black">Update your password</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">Enter your current password, then choose a new one for future student logins.</p>
            </div>

            <TextInput v-model="form.current_password" label="Current Password" type="password" required :error="form.errors.current_password" />
            <TextInput v-model="form.password" label="New Password" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" label="Confirm New Password" type="password" required :error="form.errors.password_confirmation" />

            <Button type="submit" :disabled="form.processing">Save Password</Button>
        </form>
    </AppShell>
</template>
