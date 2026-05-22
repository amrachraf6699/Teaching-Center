<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../Components/Button.vue';
import EmptyState from '../../Components/EmptyState.vue';
import StatTile from '../../Components/StatTile.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    metrics: Array,
    upcomingSessions: Array,
    recentNotifications: Array,
    quickActions: Array,
});
</script>

<template>
    <Head title="Teacher Dashboard" />
    <AppShell title="Teacher Dashboard" subtitle="Manage students, groups, sessions, exams, grades, and parent updates.">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <StatTile v-for="metric in metrics" :key="metric.label" v-bind="metric" />
        </section>

        <section class="mt-6 grid gap-5 xl:grid-cols-[1.4fr_0.8fr]">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black">Upcoming sessions</h2>
                        <p class="text-sm font-medium text-teachify-muted">The next lessons that need attention.</p>
                    </div>
                    <Button variant="secondary" href="/admin/sessions/create">Add Session</Button>
                </div>

                <div v-if="upcomingSessions.length" class="space-y-3">
                    <article v-for="session in upcomingSessions" :key="session.id" class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-black">{{ session.title }}</h3>
                                <p class="text-sm font-semibold text-teachify-muted">{{ session.group || 'No group' }}</p>
                            </div>
                            <div class="rounded-full bg-teachify-yellow-soft px-3 py-1 text-sm font-black text-teachify-ink">{{ session.starts_at }}</div>
                        </div>
                    </article>
                </div>
                <EmptyState v-else title="No sessions scheduled" message="Create the next lesson to keep the week organized." />
            </div>

            <aside class="teachify-card rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Quick actions</h2>
                <div class="mt-4 grid gap-2">
                    <Button v-for="action in quickActions" :key="action.label" :href="action.href" variant="secondary">{{ action.label }}</Button>
                </div>
            </aside>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Recent parent updates</h2>
            <div v-if="recentNotifications.length" class="mt-4 grid gap-3">
                <article v-for="notification in recentNotifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-black">{{ notification.title }}</h3>
                            <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</span>
                    </div>
                </article>
            </div>
            <EmptyState v-else title="No updates yet" message="Attendance and exam results will create parent notifications here." />
        </section>
    </AppShell>
</template>
