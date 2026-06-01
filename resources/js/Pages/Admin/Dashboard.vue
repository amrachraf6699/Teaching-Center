<script setup>
import { Head } from '@inertiajs/vue3';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../../Components/Button.vue';
import EmptyState from '../../Components/EmptyState.vue';
import StatTile from '../../Components/StatTile.vue';
import AppShell from '../../Layouts/AppShell.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Tooltip, Legend, Filler);

const props = defineProps({
    metrics: Array,
    charts: {
        type: Object,
        default: () => ({}),
    },
    upcomingSessions: Array,
    recentNotifications: Array,
    quickActions: Array,
});

const { t } = useI18n();

function localizeChart(chart) {
    return {
        labels: chart?.label_keys?.length
            ? chart.label_keys.map((labelKey) => t(labelKey))
            : (chart?.labels ?? []),
        datasets: (chart?.datasets ?? []).map((dataset) => ({
            ...dataset,
            label: dataset.label_key ? t(dataset.label_key) : dataset.label,
        })),
    };
}

const localizedMetrics = computed(() => (props.metrics ?? []).map((metric) => ({
    ...metric,
    label: metric.label_key ? t(metric.label_key) : metric.label,
})));

const localizedQuickActions = computed(() => (props.quickActions ?? []).map((action) => ({
    ...action,
    label: action.label_key ? t(action.label_key) : action.label,
})));

const palette = {
    blue: '#2563eb',
    mint: '#10b981',
    yellow: '#f59e0b',
    coral: '#f97316',
    ink: '#111827',
    muted: '#64748b',
    line: '#e2e8f0',
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                boxWidth: 10,
                color: palette.muted,
                font: { family: 'Nunito Sans', weight: '700' },
            },
        },
        tooltip: {
            backgroundColor: palette.ink,
            titleFont: { family: 'Nunito Sans', weight: '800' },
            bodyFont: { family: 'Nunito Sans', weight: '700' },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: palette.muted, font: { family: 'Nunito Sans', weight: '700' } },
        },
        y: {
            beginAtZero: true,
            ticks: { precision: 0, color: palette.muted, font: { family: 'Nunito Sans', weight: '700' } },
            grid: { color: palette.line },
        },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: chartOptions.plugins,
};

function withBarColors(chart, colors = [palette.blue]) {
    return {
        labels: chart?.labels ?? [],
        datasets: (chart?.datasets ?? []).map((dataset, index) => ({
            ...dataset,
            borderRadius: 12,
            backgroundColor: colors[index] ?? colors[0],
            borderColor: colors[index] ?? colors[0],
        })),
    };
}

const attendanceTrendData = computed(() => ({
    labels: props.charts.attendanceTrend?.labels ?? [],
    datasets: (localizeChart(props.charts.attendanceTrend).datasets ?? []).map((dataset, index) => {
        const colors = [palette.mint, palette.coral, palette.yellow, palette.blue];
        const color = colors[index] ?? palette.blue;

        return {
            ...dataset,
            borderColor: color,
            backgroundColor: `${color}22`,
            pointBackgroundColor: color,
            pointBorderColor: '#ffffff',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
        };
    }),
}));

const weeklySessionsData = computed(() => withBarColors(localizeChart(props.charts.weeklySessions), [palette.blue]));
const studentGrowthData = computed(() => withBarColors(localizeChart(props.charts.studentGrowth), [palette.mint]));
const groupLoadData = computed(() => withBarColors(localizeChart(props.charts.groupLoad), [palette.coral]));
const examPipelineData = computed(() => ({
    labels: localizeChart(props.charts.examPipeline).labels,
    datasets: localizeChart(props.charts.examPipeline).datasets.map((dataset) => ({
        ...dataset,
        backgroundColor: [palette.blue, palette.coral, palette.yellow, palette.mint],
        borderColor: '#ffffff',
        borderWidth: 4,
    })),
}));
</script>

