<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import TextInput from '../../Components/TextInput.vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    title: String,
    action: String,
    redirect: {
        type: String,
        default: '',
    },
});

const { t } = useI18n();
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
    <Head :title="t('auth.studentLoginTitle')" />
    <AuthLayout>
        <form class="teachify-card space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-xl font-black">{{ $t('auth.studentLoginHeading') }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('auth.studentLoginSubtitle') }}</p>
            </div>

            <TextInput v-model="form.code" :label="$t('auth.studentCode')" required :error="form.errors.code" />
            <TextInput v-model="form.password" :label="$t('auth.password')" type="password" required :error="form.errors.password" />

            <Button type="submit" class="w-full" :disabled="form.processing">{{ $t('auth.signIn') }}</Button>
        </form>
    </AuthLayout>
</template>
