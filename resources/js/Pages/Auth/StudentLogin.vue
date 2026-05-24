<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import TextInput from '../../Components/TextInput.vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const props = defineProps({
    title: String,
    action: String,
    redirect: {
        type: String,
        default: '',
    },
});

const form = useForm({
    code: '',
    password: '',
    redirect: props.redirect,
});

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head :title="title" />
    <AuthLayout>
        <form class="teachify-card space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-xl font-black">Student attendance login</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">Sign in with your student code and password to confirm attendance.</p>
            </div>

            <TextInput v-model="form.code" label="Student Code" required :error="form.errors.code" />
            <TextInput v-model="form.password" label="Password" type="password" required :error="form.errors.password" />

            <Button type="submit" class="w-full" :disabled="form.processing">Sign in</Button>
        </form>
    </AuthLayout>
</template>
