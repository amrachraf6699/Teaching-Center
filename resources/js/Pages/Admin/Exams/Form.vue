<script setup>
import { computed, ref, watch } from 'vue';
import { ChevronDown, ChevronUp, Copy, Trash2 } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';

const props = defineProps({
    form: Object,
    groups: Array,
    questionTypes: Array,
    reviewModes: Array,
    submitLabel: String,
    cancelUrl: {
        type: String,
        default: '',
    },
    heading: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
});

defineEmits(['submit']);

const { t } = useI18n();
const maxScore = computed(() => props.form.questions.reduce((total, question) => total + Number(question.points || 0), 0));
const totalQuestions = computed(() => props.form.questions.length);
const selectedGroup = computed(() => props.groups.find((group) => String(group.id ?? group.value) === String(props.form.teaching_group_id)));
const formHeading = computed(() => props.heading || t('admin.exams.builder'));
const formDescription = computed(() => props.description || t('admin.exams.builderDescription'));
const localizedQuestionTypes = computed(() => props.questionTypes.map((option) => ({
    ...option,
    label: t(`admin.exams.questionTypes.${option.value}`),
})));
const localizedReviewModes = computed(() => props.reviewModes.map((option) => ({
    ...option,
    label: t(`admin.exams.reviewModes.${option.value}`),
})));
const collapsedQuestions = ref({});
const scheduledMinutes = computed(() => {
    if (!props.form.start_at || !props.form.end_at) {
        return null;
    }

    const start = new Date(props.form.start_at);
    const end = new Date(props.form.end_at);
    const diff = Math.round((end.getTime() - start.getTime()) / 60000);

    return Number.isFinite(diff) && diff > 0 ? diff : null;
});

function createQuestion(type = 'true_false') {
    return {
        type,
        prompt: '',
        points: 1,
        correct_boolean: 'true',
        options: type === 'mcq'
            ? [
                { label: '', is_correct: true },
                { label: '', is_correct: false },
            ]
            : [],
    };
}

function addQuestion(type = 'true_false') {
    props.form.questions.push(createQuestion(type));
    expandQuestion(props.form.questions.length - 1);
}

function removeQuestion(index) {
    props.form.questions.splice(index, 1);
    syncCollapsedQuestions();
}

function duplicateQuestion(index) {
    const duplicate = JSON.parse(JSON.stringify(props.form.questions[index]));

    delete duplicate.id;
    duplicate.options = (duplicate.options ?? []).map((option) => ({
        ...option,
        id: undefined,
    }));
    props.form.questions.splice(index + 1, 0, duplicate);
    syncCollapsedQuestions();
    expandQuestion(index + 1);
}

function moveQuestion(index, direction) {
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= props.form.questions.length) {
        return;
    }

    const items = [...props.form.questions];
    const [question] = items.splice(index, 1);
    items.splice(targetIndex, 0, question);
    props.form.questions = items;
}

function syncQuestionType(question) {
    if (question.type === 'true_false') {
        question.correct_boolean = question.correct_boolean || 'true';
        question.options = [];
        return;
    }

    question.correct_boolean = '';
    question.options = [
        { label: '', is_correct: true },
        { label: '', is_correct: false },
    ];
}

function addOption(question) {
    if (question.options.length >= 6) {
        return;
    }

    question.options.push({
        label: '',
        is_correct: false,
    });
}

function removeOption(question, optionIndex) {
    question.options.splice(optionIndex, 1);

    if (!question.options.some((option) => option.is_correct) && question.options.length) {
        question.options[0].is_correct = true;
    }
}

function setCorrectOption(question, optionIndex) {
    question.options = question.options.map((option, index) => ({
        ...option,
        is_correct: index === optionIndex,
    }));
}

function questionTypeLabel(type) {
    return t(`admin.exams.questionTypes.${type}`);
}

function questionSummary(question) {
    const prompt = String(question.prompt || '').trim();

    if (prompt !== '') {
        return prompt.length > 90 ? `${prompt.slice(0, 90)}...` : prompt;
    }

    return question.type === 'mcq' ? t('admin.exams.multipleChoiceQuestion') : t('admin.exams.trueFalseQuestion');
}

function isCollapsed(index) {
    return collapsedQuestions.value[index] === true;
}

function toggleQuestion(index) {
    collapsedQuestions.value = {
        ...collapsedQuestions.value,
        [index]: !isCollapsed(index),
    };
}

function expandQuestion(index) {
    collapsedQuestions.value = {
        ...collapsedQuestions.value,
        [index]: false,
    };
}

function syncCollapsedQuestions() {
    const next = {};

    props.form.questions.forEach((_, index) => {
        next[index] = collapsedQuestions.value[index] ?? false;
    });

    collapsedQuestions.value = next;
}

