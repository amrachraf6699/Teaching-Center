<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Button from '../../../Components/Button.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    exam: Object,
    attempt: Object,
    result: Object,
});

const form = useForm({
    exam_question_id: null,
    selected_option_id: null,
});

const remainingSeconds = ref(props.attempt.remaining_seconds ?? 0);
let intervalId = null;

const isSubmitted = computed(() => props.attempt.status !== 'in_progress');

function saveAnswer(questionId, optionId) {
    if (isSubmitted.value) {
        return;
    }

    form.transform(() => ({
        exam_question_id: questionId,
        selected_option_id: optionId,
    })).post(props.exam.answer_url, {
        preserveScroll: true,
    });
}

function submit() {
    form.post(props.exam.submit_url);
}

function tick() {
    if (remainingSeconds.value > 0) {
        remainingSeconds.value -= 1;
        return;
    }

    if (!isSubmitted.value) {
        submit();
    }
}

onMounted(() => {
    if (!isSubmitted.value && remainingSeconds.value > 0) {
        intervalId = window.setInterval(tick, 1000);
    }
});

onBeforeUnmount(() => {
    if (intervalId) {
        window.clearInterval(intervalId);
    }
});

watch(isSubmitted, (value) => {
    if (value && intervalId) {
        window.clearInterval(intervalId);
    }
});
</script>

<template>
    <Head :title="exam.title" />
    <AppShell :title="exam.title">
        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Group</div><div class="mt-1 font-bold">{{ exam.group?.name || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Subject</div><div class="mt-1 font-bold">{{ exam.group?.subject || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Status</div><div class="mt-1 font-bold capitalize">{{ attempt.status }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Time left</div><div class="mt-1 font-bold">{{ remainingSeconds }} sec</div></div>
            </div>

            <div v-if="result" class="mt-5 rounded-[1.4rem] border border-teachify-line bg-white p-4">
                <div class="font-black">Result</div>
                <div class="mt-2 text-lg font-black text-teachify-blue">{{ result.score }} / {{ exam.max_score }} · {{ result.percentage }}%</div>
                <p v-if="result.notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ result.notes }}</p>
            </div>

            <div v-if="!isSubmitted" class="mt-5">
                <Button type="button" :disabled="form.processing" @click="submit">Submit Exam</Button>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Questions</h2>
            <div class="mt-4 space-y-4">
                <article v-for="(question, index) in attempt.questions" :key="question.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-black uppercase text-teachify-muted">Question {{ index + 1 }}</div>
                            <div class="mt-1 font-black text-teachify-ink">{{ question.prompt }}</div>
                        </div>
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-teachify-ink">{{ question.points }} pts</div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <button
                            v-for="option in question.options"
                            :key="option.id"
                            type="button"
                            class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm font-semibold transition"
                            :class="[
                                question.selected_option_id === option.id ? 'border-teachify-blue bg-teachify-blue-soft text-teachify-blue' : 'border-teachify-line bg-white text-teachify-ink',
                                isSubmitted && exam.review_mode === 'question_review' && option.is_correct ? 'ring-2 ring-emerald-300' : '',
                            ]"
                            :disabled="isSubmitted || form.processing"
                            @click="saveAnswer(question.id, option.id)"
                        >
                            <span>{{ option.label }}</span>
                            <span v-if="question.selected_option_id === option.id" class="text-xs font-black uppercase">Selected</span>
                        </button>
                    </div>

                    <div v-if="isSubmitted && exam.review_mode === 'question_review'" class="mt-3 text-sm font-medium text-teachify-muted">
                        <span class="font-black text-teachify-ink">{{ question.earned_points || 0 }}</span> points earned
                        <span v-if="question.is_correct !== null"> · {{ question.is_correct ? 'Correct' : 'Incorrect' }}</span>
                    </div>
                </article>
            </div>
        </section>
    </AppShell>
</template>
