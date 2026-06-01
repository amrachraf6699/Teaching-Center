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
            <Button :href="student.edit_url">{{ $t('admin.students.edit') }}</Button>
            <Button variant="secondary" :href="`/admin/notifications?student_id=${student.id}`">{{ $t('admin.notifications.sendButton') }}</Button>
            <Button variant="secondary" :href="student.index_url">{{ $t('admin.students.back') }}</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-5">
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.code') }}</div><div class="mt-1 font-bold">{{ student.code }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.phone') }}</div><div class="mt-1 font-bold">{{ student.phone || $t('common.noData') }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.birthDate') }}</div><div class="mt-1 font-bold">{{ student.date_of_birth || $t('common.noData') }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.status') }}</div><div class="mt-1 font-bold">{{ student.is_active ? $t('common.active') : $t('common.inactive') }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('admin.students.login') }}</div><div class="mt-1 font-bold">{{ student.student_login.ready ? $t('common.ready') : $t('common.missing') }}</div></div>
            </div>
            <p v-if="student.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ student.notes }}</p>
            <p class="mt-4 text-sm font-bold">
                {{ $t('fields.parent') }}:
                <Link v-if="student.parent" :href="student.parent.show_url" class="text-teachify-blue">{{ student.parent.name }}</Link>
                <span v-else>{{ $t('common.noData') }}</span>
            </p>
            <div class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-medium text-teachify-muted">
                {{ $t('admin.students.loginHelp', { code: student.student_login.code }) }}
            </div>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-2">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.students.groupsAndSessions') }}</h2>
                <div v-if="student.groups.length" class="mt-4 space-y-4">
                    <article v-for="group in student.groups" :key="group.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <Link :href="group.show_url" class="font-black text-teachify-blue">{{ group.name }}</Link>
                        <div class="text-sm font-semibold text-teachify-muted">{{ group.subject || $t('parentPortal.general') }} - {{ group.level || $t('common.noData') }}</div>
                        <div class="mt-3 space-y-2">
                            <div v-for="session in group.sessions" :key="session.id" class="flex justify-between gap-3 rounded-xl bg-teachify-blue-soft/60 px-3 py-2 text-sm">
                                <span class="font-bold">{{ session.title }}</span>
                                <span class="font-black capitalize">{{ $t(`attendance.status.${session.attendance || 'pending'}`) }}</span>
                            </div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else :title="$t('admin.students.noGroups')" :message="$t('admin.students.noGroupsMessage')" />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('admin.students.examResults') }}</h2>
                <div v-if="student.exam_results.length" class="mt-4 space-y-3">
                    <div v-for="result in student.exam_results" :key="result.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="flex justify-between gap-3"><span class="font-black">{{ result.title }}</span><span class="font-black text-teachify-blue">{{ result.percentage }}%</span></div>
                        <div class="text-sm font-semibold text-teachify-muted">{{ result.score }} / {{ result.max_score }} - {{ result.group || $t('common.noData') }}</div>
                        <div class="mt-1 text-xs font-semibold text-teachify-muted">{{ result.schedule }}</div>
                    </div>
                </div>
                <EmptyState v-else :title="$t('admin.students.noExamResults')" :message="$t('admin.students.noExamResultsMessage')" />
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">{{ $t('admin.students.attendanceAndNotifications') }}</h2>
            <div class="mt-4 grid gap-5 xl:grid-cols-2">
                <div>
                    <h3 class="font-black">{{ $t('admin.students.attendance') }}</h3>
                    <div v-if="student.attendance.length" class="mt-3 space-y-2">
                        <div v-for="attendance in student.attendance" :key="attendance.id" class="rounded-xl border border-teachify-line bg-white p-3 text-sm">
                            <div class="font-black capitalize">{{ $t(`attendance.status.${attendance.status || 'pending'}`) }} - {{ attendance.session }}</div>
                            <div class="font-semibold text-teachify-muted">{{ attendance.group }} - {{ attendance.starts_at }}</div>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-sm font-medium text-teachify-muted">{{ $t('admin.students.noAttendanceRecords') }}</p>
                </div>
                <div>
                    <h3 class="font-black">{{ $t('admin.students.notifications') }}</h3>
                    <div v-if="student.notifications.length" class="mt-3 space-y-2">
                        <div v-for="notification in student.notifications" :key="notification.id" class="rounded-xl border border-teachify-line bg-white p-3 text-sm">
                            <div class="font-black">{{ notification.title }}</div>
                            <div class="font-semibold text-teachify-muted">{{ $t(`admin.notifications.recipients.${notification.recipient_role}`) }} {{ $t('common.separator') }} {{ notification.type }} - {{ notification.created_at }}</div>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-sm font-medium text-teachify-muted">{{ $t('admin.students.noNotifications') }}</p>
                </div>
            </div>
        </section>
    </AppShell>
</template>
