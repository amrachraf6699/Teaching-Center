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
    <Head :title="$t('student.changePassword')" />
    <AppShell :title="$t('student.changePassword')">
        <form class="teachify-card mx-auto max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-lg font-black">{{ $t('student.updatePassword') }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('student.updatePasswordDescription') }}</p>
            </div>

            <TextInput v-model="form.current_password" :label="$t('student.currentPassword')" type="password" required :error="form.errors.current_password" />
            <TextInput v-model="form.password" :label="$t('student.newPassword')" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" :label="$t('student.confirmNewPassword')" type="password" required :error="form.errors.password_confirmation" />

            <Button type="submit" :disabled="form.processing">{{ $t('student.savePassword') }}</Button>
        </form>
    </AppShell>
</template>
