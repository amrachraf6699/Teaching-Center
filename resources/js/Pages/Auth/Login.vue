<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn, ShieldCheck, Users } from 'lucide-vue-next';
import Button from '../../Components/Button.vue';
import TextInput from '../../Components/TextInput.vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    title: String,
    action: String,
    quickLoginAccounts: {
        type: Array,
        default: () => [],
    },
});

const { t } = useI18n();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(props.action);
}

const roleIcons = {
    teacher: ShieldCheck,
    parent: Users,
};

function iconFor(role) {
    return roleIcons[role] ?? LogIn;
}

function quickLogin(account) {
    form.email = account.email;
    form.password = account.password;
    form.remember = false;
    form.clearErrors();
    form.post(props.action);
}
</script>

<template>
    <Head :title="t('auth.loginTitle')" />
    <AuthLayout>
        <form class="teachify-card space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-xl font-black">{{ $t('auth.loginHeading') }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('auth.loginSubtitle') }}</p>
            </div>

            <div v-if="quickLoginAccounts.length" class="space-y-3 border-y border-teachify-line py-4">
                <div>
                    <p class="text-sm font-black text-teachify-ink">{{ $t('auth.quickLoginTitle') }}</p>
                    <p class="mt-1 text-xs font-medium text-teachify-muted">{{ $t('auth.quickLoginHelp') }}</p>
                </div>

                <div class="grid gap-2">
                    <Button
                        v-for="account in quickLoginAccounts"
                        :key="account.email"
                        type="button"
                        variant="secondary"
                        class="w-full justify-start text-left"
                        :disabled="form.processing"
                        @click="quickLogin(account)"
                    >
                        <component :is="iconFor(account.role)" class="h-4 w-4 shrink-0" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{ $t('auth.quickLoginAction', { name: account.name }) }}</span>
                            <span class="block truncate text-xs font-semibold text-teachify-muted">
                                {{ $t(`auth.quickLoginRole.${account.role}`) }} - {{ account.email }}
                            </span>
                            <span class="block truncate text-xs font-semibold text-teachify-muted">
                                {{ $t('auth.password') }}: {{ account.password }}
                            </span>
                        </span>
                    </Button>
                </div>
            </div>

            <TextInput v-model="form.email" :label="$t('auth.email')" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="$t('auth.password')" type="password" required :error="form.errors.password" />

            <label class="flex items-center gap-3 text-sm font-bold text-teachify-muted">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                {{ $t('auth.rememberMe') }}
            </label>

            <Button type="submit" class="w-full" :disabled="form.processing">{{ $t('auth.signIn') }}</Button>
            <div class="text-center text-sm font-medium text-teachify-muted">
                {{ $t('auth.studentPortalPrompt') }}
                <Link href="/student/login" class="font-bold text-teachify-blue">{{ $t('auth.studentLoginLink') }}</Link>
            </div>
        </form>
    </AuthLayout>
</template>