<template>
    <Head :title="$t('dashboard.teacherDashboard')" />
    <AppShell :title="$t('dashboard.teacherDashboard')">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <StatTile v-for="metric in localizedMetrics" :key="metric.label" v-bind="metric" />
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-2">
            <article class="teachify-card rounded-[1.6rem] p-5">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.attendanceTrend') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.attendanceTrendDescription') }}</p>
                </div>
                <div class="mt-5 h-80">
                    <Line :data="attendanceTrendData" :options="chartOptions" />
                </div>
            </article>

            <article class="teachify-card rounded-[1.6rem] p-5">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.weeklySessions') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.weeklySessionsDescription') }}</p>
                </div>
                <div class="mt-5 h-80">
                    <Bar :data="weeklySessionsData" :options="chartOptions" />
                </div>
            </article>

            <article class="teachify-card rounded-[1.6rem] p-5">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.studentGrowth') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.studentGrowthDescription') }}</p>
                </div>
                <div class="mt-5 h-80">
                    <Bar :data="studentGrowthData" :options="chartOptions" />
                </div>
            </article>

            <article class="teachify-card rounded-[1.6rem] p-5">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.examPipeline') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.examPipelineDescription') }}</p>
                </div>
                <div class="mt-5 h-80">
                    <Doughnut :data="examPipelineData" :options="doughnutOptions" />
                </div>
            </article>

            <article class="teachify-card rounded-[1.6rem] p-5 xl:col-span-2">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.topGroupsByLoad') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.topGroupsByLoadDescription') }}</p>
                </div>
                <div class="mt-5 h-80">
                    <Bar :data="groupLoadData" :options="chartOptions" />
                </div>
            </article>
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[1.4fr_0.8fr]">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black">{{ $t('dashboard.upcomingSessions') }}</h2>
                        <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.upcomingSessionsDescription') }}</p>
                    </div>
                    <Button variant="secondary" href="/admin/sessions/create">{{ $t('teacherDashboard.addManualSession') }}</Button>
                </div>

                <div v-if="upcomingSessions.length" class="space-y-3">
                    <article v-for="session in upcomingSessions" :key="session.id" class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-black">{{ session.title }}</h3>
                                <p class="text-sm font-semibold text-teachify-muted">{{ session.group || $t('teacherDashboard.noGroup') }}</p>
                            </div>
                            <div class="rounded-full bg-teachify-yellow-soft px-3 py-1 text-sm font-black text-teachify-ink">{{ session.starts_at }}</div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else :title="$t('teacherDashboard.noSessionsScheduled')" :message="$t('teacherDashboard.noSessionsScheduledMessage')" />
            </div>

            <aside class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">{{ $t('teacherDashboard.quickActions') }}</h2>
                <div class="mt-4 grid gap-2">
                    <Button v-for="action in localizedQuickActions" :key="action.label" :href="action.href" variant="secondary">{{ action.label }}</Button>
                </div>
            </aside>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black">{{ $t('teacherDashboard.recentNotifications') }}</h2>
                    <p class="text-sm font-medium text-teachify-muted">{{ $t('teacherDashboard.recentNotificationsDescription') }}</p>
                </div>
                <Button variant="secondary" href="/admin/notifications">{{ $t('teacherDashboard.manageNotifications') }}</Button>
            </div>
            <div v-if="recentNotifications.length" class="mt-4 grid gap-3">
                <article v-for="notification in recentNotifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-black uppercase tracking-[0.14em] text-teachify-blue">
                                {{ $t(`admin.notifications.recipients.${notification.recipient_role}`) }} {{ $t('common.separator') }} {{ notification.student || $t('teacherDashboard.noStudent') }}
                            </div>
                            <h3 class="font-black">{{ notification.title }}</h3>
                            <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</span>
                    </div>
                </article>
            </div>
            <EmptyState v-else :title="$t('notifications.noNotificationsYet')" :message="$t('teacherDashboard.recentNotificationsDescription')" />
        </section>
    </AppShell>
</template>
