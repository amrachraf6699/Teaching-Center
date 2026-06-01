<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import AppShell from '../../Layouts/AppShell.vue';

const props = defineProps({
    student: Object,
    session: Object,
});

const form = useForm({});

function submit() {
    form.post(props.session.submit_url);
}
</script>

<template>
    <Head :title="session.title" />
    <AppShell :title="$t('student.scanAttendance')">
        <section class="teachify-card max-w-3xl rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('nav.students') }}</div>
                    <div class="mt-1 font-bold">{{ student.name }} · {{ student.code }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('exams.group') }}</div>
                    <div class="mt-1 font-bold">{{ session.group || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('exams.subject') }}</div>
                    <div class="mt-1 font-bold">{{ session.subject || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('sessions.starts') }}</div>
                    <div class="mt-1 font-bold">{{ session.starts_at || '-' }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-[1.4rem] border border-teachify-line bg-white p-4">
                <h2 class="text-lg font-black">{{ session.title }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('student.attendanceConfirmDescription') }}</p>

                <div v-if="session.attendance" class="mt-4 rounded-2xl bg-teachify-blue-soft px-4 py-3 text-sm font-bold text-teachify-blue">
                    {{ $t('student.attendanceAlreadyRecorded', { status: $t(`attendance.status.${session.attendance.status || 'pending'}`) }) }}
                </div>

                <div v-else class="mt-4">
                    <Button type="button" :disabled="form.processing" @click="submit">{{ $t('student.markPresent') }}</Button>
                </div>
            </div>
        </section>
    </AppShell>
</template>
