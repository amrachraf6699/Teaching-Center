<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import ToggleSwitch from '../../../Components/ToggleSwitch.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ groups: Array, action: String });
const form = useForm({ teaching_group_id: '', title: '', starts_at: '', ends_at: '', attendance_entry_enabled: true, notes: '' });

function submit() {
    form.post(props.action);
}
</script>

<template>
    <Head title="Add Manual Session" />
    <AppShell title="Add Manual Session">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-lg font-black">Manual session exception</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">Use this only for one-off lessons or exceptions. Weekly lessons should come from the timetable.</p>
            </div>
            <SelectInput v-model="form.teaching_group_id" label="Group" required :options="groups" :error="form.errors.teaching_group_id" />
            <TextInput v-model="form.title" label="Title" required :error="form.errors.title" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.starts_at" label="Starts At" type="datetime-local" required :error="form.errors.starts_at" />
                <TextInput v-model="form.ends_at" label="Ends At" type="datetime-local" :error="form.errors.ends_at" />
            </div>
            <label class="flex items-center justify-between rounded-2xl border border-teachify-line bg-slate-50 px-4 py-3">
                <span>
                    <span class="block text-sm font-bold text-teachify-ink">{{ $t('sessions.studentCheckIn') }}</span>
                    <span class="block text-xs font-medium text-teachify-muted">{{ $t('sessions.allowStudentCheckIn') }}</span>
                </span>
                <ToggleSwitch v-model="form.attendance_entry_enabled" />
            </label>
            <TextareaInput v-model="form.notes" label="Notes" :error="form.errors.notes" />
            <Button type="submit" :disabled="form.processing">Create Manual Session</Button>
        </form>
    </AppShell>
</template>
