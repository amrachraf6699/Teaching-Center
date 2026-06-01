<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    student: Object,
});
</script>

<template>
    <Head :title="$t('nav.sessions')" />
    <AppShell :title="$t('nav.sessions')">
        <section class="overflow-hidden rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(251,191,36,0.08))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-black tracking-tight text-teachify-ink">{{ student.name }}</h2>
                    <p class="mt-2 text-sm font-medium text-teachify-muted">
                        {{ $t('student.sessionsWeekRange', { start: student.week.starts_at, end: student.week.ends_at }) }}
                    </p>
                </div>
            </div>

            <div class="border-t border-white/70 bg-white/75 px-5 py-4 sm:px-6">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('student.studentCode') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.code }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('nav.groups') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.groups.length }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('student.attendanceRecords') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.attendance.length }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6">
            <div class="mb-4">
                <h2 class="text-xl font-black text-teachify-ink">{{ $t('student.thisWeek') }}</h2>
                <p class="text-sm font-medium text-teachify-muted">{{ $t('student.saturdayToFriday') }}</p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <article v-for="day in student.week.days" :key="day.date" class="teachify-card rounded-[1.6rem] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-black uppercase tracking-[0.18em] text-teachify-muted">{{ day.day_short }}</div>
                            <h3 class="mt-1 text-lg font-black text-teachify-ink">{{ day.day_name }}</h3>
                        </div>
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-teachify-ink">{{ day.day_number }}</div>
                    </div>

                    <div v-if="day.sessions.length" class="mt-4 space-y-3">
                        <article v-for="session in day.sessions" :key="session.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="font-black text-teachify-blue">{{ session.title }}</div>
                                    <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ session.group || '-' }} · {{ session.subject || '-' }}</div>
                                </div>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.12em]"
                                    :class="session.attendance_status ? 'bg-teachify-blue-soft text-teachify-blue' : 'bg-amber-50 text-amber-700'"
                                >
                                    {{ $t(`attendance.status.${session.attendance_status || 'pending'}`) }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-sm font-medium text-teachify-muted">
                                <span class="rounded-full bg-slate-50 px-3 py-2">{{ session.starts_at || '-' }} - {{ session.ends_at || '-' }}</span>
                            </div>

                            <p v-if="session.attendance_notes" class="mt-3 text-sm font-medium text-teachify-muted">{{ session.attendance_notes }}</p>
                        </article>
                    </div>

                    <EmptyState v-else :title="$t('student.noSessions')" :message="$t('student.noSessionsMessage')" />
                </article>
            </div>
        </section>
    </AppShell>
</template>
