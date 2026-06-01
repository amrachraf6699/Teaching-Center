<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import SearchableSelect from '../../../Components/SearchableSelect.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
    recipientOptions: {
        type: Array,
        default: () => [],
    },
    notifications: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    indexUrl: String,
    storeUrl: String,
});

const { t } = useI18n();
const form = useForm({
    student_id: props.filters.student_id ?? '',
    recipient: props.filters.recipient || 'parent',
    type: 'general',
    title: '',
    body: '',
});

const localizedStudents = computed(() => props.students.map((student) => ({
    ...student,
    description: [
        student.parent_name ? t('admin.notifications.parentDescription', { name: student.parent_name }) : null,
        student.student_login_ready ? t('admin.notifications.studentLoginReady') : t('admin.notifications.studentLoginMissing'),
    ].filter(Boolean).join(` ${t('common.separator')} `),
})));
const localizedRecipientOptions = computed(() => props.recipientOptions.map((recipient) => ({
    ...recipient,
    label: t(`admin.notifications.recipients.${recipient.value}`),
})));
const selectedStudent = computed(() => localizedStudents.value.find((student) => student.value === String(form.student_id)) ?? null);
const recipientReady = computed(() => selectedStudent.value?.recipient_state?.[form.recipient] ?? false);
const hasFilters = computed(() => Boolean(props.filters.student_id || props.filters.recipient));

function submit() {
    form.post(props.storeUrl, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('title', 'body');
            form.type = 'general';
        },
    });
}

function filterByStudent(studentId) {
    router.get(props.indexUrl, {
        student_id: studentId || undefined,
        recipient: props.filters.recipient || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function filterByRecipient(recipient) {
    router.get(props.indexUrl, {
        student_id: props.filters.student_id || undefined,
        recipient: recipient || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    router.get(props.indexUrl, {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <Head :title="$t('admin.notifications.title')" />
    <AppShell :title="$t('admin.notifications.title')">
        <section class="teachify-card rounded-[1.6rem] p-5">
            <div>
                <h2 class="text-lg font-black">{{ $t('admin.notifications.send') }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('admin.notifications.sendHelp') }}</p>
            </div>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <SearchableSelect
                    v-model="form.student_id"
                    :label="$t('fields.student')"
                    :options="localizedStudents"
                    :placeholder="$t('admin.notifications.studentPlaceholder')"
                    :empty-message="$t('admin.notifications.noStudentMatch')"
                />

                <div class="grid gap-4 md:grid-cols-2">
                    <SelectInput v-model="form.recipient" :label="$t('fields.sendTo')" :options="localizedRecipientOptions" required />
                    <TextInput v-model="form.type" :label="$t('fields.type')" :placeholder="$t('admin.notifications.typePlaceholder')" :error="form.errors.type" />
                </div>

                <p v-if="selectedStudent" class="text-sm font-medium" :class="recipientReady ? 'text-emerald-700' : 'text-teachify-coral'">
                    {{
                        recipientReady
                            ? $t('admin.notifications.readyMessage', { recipient: $t(`admin.notifications.recipients.${form.recipient}`), student: selectedStudent.label })
                            : $t('admin.notifications.missingMessage', { recipient: $t(`admin.notifications.recipients.${form.recipient}`) })
                    }}
                </p>

                <TextInput v-model="form.title" :label="$t('fields.title')" :error="form.errors.title" required />
                <TextareaInput v-model="form.body" :label="$t('fields.message')" :error="form.errors.body" />

                <div v-if="form.errors.student_id || form.errors.recipient" class="text-sm font-semibold text-teachify-coral">
                    {{ form.errors.student_id || form.errors.recipient }}
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button type="submit" :disabled="form.processing || !form.student_id || !recipientReady">{{ $t('admin.notifications.sendButton') }}</Button>
                </div>
            </form>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-black">{{ $t('admin.notifications.recent') }}</h2>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('admin.notifications.recentHelp') }}</p>
                </div>

                <div class="grid gap-3 md:grid-cols-2 lg:w-[34rem]">
                    <SearchableSelect
                        :model-value="props.filters.student_id ?? ''"
                        :label="$t('fields.filterStudent')"
                        :options="localizedStudents"
                        :placeholder="$t('admin.notifications.allStudents')"
                        :empty-message="$t('admin.notifications.noStudentMatch')"
                        @update:model-value="filterByStudent"
                    />
                    <SelectInput
                        :model-value="props.filters.recipient ?? ''"
                        :label="$t('fields.filterRecipient')"
                        :options="localizedRecipientOptions"
                        :placeholder="$t('admin.notifications.allRecipients')"
                        @update:model-value="filterByRecipient"
                    />
                </div>
            </div>

            <div v-if="hasFilters" class="mt-4">
                <Button type="button" variant="secondary" @click="resetFilters">{{ $t('actions.clearFilters') }}</Button>
            </div>

            <div v-if="notifications.data.length" class="mt-5 space-y-3">
                <article v-for="notification in notifications.data" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-xs font-black uppercase text-teachify-blue">{{ $t(`admin.notifications.recipients.${notification.recipient_role}`) }} {{ $t('common.separator') }} {{ notification.type }}</div>
                            <h3 class="mt-1 font-black">{{ notification.title }}</h3>
                            <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                            <div class="mt-2 text-xs font-bold text-teachify-muted">
                                {{ notification.student_name || $t('common.unknownStudent') }}<span v-if="notification.student_code"> {{ $t('common.separator') }} {{ notification.student_code }}</span>
                                <span v-if="notification.recipient_name"> {{ $t('common.separator') }} {{ notification.recipient_name }}</span>
                            </div>
                        </div>
                        <div class="text-xs font-bold text-teachify-muted">{{ notification.created_at }}</div>
                    </div>
                </article>
            </div>

            <EmptyState v-else :title="$t('admin.notifications.noNotifications')" :message="$t('admin.notifications.noNotificationsMessage')" />

            <Pagination :links="notifications.links" />
        </section>
    </AppShell>
</template>
