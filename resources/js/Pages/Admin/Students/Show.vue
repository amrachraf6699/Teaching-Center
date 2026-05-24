<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ student: Object });
</script>

<template>
    <Head :title="student.name" />
    <AppShell :title="student.name">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="student.edit_url">Edit Student</Button>
            <Button variant="secondary" :href="student.index_url">Back to Students</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Code</div><div class="mt-1 font-bold">{{ student.code }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Phone</div><div class="mt-1 font-bold">{{ student.phone || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Birth Date</div><div class="mt-1 font-bold">{{ student.date_of_birth || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Status</div><div class="mt-1 font-bold">{{ student.is_active ? 'Active' : 'Inactive' }}</div></div>
            </div>
            <p v-if="student.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ student.notes }}</p>
            <p class="mt-4 text-sm font-bold">
                Parent:
                <Link v-if="student.parent" :href="student.parent.show_url" class="text-teachify-blue">{{ student.parent.name }}</Link>
                <span v-else>-</span>
            </p>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-2">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Groups and Sessions</h2>
                <div v-if="student.groups.length" class="mt-4 space-y-4">
                    <article v-for="group in student.groups" :key="group.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <Link :href="group.show_url" class="font-black text-teachify-blue">{{ group.name }}</Link>
                        <div class="text-sm font-semibold text-teachify-muted">{{ group.subject || 'General' }} - {{ group.level || '-' }}</div>
                        <div class="mt-3 space-y-2">
                            <div v-for="session in group.sessions" :key="session.id" class="flex justify-between gap-3 rounded-xl bg-teachify-blue-soft/60 px-3 py-2 text-sm">
                                <span class="font-bold">{{ session.title }}</span>
                                <span class="font-black capitalize">{{ session.attendance || 'pending' }}</span>
                            </div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else title="No groups" message="Enroll this student in a group." />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Exam Results</h2>
                <div v-if="student.exam_results.length" class="mt-4 space-y-3">
                    <div v-for="result in student.exam_results" :key="result.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="flex justify-between gap-3"><span class="font-black">{{ result.title }}</span><span class="font-black text-teachify-blue">{{ result.percentage }}%</span></div>
                        <div class="text-sm font-semibold text-teachify-muted">{{ result.score }} / {{ result.max_score }} - {{ result.group || '-' }}</div>
                        <div class="mt-1 text-xs font-semibold text-teachify-muted">{{ result.schedule }}</div>
                    </div>
                </div>
                <EmptyState v-else title="No exam results" message="Saved grades will appear here." />
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Attendance and Notifications</h2>
            <div class="mt-4 grid gap-5 xl:grid-cols-2">
                <div>
                    <h3 class="font-black">Attendance</h3>
                    <div v-if="student.attendance.length" class="mt-3 space-y-2">
                        <div v-for="attendance in student.attendance" :key="attendance.id" class="rounded-xl border border-teachify-line bg-white p-3 text-sm">
                            <div class="font-black capitalize">{{ attendance.status }} - {{ attendance.session }}</div>
                            <div class="font-semibold text-teachify-muted">{{ attendance.group }} - {{ attendance.starts_at }}</div>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-sm font-medium text-teachify-muted">No attendance records.</p>
                </div>
                <div>
                    <h3 class="font-black">Notifications</h3>
                    <div v-if="student.notifications.length" class="mt-3 space-y-2">
                        <div v-for="notification in student.notifications" :key="notification.id" class="rounded-xl border border-teachify-line bg-white p-3 text-sm">
                            <div class="font-black">{{ notification.title }}</div>
                            <div class="font-semibold text-teachify-muted">{{ notification.type }} - {{ notification.created_at }}</div>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-sm font-medium text-teachify-muted">No notifications.</p>
                </div>
            </div>
        </section>
    </AppShell>
</template>
