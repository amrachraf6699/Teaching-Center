<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    children: Array,
    notifications: Array,
});
</script>

<template>
    <Head title="Parent Portal" />
    <AppShell title="Parent Portal">
        <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
            <section class="space-y-5">
                <article v-for="child in children" :key="child.id" class="teachify-card rounded-[1.6rem] p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-black">{{ child.name }}</h2>
                            <p class="mt-1 text-sm font-bold text-teachify-muted">{{ child.groups.length }} groups assigned</p>
                        </div>
                        <span v-if="child.code" class="rounded-full bg-teachify-blue-soft px-3 py-1 text-sm font-black text-teachify-blue">{{ child.code }}</span>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <section class="rounded-[1.3rem] border border-teachify-line bg-white p-4">
                            <h3 class="font-black">Groups and sessions</h3>
                            <div v-if="child.groups.length" class="mt-3 space-y-3">
                                <div v-for="group in child.groups" :key="group.id" class="rounded-2xl bg-teachify-blue-soft/60 p-3">
                                    <div class="font-black">{{ group.name }}</div>
                                    <div class="text-sm font-semibold text-teachify-muted">{{ group.subject || 'General' }}</div>
                                    <div class="mt-3 space-y-2">
                                        <div v-for="session in group.sessions" :key="session.id" class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 text-sm">
                                            <span class="font-bold">{{ session.title }}</span>
                                            <span class="rounded-full px-2 py-1 text-xs font-black capitalize" :class="session.attendance === 'present' ? 'bg-teachify-mint-soft text-teachify-ink' : 'bg-teachify-yellow-soft text-teachify-ink'">
                                                {{ session.attendance || 'pending' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="mt-3 text-sm font-medium text-teachify-muted">No groups assigned yet.</p>
                        </section>

                        <section class="rounded-[1.3rem] border border-teachify-line bg-white p-4">
                            <h3 class="font-black">Exam Results</h3>
                            <div v-if="child.exam_results.length" class="mt-3 space-y-3">
                                <div v-for="result in child.exam_results" :key="result.id" class="rounded-2xl border border-teachify-line p-3">
                                    <div class="flex justify-between gap-3">
                                        <span class="font-black">{{ result.title }}</span>
                                        <span class="font-black text-teachify-blue">{{ result.percentage }}%</span>
                                    </div>
                                    <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ result.score }} / {{ result.max_score }} - {{ result.group }}</div>
                                </div>
                            </div>
                            <p v-else class="mt-3 text-sm font-medium text-teachify-muted">No exam results posted yet.</p>
                        </section>
                    </div>
                </article>

                <EmptyState v-if="!children.length" title="No children linked" message="Ask the teacher to link students to this parent account." />
            </section>

            <aside class="teachify-card h-fit rounded-[1.6rem] p-5">
                <h2 class="text-lg font-black">Notifications</h2>
                <div v-if="notifications.length" class="mt-4 space-y-3">
                    <article v-for="notification in notifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                        <div class="text-xs font-black uppercase text-teachify-blue">{{ notification.type }}</div>
                        <h3 class="mt-1 font-black">{{ notification.title }}</h3>
                        <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</div>
                    </article>
                </div>
                <p v-else class="mt-4 text-sm font-medium text-teachify-muted">No notifications yet.</p>
            </aside>
        </div>
    </AppShell>
</template>
