<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextareaInput from '../../../Components/TextareaInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ session: Object, groups: Array, action: String, showUrl: String });
const form = useForm({ ...props.session });

function submit() {
    form.put(props.action);
}
</script>

<template>
    <Head title="Edit Session" />
    <AppShell title="Edit Session">
        <form class="teachify-card max-w-3xl space-y-4 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-lg font-black">Manual session details</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">Saving here keeps this lesson as a manual exception outside the weekly timetable.</p>
            </div>
            <SelectInput v-model="form.teaching_group_id" label="Group" required :options="groups" :error="form.errors.teaching_group_id" />
            <TextInput v-model="form.title" label="Title" required :error="form.errors.title" />
            <div class="grid gap-4 sm:grid-cols-2">
                <TextInput v-model="form.starts_at" label="Starts At" type="datetime-local" required :error="form.errors.starts_at" />
                <TextInput v-model="form.ends_at" label="Ends At" type="datetime-local" :error="form.errors.ends_at" />
            </div>
            <TextareaInput v-model="form.notes" label="Notes" :error="form.errors.notes" />
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">Save Session</Button>
                <Button variant="secondary" :href="showUrl">Cancel</Button>
            </div>
        </form>
    </AppShell>
</template>
