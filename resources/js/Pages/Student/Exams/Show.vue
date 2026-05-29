<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    exam: Object,
});

function start() {
    router.post(props.exam.start_url);
}
</script>

<template>
    <Head :title="exam.title" />
    <AppShell :title="exam.title">
        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Group</div><div class="mt-1 font-bold">{{ exam.group?.name || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Subject</div><div class="mt-1 font-bold">{{ exam.group?.subject || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Schedule</div><div class="mt-1 font-bold">{{ exam.schedule }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Allowed Time</div><div class="mt-1 font-bold">{{ exam.max_allowed_time }} min</div></div>
            </div>
            <p v-if="exam.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ exam.notes }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <Button v-if="exam.start_url" type="button" @click="start">Start Exam</Button>
                <Button v-else-if="exam.attempt_url" :href="exam.attempt_url">Open Attempt</Button>
                <Button variant="secondary" :href="exam.index_url">Back to Exams</Button>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Exam status</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                    <div class="text-xs font-black uppercase text-teachify-muted">Availability</div>
                    <div class="mt-1 text-sm font-bold capitalize">{{ exam.availability }}</div>
                </div>
                <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                    <div class="text-xs font-black uppercase text-teachify-muted">Questions</div>
                    <div class="mt-1 text-sm font-bold">{{ exam.questions_count }}</div>
                </div>
                <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                    <div class="text-xs font-black uppercase text-teachify-muted">Max score</div>
                    <div class="mt-1 text-sm font-bold">{{ exam.max_score }}</div>
                </div>
                <div class="rounded-2xl border border-teachify-line bg-white px-4 py-3">
                    <div class="text-xs font-black uppercase text-teachify-muted">Review mode</div>
                    <div class="mt-1 text-sm font-bold capitalize">{{ exam.review_mode.replace('_', ' ') }}</div>
                </div>
            </div>

            <div v-if="exam.score !== null && exam.score !== undefined" class="mt-5 rounded-[1.4rem] border border-teachify-line bg-white p-4">
                <div class="font-black text-teachify-ink">Result</div>
                <div class="mt-2 text-lg font-black text-teachify-blue">{{ exam.score }} / {{ exam.max_score }} · {{ exam.percentage }}%</div>
                <p v-if="exam.result_notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ exam.result_notes }}</p>
                <Link v-if="exam.attempt_url" :href="exam.attempt_url" class="mt-3 inline-flex text-sm font-bold text-teachify-blue">Open result view</Link>
            </div>
        </section>
    </AppShell>
</template>
