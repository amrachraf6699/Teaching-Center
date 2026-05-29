<script setup>
import { Head, Link } from '@inertiajs/vue3';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({
    upcomingExams: Array,
    conductedExams: Array,
});
</script>

<template>
    <Head title="Exams" />
    <AppShell title="Exams">
        <section class="grid gap-6 xl:grid-cols-2">
            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-black text-teachify-ink">Upcoming exams</h2>
                    <p class="text-sm font-medium text-teachify-muted">Scheduled and available exams for your groups.</p>
                </div>
                <div v-if="upcomingExams.length" class="space-y-3">
                    <Link v-for="exam in upcomingExams" :key="exam.id" :href="exam.show_url" class="block rounded-2xl border border-teachify-line bg-white p-4 transition hover:border-teachify-blue">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-black text-teachify-ink">{{ exam.title }}</div>
                                <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ exam.group?.name || '-' }} · {{ exam.group?.subject || '-' }}</div>
                            </div>
                            <span class="rounded-full bg-teachify-blue-soft px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-teachify-blue">{{ exam.availability }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ exam.schedule }}</div>
                    </Link>
                </div>
                <EmptyState v-else title="No upcoming exams" message="Scheduled exams will appear here." />
            </div>

            <div class="teachify-card rounded-[1.6rem] p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-black text-teachify-ink">Conducted exams</h2>
                    <p class="text-sm font-medium text-teachify-muted">Submitted or closed exams with results and review rules.</p>
                </div>
                <div v-if="conductedExams.length" class="space-y-3">
                    <Link v-for="exam in conductedExams" :key="exam.id" :href="exam.show_url" class="block rounded-2xl border border-teachify-line bg-white p-4 transition hover:border-teachify-blue">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-black text-teachify-ink">{{ exam.title }}</div>
                                <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ exam.group?.name || '-' }} · {{ exam.group?.subject || '-' }}</div>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-[0.12em] text-teachify-ink">{{ exam.attempt_status || exam.availability }}</span>
                        </div>
                        <div class="mt-2 text-xs font-bold text-teachify-muted">{{ exam.schedule }}</div>
                        <div v-if="exam.score !== null && exam.score !== undefined" class="mt-2 text-sm font-black text-teachify-blue">{{ exam.score }} / {{ exam.max_score }} · {{ exam.percentage }}%</div>
                    </Link>
                </div>
                <EmptyState v-else title="No conducted exams" message="Submitted or closed exams will appear here." />
            </div>
        </section>
    </AppShell>
</template>
