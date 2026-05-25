<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Camera, CalendarDays } from 'lucide-vue-next';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    student: Object,
});

const page = usePage();
</script>

<template>
    <Head title="My Week" />
    <AppShell title="My Week">
        <section class="overflow-hidden rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(251,191,36,0.08))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <CalendarDays class="h-3.5 w-3.5" />
                        Weekly timetable
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-teachify-ink">{{ student.name }}</h2>
                    <p class="mt-2 text-sm font-medium text-teachify-muted">
                        Your sessions for this week from <span class="font-black text-teachify-ink">{{ student.week.starts_at }}</span> to
                        <span class="font-black text-teachify-ink">{{ student.week.ends_at }}</span>. Week starts on Saturday and ends on Friday.
                    </p>
                </div>

                <Link
                    :href="page.props.routes.studentScanAttendance"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-teachify-blue px-4 py-2.5 text-sm font-bold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700"
                >
                    <Camera class="h-4 w-4" />
                    Scan Attendance
                </Link>
            </div>

            <div class="border-t border-white/70 bg-white/75 px-5 py-4 sm:px-6">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">Student code</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.code }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">Groups</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.groups.length }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">Attendance records</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ student.attendance.length }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-teachify-ink">This week’s sessions</h2>
                    <p class="text-sm font-medium text-teachify-muted">Saturday to Friday, grouped by day.</p>
                </div>
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
                                    :class="session.attendance_status
                                        ? 'bg-teachify-blue-soft text-teachify-blue'
                                        : 'bg-amber-50 text-amber-700'"
                                >
                                    {{ session.attendance_status || 'Pending' }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-sm font-medium text-teachify-muted">
                                <span class="rounded-full bg-slate-50 px-3 py-2">{{ session.starts_at || '-' }} - {{ session.ends_at || '-' }}</span>
                            </div>

                            <p v-if="session.attendance_notes" class="mt-3 text-sm font-medium text-teachify-muted">{{ session.attendance_notes }}</p>
                        </article>
                    </div>

                    <EmptyState v-else title="No sessions" message="No lessons scheduled for this day." />
                </article>
            </div>
        </section>
    </AppShell>
</template>
