<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Bell, CalendarDays, Download, FileText, GraduationCap, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

const props = defineProps({
    children: Array,
});

const page = usePage();
const routes = computed(() => page.props.routes ?? {});

const dashboardStats = computed(() => {
    const children = props.children ?? [];

    return [
        {
            label: 'Children',
            value: children.length,
            icon: UserRound,
        },
        {
            label: 'Groups',
            value: children.reduce((total, child) => total + (child.group_count || 0), 0),
            icon: GraduationCap,
        },
        {
            label: 'Upcoming sessions',
            value: children.reduce((total, child) => total + (child.upcoming_sessions?.length || 0), 0),
            icon: CalendarDays,
        },
        {
            label: 'Upcoming exams',
            value: children.reduce((total, child) => total + (child.upcoming_exams?.length || 0), 0),
            icon: FileText,
        },
    ];
});

function initials(name) {
    return String(name || '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase() || 'S';
}

function attendanceClass(status) {
    if (status === 'present') return 'bg-emerald-50 text-emerald-700';
    if (status === 'absent') return 'bg-red-50 text-red-600';
    if (status === 'late') return 'bg-amber-50 text-amber-700';
    if (status === 'excused') return 'bg-sky-50 text-sky-700';

    return 'bg-slate-100 text-slate-600';
}

function examClass(percentage) {
    if (percentage == null) return 'bg-slate-100 text-slate-600';
    if (percentage >= 80) return 'bg-emerald-50 text-emerald-700';
    if (percentage >= 50) return 'bg-amber-50 text-amber-700';

    return 'bg-red-50 text-red-600';
}

function availabilityLabel(value) {
    return String(value || 'scheduled').replaceAll('_', ' ');
}
</script>

<template>
    <Head title="Parent Portal" />
    <AppShell title="Parent Portal">
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="stat in dashboardStats"
                :key="stat.label"
                class="rounded-[1.4rem] border border-teachify-line bg-white p-4 shadow-sm"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">{{ stat.label }}</div>
                        <div class="mt-1 text-2xl font-black text-teachify-ink">{{ stat.value }}</div>
                    </div>
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-teachify-blue-soft text-teachify-blue">
                        <component :is="stat.icon" class="h-5 w-5" />
                    </div>
                </div>
            </article>
        </section>

        <div v-if="children.length" class="mt-6 space-y-6">
            <article
                v-for="child in children"
                :key="child.id"
                class="overflow-hidden rounded-[1.6rem] border border-teachify-line bg-white shadow-[0_14px_40px_rgba(15,23,42,0.08)]"
            >
                <div class="border-b border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.96)_48%,rgba(13,148,136,0.08))] p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex min-w-0 items-start gap-4">
                            <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-teachify-blue text-lg font-black text-white">
                                {{ initials(child.name) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-2xl font-black tracking-tight text-teachify-ink">{{ child.name }}</h2>
                                    <span v-if="child.code" class="rounded-full bg-white px-3 py-1 text-xs font-black text-teachify-blue shadow-sm">{{ child.code }}</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-teachify-muted">
                                    {{ child.group_count }} {{ child.group_count === 1 ? 'group' : 'groups' }}
                                    <span v-if="child.phone"> - {{ child.phone }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                :href="child.pdf_url"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-2xl border border-teachify-line bg-white px-4 py-2 text-sm font-bold text-teachify-ink shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
                            >
                                <Download class="h-4 w-4" />
                                PDF
                            </a>
                            <Link
                                :href="routes.parentAttendance"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-2xl border border-teachify-line bg-white px-4 py-2 text-sm font-bold text-teachify-ink shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
                            >
                                <CalendarDays class="h-4 w-4" />
                                Attendance
                            </Link>
                            <Link
                                :href="routes.parentExams"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-2xl bg-teachify-blue px-4 py-2 text-sm font-bold text-white shadow-[0_10px_22px_rgba(37,99,235,0.18)] transition hover:bg-blue-700"
                            >
                                <FileText class="h-4 w-4" />
                                Exams
                            </Link>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-3">
                            <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Groups</div>
                            <div class="mt-1 text-lg font-black text-teachify-ink">{{ child.group_count }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-3">
                            <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Last attendance</div>
                            <div class="mt-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-black capitalize" :class="attendanceClass(child.last_attendance)">
                                    {{ child.last_attendance || 'pending' }}
                                </span>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-3">
                            <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Latest exam</div>
                            <div class="mt-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-black" :class="examClass(child.latest_exam_percentage)">
                                    {{ child.latest_exam_percentage != null ? `${child.latest_exam_percentage}%` : 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/85 px-4 py-3">
                            <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Notifications</div>
                            <div class="mt-1 text-lg font-black text-teachify-ink">{{ child.recent_notifications.length }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(280px,0.75fr)]">
                    <section>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-black text-teachify-ink">This week</h3>
                                <p class="text-sm font-medium text-teachify-muted">{{ child.week.starts_at }} - {{ child.week.ends_at }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <article
                                v-for="day in child.week.days"
                                :key="day.date"
                                class="rounded-[1.2rem] border border-teachify-line bg-slate-50 p-3"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-[11px] font-black uppercase tracking-[0.14em] text-teachify-muted">{{ day.day_short }}</div>
                                        <div class="font-black text-teachify-ink">{{ day.day_name }}</div>
                                    </div>
                                    <div class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-teachify-ink">{{ day.day_number }}</div>
                                </div>

                                <div v-if="day.sessions.length" class="mt-3 space-y-2">
                                    <div v-for="session in day.sessions" :key="session.id" class="rounded-xl bg-white p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-black text-teachify-blue">{{ session.title }}</div>
                                                <div class="mt-0.5 text-xs font-semibold text-teachify-muted">{{ session.group || '-' }} - {{ session.subject || '-' }}</div>
                                            </div>
                                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-black capitalize" :class="attendanceClass(session.attendance_status)">
                                                {{ session.attendance_status || 'pending' }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ session.starts_at || '-' }} - {{ session.ends_at || '-' }}</div>
                                    </div>
                                </div>
                                <p v-else class="mt-3 text-sm font-medium text-teachify-muted">No sessions.</p>
                            </article>
                        </div>
                    </section>

                    <aside class="space-y-5">
                        <section class="rounded-[1.3rem] border border-teachify-line bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="font-black text-teachify-ink">Upcoming</h3>
                                <CalendarDays class="h-4 w-4 text-teachify-blue" />
                            </div>

                            <div class="mt-3 space-y-3">
                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Sessions</div>
                                    <div v-if="child.upcoming_sessions.length" class="mt-2 space-y-2">
                                        <div v-for="session in child.upcoming_sessions" :key="session.id" class="rounded-xl bg-white px-3 py-2">
                                            <div class="text-sm font-black text-teachify-ink">{{ session.title }}</div>
                                            <div class="mt-0.5 text-xs font-semibold text-teachify-muted">{{ session.group || '-' }} - {{ session.starts_at || '-' }}</div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm font-medium text-teachify-muted">No upcoming sessions.</p>
                                </div>

                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Exams</div>
                                    <div v-if="child.upcoming_exams.length" class="mt-2 space-y-2">
                                        <div v-for="exam in child.upcoming_exams" :key="exam.id" class="rounded-xl bg-white px-3 py-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-black text-teachify-ink">{{ exam.title }}</div>
                                                    <div class="mt-0.5 text-xs font-semibold text-teachify-muted">{{ exam.group?.name || '-' }} - {{ exam.schedule }}</div>
                                                </div>
                                                <span class="shrink-0 rounded-full bg-teachify-blue-soft px-2 py-0.5 text-[11px] font-black capitalize text-teachify-blue">
                                                    {{ availabilityLabel(exam.availability) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm font-medium text-teachify-muted">No upcoming exams.</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[1.3rem] border border-teachify-line bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="font-black text-teachify-ink">Recent</h3>
                                <Bell class="h-4 w-4 text-teachify-blue" />
                            </div>

                            <div class="mt-3 space-y-4">
                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Attendance</div>
                                    <div v-if="child.recent_attendance.length" class="mt-2 space-y-2">
                                        <div v-for="item in child.recent_attendance" :key="item.id" class="rounded-xl bg-white px-3 py-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-black text-teachify-ink">{{ item.session || '-' }}</div>
                                                    <div class="mt-0.5 text-xs font-semibold text-teachify-muted">{{ item.group || '-' }} - {{ item.starts_at || '-' }}</div>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-black capitalize" :class="attendanceClass(item.status)">
                                                    {{ item.status || 'pending' }}
                                                </span>
                                            </div>
                                            <p v-if="item.notes" class="mt-1 text-xs font-medium text-teachify-muted">{{ item.notes }}</p>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm font-medium text-teachify-muted">No attendance yet.</p>
                                </div>

                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Exam results</div>
                                    <div v-if="child.conducted_exams.length" class="mt-2 space-y-2">
                                        <div v-for="exam in child.conducted_exams" :key="exam.id" class="rounded-xl bg-white px-3 py-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-black text-teachify-ink">{{ exam.title }}</div>
                                                    <div class="mt-0.5 text-xs font-semibold text-teachify-muted">{{ exam.group?.name || '-' }} - {{ exam.schedule }}</div>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-black" :class="examClass(exam.percentage)">
                                                    {{ exam.percentage != null ? `${exam.percentage}%` : availabilityLabel(exam.availability) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm font-medium text-teachify-muted">No exam results yet.</p>
                                </div>

                                <div>
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Notifications</div>
                                    <div v-if="child.recent_notifications.length" class="mt-2 space-y-2">
                                        <div v-for="notification in child.recent_notifications" :key="notification.id" class="rounded-xl bg-white px-3 py-2">
                                            <div class="text-[11px] font-black uppercase tracking-[0.12em] text-teachify-blue">{{ notification.type }}</div>
                                            <div class="mt-1 text-sm font-black text-teachify-ink">{{ notification.title }}</div>
                                            <p class="mt-0.5 text-xs font-medium text-teachify-muted">{{ notification.body }}</p>
                                        </div>
                                    </div>
                                    <p v-else class="mt-2 text-sm font-medium text-teachify-muted">No recent notifications.</p>
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </article>
        </div>

        <EmptyState
            v-else
            title="No children linked"
            message="Ask the teacher to link students to this parent account."
        />
    </AppShell>
</template>
