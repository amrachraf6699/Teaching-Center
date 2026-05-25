<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, Download, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    children: Array,
});

const page = usePage();
const routes = computed(() => page.props.routes ?? {});

const themes = [
    { from: '#2563eb', to: '#1d4ed8' },
    { from: '#0d9488', to: '#0f766e' },
    { from: '#7c3aed', to: '#6d28d9' },
    { from: '#e11d48', to: '#be123c' },
];

function theme(index) {
    return themes[index % themes.length];
}

function initials(name) {
    return name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
}

function attendanceColor(status) {
    if (status === 'present') return '#10b981';
    if (status) return '#f59e0b';
    return '#94a3b8';
}

function examColor(pct) {
    if (pct == null) return '#94a3b8';
    if (pct >= 80) return '#10b981';
    if (pct >= 50) return '#f59e0b';
    return '#f43f5e';
}
</script>

<template>
    <Head title="Parent Portal" />
    <AppShell title="Parent Portal">
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="(child, index) in children"
                :key="child.id"
                class="overflow-hidden rounded-[1.6rem] border border-white/60 bg-white shadow-[0_8px_32px_rgba(37,99,235,0.10)]"
            >
                <!-- Gradient header -->
                <div
                    class="relative overflow-hidden px-5 pb-5 pt-5"
                    :style="`background: linear-gradient(135deg, ${theme(index).from}, ${theme(index).to})`"
                >
                    <!-- Decorative blob -->
                    <div
                        class="pointer-events-none absolute -right-6 -top-6 h-32 w-32 rounded-full opacity-20"
                        :style="`background: white`"
                    />

                    <div class="relative flex items-start justify-between gap-3">
                        <!-- Avatar -->
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 text-base font-black text-white ring-1 ring-white/30">
                            {{ initials(child.name) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Code -->
                            <span
                                v-if="child.code"
                                class="rounded-full bg-white/25 px-3 py-1 text-[11px] font-bold tracking-wide text-white"
                            >
                                {{ child.code }}
                            </span>
                            <!-- Download PDF -->
                            <a
                                :href="child.pdf_url"
                                target="_blank"
                                rel="noopener"
                                title="Download full profile PDF"
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white hover:text-teachify-blue"
                            >
                                <Download class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>

                    <div class="relative mt-3">
                        <h2 class="text-[1.35rem] font-black leading-tight text-white">{{ child.name }}</h2>
                        <p class="mt-0.5 text-sm font-medium text-white/65">
                            {{ child.group_count }} {{ child.group_count === 1 ? 'group' : 'groups' }} enrolled
                        </p>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="grid grid-cols-3 divide-x divide-teachify-line border-b border-teachify-line">
                    <!-- Groups -->
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <span class="text-xl font-black text-teachify-ink">{{ child.group_count }}</span>
                        <span class="text-[11px] font-semibold text-teachify-muted">Groups</span>
                    </div>

                    <!-- Attendance -->
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <span
                            class="text-sm font-black capitalize"
                            :style="`color: ${attendanceColor(child.last_attendance)}`"
                        >
                            {{ child.last_attendance || 'N/A' }}
                        </span>
                        <span class="text-[11px] font-semibold text-teachify-muted">Attendance</span>
                    </div>

                    <!-- Last exam -->
                    <div class="flex flex-col items-center gap-0.5 py-4">
                        <span
                            class="text-xl font-black"
                            :style="`color: ${examColor(child.latest_exam_percentage)}`"
                        >
                            {{ child.latest_exam_percentage != null ? `${child.latest_exam_percentage}%` : '—' }}
                        </span>
                        <span class="text-[11px] font-semibold text-teachify-muted">Last Exam</span>
                    </div>
                </div>

                <!-- Exam title hint -->
                <div v-if="child.latest_exam_title" class="border-b border-teachify-line px-5 py-2.5">
                    <p class="truncate text-xs text-teachify-muted">
                        <span class="font-bold text-teachify-ink">Latest:</span>
                        {{ child.latest_exam_title }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div class="grid grid-cols-2 divide-x divide-teachify-line">
                    <Link
                        :href="routes.parentAttendance"
                        class="flex items-center justify-center gap-1.5 py-3.5 text-sm font-bold text-teachify-blue transition hover:bg-teachify-blue-soft"
                    >
                        <CalendarDays class="h-4 w-4" />
                        Attendance
                    </Link>
                    <Link
                        :href="routes.parentExams"
                        class="flex items-center justify-center gap-1.5 py-3.5 text-sm font-bold text-teachify-blue transition hover:bg-teachify-blue-soft"
                    >
                        <FileText class="h-4 w-4" />
                        Exams
                    </Link>
                </div>
            </article>

            <EmptyState
                v-if="!children.length"
                title="No children linked"
                message="Ask the teacher to link students to this parent account."
            />
        </div>
    </AppShell>
</template>
