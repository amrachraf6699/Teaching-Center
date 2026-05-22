<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ session: Object });
</script>

<template>
    <Head :title="session.title" />
    <AppShell :title="session.title" subtitle="Session details, enrolled students, and attendance records.">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="session.edit_url">Edit Session</Button>
            <Button variant="secondary" :href="session.index_url">Back to Sessions</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Group</div><Link v-if="session.group" :href="session.group.show_url" class="mt-1 block font-bold text-teachify-blue">{{ session.group.name }}</Link></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Starts</div><div class="mt-1 font-bold">{{ session.starts_at }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Ends</div><div class="mt-1 font-bold">{{ session.ends_at || '-' }}</div></div>
            </div>
            <p v-if="session.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ session.notes }}</p>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Students</h2>
            <div v-if="session.students.length" class="mt-4 grid gap-3 sm:grid-cols-2">
                <Link v-for="student in session.students" :key="student.id" :href="student.show_url" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black text-teachify-blue">{{ student.name }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ student.code || '-' }} · {{ student.parent || 'No parent' }}</div>
                    <div class="mt-2 text-sm font-black capitalize">{{ student.attendance || 'pending' }}</div>
                </Link>
            </div>
            <EmptyState v-else title="No students" message="Students assigned to the group will appear here." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Attendance Records</h2>
            <div v-if="session.attendance.length" class="mt-4 grid gap-3">
                <article v-for="attendance in session.attendance" :key="attendance.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black">{{ attendance.student }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ attendance.parent || 'No parent' }} · <span class="capitalize">{{ attendance.status }}</span></div>
                    <p v-if="attendance.notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ attendance.notes }}</p>
                </article>
            </div>
            <EmptyState v-else title="No attendance yet" message="Attendance records will appear here after saving them." />
        </section>
    </AppShell>
</template>
