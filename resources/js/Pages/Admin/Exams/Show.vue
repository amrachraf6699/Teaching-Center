<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ exam: Object });
</script>

<template>
    <Head :title="exam.title" />
    <AppShell :title="exam.title">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="exam.edit_url">Edit Exam</Button>
            <Button variant="secondary" :href="exam.index_url">Back to Exams</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Group</div><Link v-if="exam.group" :href="exam.group.show_url" class="mt-1 block font-bold text-teachify-blue">{{ exam.group.name }}</Link></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Schedule</div><div class="mt-1 font-bold">{{ exam.schedule }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Allowed Time</div><div class="mt-1 font-bold">{{ exam.max_allowed_time }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Max Score</div><div class="mt-1 font-bold">{{ exam.max_score }}</div></div>
            </div>
            <p v-if="exam.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ exam.notes }}</p>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Questions</h2>
            <div v-if="exam.questions.length" class="mt-4 space-y-4">
                <article v-for="(question, index) in exam.questions" :key="question.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-xs font-black uppercase text-teachify-muted">Question {{ index + 1 }} · {{ question.type_label }}</div>
                            <div class="mt-1 font-black text-teachify-ink">{{ question.prompt }}</div>
                        </div>
                        <div class="rounded-full bg-teachify-blue-soft px-3 py-1 text-sm font-black text-teachify-blue">{{ question.points }} pts</div>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div v-for="option in question.options" :key="option.id" class="rounded-xl px-3 py-2 text-sm font-semibold" :class="option.is_correct ? 'bg-teachify-mint-soft text-teachify-ink' : 'bg-slate-100 text-slate-700'">
                            {{ option.label }}
                        </div>
                    </div>
                </article>
            </div>
            <EmptyState v-else title="No questions" message="Add questions from the edit screen." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Students</h2>
            <div v-if="exam.students.length" class="mt-4 grid gap-3 sm:grid-cols-2">
                <Link v-for="student in exam.students" :key="student.id" :href="student.show_url" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black text-teachify-blue">{{ student.name }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ student.code || '-' }} · {{ student.parent || 'No parent' }}</div>
                    <div class="mt-2 text-sm font-black">
                        <span v-if="student.result">{{ student.result.score }} / {{ exam.max_score }} · {{ student.result.percentage }}%</span>
                        <span v-else class="text-teachify-muted">No result</span>
                    </div>
                </Link>
            </div>
            <EmptyState v-else title="No students" message="Students assigned to the group will appear here." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Results</h2>
            <div v-if="exam.results.length" class="mt-4 grid gap-3">
                <article v-for="result in exam.results" :key="result.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex justify-between gap-3">
                        <div>
                            <div class="font-black">{{ result.student }}</div>
                            <div class="text-sm font-semibold text-teachify-muted">{{ result.parent || 'No parent' }}</div>
                        </div>
                        <div class="font-black text-teachify-blue">{{ result.score }} · {{ result.percentage }}%</div>
                    </div>
                    <p v-if="result.notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ result.notes }}</p>
                </article>
            </div>
            <EmptyState v-else title="No results" message="Saved exam results will appear here." />
        </section>
    </AppShell>
</template>
