<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import TextInput from '../../Components/TextInput.vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const props = defineProps({
    title: String,
    action: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
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
                <h2 class="text-xl font-black">Welcome back</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">Sign in with your teacher or parent account.</p>
            </div>

            <TextInput v-model="form.email" label="Email" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" label="Password" type="password" required :error="form.errors.password" />

            <label class="flex items-center gap-3 text-sm font-bold text-teachify-muted">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                Remember me
            </label>

            <Button type="submit" class="w-full" :disabled="form.processing">Sign in</Button>
            <div class="text-center text-sm font-medium text-teachify-muted">
                Student attendance scan?
                <Link href="/student/login" class="font-bold text-teachify-blue">Student login</Link>
            </div>
        </form>
    </AuthLayout>
</template>
