<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ group: Object });
</script>

<template>
    <Head :title="group.name" />
    <AppShell :title="group.name">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="group.edit_url">Edit Group</Button>
            <Button variant="secondary" :href="group.index_url">Back to Groups</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-5">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Subject</div><div class="mt-1 font-bold">{{ group.subject || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Level</div><div class="mt-1 font-bold">{{ group.level || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Students</div><div class="mt-1 font-bold">{{ group.students_count }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Sessions</div><div class="mt-1 font-bold">{{ group.sessions_count }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Status</div><div class="mt-1 font-bold">{{ group.is_active ? 'Active' : 'Inactive' }}</div></div>
            </div>
            <p v-if="group.description" class="mt-4 text-sm font-medium text-teachify-muted">{{ group.description }}</p>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Students</h2>
            <div v-if="group.students.length" class="mt-4 grid gap-3 sm:grid-cols-2">
                <Link v-for="student in group.students" :key="student.id" :href="student.show_url" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black text-teachify-blue">{{ student.name }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ student.code || '-' }} · {{ student.parent || 'No parent' }}</div>
                </Link>
            </div>
            <EmptyState v-else title="No students" message="Enroll students from the edit screen." />
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-2">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Sessions and Attendance</h2>
                <div v-if="group.sessions.length" class="mt-4 space-y-4">
                    <article v-for="session in group.sessions" :key="session.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <Link :href="session.show_url" class="font-black text-teachify-blue">{{ session.title }}</Link>
                        <div class="text-sm font-semibold text-teachify-muted">{{ session.starts_at }} - {{ session.ends_at || 'No end time' }}</div>
                        <div v-if="session.attendance.length" class="mt-3 space-y-2">
                            <div v-for="attendance in session.attendance" :key="attendance.id" class="rounded-xl bg-teachify-blue-soft/60 px-3 py-2 text-sm">
                                <span class="font-black">{{ attendance.student }}</span> · <span class="capitalize">{{ attendance.status }}</span>
                            </div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else title="No sessions" message="Scheduled sessions will appear here." />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Exams and Results</h2>
                <div v-if="group.exams.length" class="mt-4 space-y-4">
                    <article v-for="exam in group.exams" :key="exam.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <Link :href="exam.show_url" class="font-black text-teachify-blue">{{ exam.title }}</Link>
                        <div class="text-sm font-semibold text-teachify-muted">{{ exam.exam_date }} · Max {{ exam.max_score }}</div>
                        <div v-if="exam.results.length" class="mt-3 space-y-2">
                            <div v-for="result in exam.results" :key="result.id" class="rounded-xl bg-teachify-yellow-soft px-3 py-2 text-sm">
                                <span class="font-black">{{ result.student }}</span> · {{ result.score }} · {{ result.percentage }}%
                            </div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else title="No exams" message="Group exams will appear here." />
            </div>
        </section>
    </AppShell>
</template>
