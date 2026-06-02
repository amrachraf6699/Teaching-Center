<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { GraduationCap } from 'lucide-vue-next';
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
    quickLoginAccounts: {
        type: Array,
        default: () => [],
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

function quickLogin(account) {
    form.code = account.code;
    form.password = account.password;
    form.redirect = props.redirect;
    form.clearErrors();
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

            <div v-if="quickLoginAccounts.length" class="space-y-3 border-y border-teachify-line py-4">
                <div>
                    <p class="text-sm font-black text-teachify-ink">{{ $t('auth.quickLoginTitle') }}</p>
                    <p class="mt-1 text-xs font-medium text-teachify-muted">{{ $t('auth.quickStudentLoginHelp') }}</p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <Button
                        v-for="account in quickLoginAccounts"
                        :key="account.code"
                        type="button"
                        variant="secondary"
                        class="w-full justify-start text-left"
                        :disabled="form.processing"
                        @click="quickLogin(account)"
                    >
                        <GraduationCap class="h-4 w-4 shrink-0" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{ $t('auth.quickLoginAction', { name: account.name }) }}</span>
                            <span class="block truncate text-xs font-semibold text-teachify-muted">
                                {{ $t('auth.studentCode') }} - {{ account.code }}
                            </span>
                            <span class="block truncate text-xs font-semibold text-teachify-muted">
                                {{ $t('auth.password') }}: {{ account.password }}
                            </span>
                        </span>
                    </Button>
                </div>
            </div>

            <TextInput v-model="form.code" :label="$t('auth.studentCode')" required :error="form.errors.code" />
            <TextInput v-model="form.password" :label="$t('auth.password')" type="password" required :error="form.errors.password" />

            <Button type="submit" class="w-full" :disabled="form.processing">{{ $t('auth.signIn') }}</Button>
        </form>
    </AuthLayout>
</template>