watch(
    () => props.form.questions.length,
    () => {
        syncCollapsedQuestions();
    },
    { immediate: true },
);
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <section class="teachify-card rounded-[1.6rem] p-5 sm:p-6">
            <div class="flex flex-col gap-4 border-b border-teachify-line pb-5">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-teachify-ink">{{ formHeading }}</h2>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ formDescription }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-teachify-line bg-slate-50 px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.group') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ selectedGroup?.name ?? $t('admin.exams.notSelectedYet') }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-slate-50 px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.questions') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ $t('common.totalCount', { count: totalQuestions }) }}</div>
                    </div>
                    <div class="rounded-2xl border border-teachify-line bg-slate-50 px-4 py-3">
                        <div class="text-xs font-black uppercase tracking-[0.16em] text-teachify-muted">{{ $t('fields.maxScore') }}</div>
                        <div class="mt-1 text-sm font-bold text-teachify-ink">{{ $t('common.points', { count: maxScore }) }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-5 space-y-5">
                <section class="rounded-[1.5rem] border border-teachify-line bg-white p-4 sm:p-5">
                    <div class="mb-4">
                        <h3 class="text-lg font-black text-teachify-ink">{{ $t('admin.exams.detailsStep') }}</h3>
                        <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('admin.exams.detailsHelp') }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <SelectInput v-model="form.teaching_group_id" :label="$t('fields.group')" required :options="groups" :error="form.errors.teaching_group_id" />
                        <TextInput v-model="form.title" :label="$t('fields.title')" required :error="form.errors.title" />
                        <TextInput v-model="form.start_at" :label="$t('fields.startsAt')" type="datetime-local" required :error="form.errors.start_at" />
                        <TextInput v-model="form.end_at" :label="$t('fields.endsAt')" type="datetime-local" required :error="form.errors.end_at" />
                        <TextInput v-model="form.max_allowed_time" :label="$t('fields.studentTimeLimit')" type="number" required :error="form.errors.max_allowed_time" />
                        <SelectInput v-model="form.student_review_mode" :label="$t('fields.studentReviewMode')" required :options="localizedReviewModes" :error="form.errors.student_review_mode" />
                        <label class="block">
                            <span class="text-sm font-bold text-teachify-ink">{{ $t('fields.examWindow') }}</span>
                            <div class="mt-2 flex min-h-12 items-center rounded-2xl border border-teachify-line bg-slate-50 px-4 text-sm font-medium text-teachify-muted">
                                {{ scheduledMinutes ? $t('admin.exams.windowSummary', { minutes: scheduledMinutes }) : $t('admin.exams.windowPlaceholder') }}
                            </div>
                        </label>
                    </div>

                    <div class="mt-4">
                        <TextareaInput v-model="form.notes" :label="$t('fields.notes')" :error="form.errors.notes" />
                    </div>
                </section>

                <section class="rounded-[1.5rem] border border-teachify-line bg-white p-4 sm:p-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-black text-teachify-ink">{{ $t('admin.exams.questionsStep') }}</h3>
                            <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('admin.exams.questionsHelp') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button type="button" variant="secondary" @click="addQuestion('true_false')">{{ $t('admin.exams.addTrueFalse') }}</Button>
                            <Button type="button" variant="secondary" @click="addQuestion('mcq')">{{ $t('admin.exams.addMcq') }}</Button>
                        </div>
                    </div>

                    <p v-if="form.errors.questions" class="mt-3 text-sm font-semibold text-teachify-coral">{{ form.errors.questions }}</p>

                    <div class="mt-5 space-y-4">
                        <article
                            v-for="(question, questionIndex) in form.questions"
                            :key="question.id ?? questionIndex"
                            class="rounded-[1.4rem] border border-teachify-line bg-slate-50 p-4 sm:p-5"
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 text-left text-sm font-black text-teachify-ink"
                                        :aria-label="isCollapsed(questionIndex) ? $t('admin.exams.expandQuestion', { number: questionIndex + 1 }) : $t('admin.exams.collapseQuestion', { number: questionIndex + 1 })"
                                        @click="toggleQuestion(questionIndex)"
                                    >
                                        <ChevronDown
                                            class="h-4 w-4 text-teachify-muted transition-transform"
                                            :class="{ '-rotate-90': isCollapsed(questionIndex) }"
                                        />
                                        <span>{{ $t('admin.exams.questionLabel', { number: questionIndex + 1 }) }}</span>
                                    </button>
                                    <div class="mt-1 text-sm font-medium text-teachify-muted">
                                        {{ $t('admin.exams.questionMeta', { type: questionTypeLabel(question.type), points: $t('common.points', { count: question.points || 0 }), separator: $t('common.separator') }) }}
                                    </div>
                                    <div v-if="isCollapsed(questionIndex)" class="mt-2 text-sm text-teachify-muted">
                                        {{ questionSummary(question) }}
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-teachify-line bg-white text-teachify-muted transition hover:border-teachify-blue hover:text-teachify-blue disabled:cursor-not-allowed disabled:opacity-60"
                                        :title="$t('admin.exams.moveUp')"
                                        :aria-label="$t('admin.exams.moveUp')"
                                        @click="moveQuestion(questionIndex, -1)"
                                    >
                                        <ChevronUp class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-teachify-line bg-white text-teachify-muted transition hover:border-teachify-blue hover:text-teachify-blue disabled:cursor-not-allowed disabled:opacity-60"
                                        :title="$t('admin.exams.moveDown')"
                                        :aria-label="$t('admin.exams.moveDown')"
                                        @click="moveQuestion(questionIndex, 1)"
                                    >
                                        <ChevronDown class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-teachify-line bg-white text-teachify-muted transition hover:border-teachify-blue hover:text-teachify-blue disabled:cursor-not-allowed disabled:opacity-60"
                                        :title="$t('admin.exams.duplicate')"
                                        :aria-label="$t('admin.exams.duplicate')"
                                        @click="duplicateQuestion(questionIndex)"
                                    >
                                        <Copy class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-teachify-line bg-white text-teachify-muted transition hover:border-teachify-coral hover:text-teachify-coral disabled:cursor-not-allowed disabled:opacity-60"
                                        :title="$t('admin.exams.removeQuestion')"
                                        :aria-label="$t('admin.exams.removeQuestion')"
                                        :disabled="form.questions.length === 1"
                                        @click="removeQuestion(questionIndex)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="!isCollapsed(questionIndex)">
                                <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_160px]">
                                    <TextareaInput v-model="question.prompt" :label="$t('fields.prompt')" :error="form.errors[`questions.${questionIndex}.prompt`]" />
                                    <SelectInput
                                        v-model="question.type"
                                        :label="$t('fields.type')"
                                        :options="localizedQuestionTypes"
                                        :error="form.errors[`questions.${questionIndex}.type`]"
                                        @update:model-value="syncQuestionType(question)"
                                    />
                                    <TextInput v-model="question.points" :label="$t('fields.points')" type="number" :error="form.errors[`questions.${questionIndex}.points`]" />
                                </div>

                                <div v-if="question.type === 'true_false'" class="mt-4 max-w-sm">
                                    <SelectInput
                                        v-model="question.correct_boolean"
                                        :label="$t('fields.correctAnswer')"
                                        :options="[
                                            { value: 'true', label: $t('common.true') },
                                            { value: 'false', label: $t('common.false') },
                                        ]"
                                        :error="form.errors[`questions.${questionIndex}.correct_boolean`]"
                                    />
                                </div>

                                <div v-else class="mt-4 rounded-2xl border border-teachify-line bg-white p-4">
                                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="text-sm font-black text-teachify-ink">{{ $t('fields.options') }}</div>
                                        <Button type="button" variant="secondary" @click="addOption(question)" :disabled="question.options.length >= 6">{{ $t('admin.exams.addOption') }}</Button>
                                    </div>
                                    <p v-if="form.errors[`questions.${questionIndex}.options`]" class="mb-3 text-sm font-semibold text-teachify-coral">
                                        {{ form.errors[`questions.${questionIndex}.options`] }}
                                    </p>
                                    <div class="space-y-3">
                                        <div
                                            v-for="(option, optionIndex) in question.options"
                                            :key="option.id ?? optionIndex"
                                            class="grid gap-3 rounded-2xl border border-teachify-line bg-slate-50 p-3 md:grid-cols-[minmax(0,1fr)_170px_auto] md:items-end"
                                        >
                                            <TextInput
                                                v-model="option.label"
                                                :label="$t('admin.exams.optionLabel', { number: optionIndex + 1 })"
                                                :error="form.errors[`questions.${questionIndex}.options.${optionIndex}.label`]"
                                            />
                                            <label class="block">
                                                <span class="text-sm font-bold text-teachify-ink">{{ $t('fields.correct') }}</span>
                                                <div class="mt-2 flex min-h-12 items-center rounded-2xl border border-teachify-line bg-white px-4">
                                                    <input
                                                        :checked="option.is_correct"
                                                        type="radio"
                                                        :name="`question-${questionIndex}-correct`"
                                                        class="h-4 w-4 border-teachify-line text-teachify-blue"
                                                        @change="setCorrectOption(question, optionIndex)"
                                                    />
                                                    <span class="ml-3 text-sm font-medium text-teachify-muted">{{ $t('admin.exams.markCorrect') }}</span>
                                                </div>
                                            </label>
                                            <Button type="button" variant="secondary" @click="removeOption(question, optionIndex)" :disabled="question.options.length <= 2">{{ $t('actions.delete', { name: $t('admin.exams.optionLabel', { number: optionIndex + 1 }) }) }}</Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
            <Button v-if="cancelUrl" variant="secondary" :href="cancelUrl">{{ $t('actions.cancel') }}</Button>
        </div>
    </form>
</template>
