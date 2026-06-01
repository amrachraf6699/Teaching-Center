<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, FileText, ScanLine, UserRound } from 'lucide-vue-next';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    student: Object,
});

const page = usePage();
</script>

<template>
    <Head :title="$t('student.homeTitle')" />
    <AppShell :title="$t('student.homeTitle')">
        <section class="overflow-hidden rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(251,191,36,0.08))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <UserRound class="h-3.5 w-3.5" />
                        {{ $t('student.profile') }}
                    </div>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-teachify-ink">{{ student.name }}</h2>
                    <p class="mt-2 text-sm font-medium text-teachify-muted">
                        {{ $t('student.studentCode') }} <span class="font-black text-teachify-ink">{{ student.code }}</span>
                        <span v-if="student.phone"> · {{ student.phone }}</span>
                        <span v-if="student.date_of_birth"> · {{ student.date_of_birth }}</span>
                    </p>
                    <p v-if="student.notes" class="mt-2 max-w-xl text-sm font-medium text-teachify-muted">{{ student.notes }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Link :href="page.props.routes.studentSessions" class="rounded-2xl border border-white/70 bg-white/85 px-4 py-4 text-sm font-bold text-teachify-ink transition hover:border-teachify-blue hover:text-teachify-blue">
                        <div class="flex items-center gap-2"><CalendarDays class="h-4 w-4" /> {{ $t('student.thisWeek') }}</div>
                        <div class="mt-2 text-xs font-semibold text-teachify-muted">{{ student.week.starts_at }} - {{ student.week.ends_at }}</div>
                    </Link>
                    <Link :href="page.props.routes.studentExams" class="rounded-2xl border border-white/70 bg-white/85 px-4 py-4 text-sm font-bold text-teachify-ink transition hover:border-teachify-blue hover:text-teachify-blue">
                        <div class="flex items-center gap-2"><FileText class="h-4 w-4" /> {{ $t('exams.title') }}</div>
                        <div class="mt-2 text-xs font-semibold text-teachify-muted">{{ $t('student.upcomingExamsSummary', { upcoming: student.upcoming_exams.length, conducted: student.conducted_exams.length }) }}</div>
                    </Link>
                </div>
            </div>

            <div class="border-t border-white/70 bg-white/75 px-5 py-4 sm:px-6">
                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('nav.groups') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.groups.length }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('student.upcomingSessions') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.upcoming_sessions.length }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('student.recentAttendance') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.attendance.length }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('notifications.title') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.notifications.length }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-teachify-ink">{{ $t('student.upcomingSessions') }}</h2>
                        <p class="text-sm font-medium text-teachify-muted">{{ $t('student.upcomingSessionsDescription') }}</p>
                    </div>
                    <Link :href="page.props.routes.studentSessions" class="text-sm font-bold text-teachify-blue">{{ $t('actions.viewAll') }}</Link>
                </div>
                <div v-if="student.upcoming_sessions.length" class="mt-4 space-y-3">
                    <article v-for="session in student.upcoming_sessions" :key="session.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="font-black text-teachify-ink">{{ session.title }}</div>
                        <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ session.group || '-' }} · {{ session.subject || '-' }}</div>
                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ session.starts_at }}</div>
                    </article>
                </div>
                <EmptyState v-else :title="$t('student.noUpcomingSessions')" :message="$t('student.noUpcomingSessionsMessage')" />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-teachify-ink">{{ $t('exams.upcoming') }}</h2>
                        <p class="text-sm font-medium text-teachify-muted">{{ $t('exams.upcomingDescription') }}</p>
                    </div>
                    <Link :href="page.props.routes.studentExams" class="text-sm font-bold text-teachify-blue">{{ $t('student.openExams') }}</Link>
                </div>
                <div v-if="student.upcoming_exams.length" class="mt-4 space-y-3">
                    <Link v-for="exam in student.upcoming_exams" :key="exam.id" :href="exam.show_url" class="block rounded-2xl border border-teachify-line bg-white p-4 transition hover:border-teachify-blue">
                        <div class="font-black text-teachify-ink">{{ exam.title }}</div>
                        <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ exam.group?.name || '-' }} · {{ exam.group?.subject || '-' }}</div>
                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ exam.schedule }}</div>
                    </Link>
                </div>
                <EmptyState v-else :title="$t('student.noUpcomingExams')" :message="$t('student.noUpcomingExamsMessage')" />
            </div>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black text-teachify-ink">{{ $t('student.recentAttendance') }}</h2>
                <div v-if="student.attendance.length" class="mt-4 space-y-3">
                    <article v-for="item in student.attendance" :key="item.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-black capitalize">{{ $t(`attendance.status.${item.status || 'pending'}`) }}</div>
                                <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ item.session || '-' }} · {{ item.group || '-' }}</div>
                            </div>
                            <div class="text-right text-xs font-bold text-teachify-muted">{{ item.starts_at || '-' }}</div>
                        </div>
                        <p v-if="item.notes" class="mt-3 text-sm font-medium text-teachify-muted">{{ item.notes }}</p>
                    </article>
                </div>
                <EmptyState v-else :title="$t('student.noAttendance')" :message="$t('student.noAttendanceMessage')" />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-teachify-ink">{{ $t('notifications.title') }}</h2>
                    <span class="inline-flex items-center gap-2 rounded-full bg-teachify-blue-soft px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-teachify-blue">
                        <ScanLine class="h-3.5 w-3.5" />
                        {{ $t('student.useScanCodeFromHeader') }}
                    </span>
                </div>
                <div v-if="student.notifications.length" class="mt-4 space-y-3">
                    <article v-for="notification in student.notifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-blue">{{ notification.type }}</div>
                        <h3 class="mt-2 font-black">{{ notification.title }}</h3>
                        <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                        <div class="mt-3 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</div>
                    </article>
                </div>
                <EmptyState v-else :title="$t('student.noNotifications')" :message="$t('student.noNotificationsMessage')" />
            </div>
        </section>
    </AppShell>
</template>
