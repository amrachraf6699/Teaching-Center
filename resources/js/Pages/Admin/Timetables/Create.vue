<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import SearchableSelect from '../../../Components/SearchableSelect.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    groups: Array,
    weekdays: Array,
    action: String,
});

const form = useForm({
    teaching_group_id: '',
    entries: props.weekdays.map((day) => ({
        day: day.value,
        label: day.label,
        active: false,
        starts_at: '',
        ends_at: '',
    })),
});

function submit() {
    form.transform((data) => ({
        ...data,
        entries: data.entries.map((entry) => ({
            ...entry,
            active: Boolean(entry.active),
            starts_at: entry.active ? entry.starts_at : '',
            ends_at: entry.active ? entry.ends_at : '',
        })),
    }));

    form.post(props.action);
}
</script>

<template>
    <Head title="Add Timetable" />
    <AppShell title="Add Timetable">
        <form v-if="groups.length" class="teachify-card max-w-4xl space-y-5 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <SearchableSelect
                v-model="form.teaching_group_id"
                label="Group"
                :options="groups"
                placeholder="Search and select a group"
                empty-message="No matching group found."
            />
            <p v-if="form.errors.teaching_group_id" class="text-sm font-semibold text-teachify-coral">{{ form.errors.teaching_group_id }}</p>

            <section>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-black">Weekly schedule</h2>
                    <p class="text-sm font-medium text-teachify-muted">Enable each day the group should meet, then set its time range.</p>
                </div>

                <div class="mt-3 space-y-3">
                    <article v-for="(entry, index) in form.entries" :key="entry.day" class="rounded-[1.4rem] border border-teachify-line bg-white p-4">
                        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto_minmax(0,0.9fr)_minmax(0,0.9fr)] md:items-center">
                            <div>
                                <div class="text-sm font-black text-teachify-ink">{{ entry.label }}</div>
                                <div class="mt-1 text-xs font-medium text-teachify-muted">Mark the day active and choose the group session time.</div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-bold text-teachify-muted">
                                <input v-model="entry.active" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                                Active
                            </label>
                            <label class="block">
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Starts</span>
                                <input v-model="entry.starts_at" type="time" :disabled="!entry.active" class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium outline-none transition disabled:bg-slate-100 disabled:text-teachify-muted" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">Ends</span>
                                <input v-model="entry.ends_at" type="time" :disabled="!entry.active" class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium outline-none transition disabled:bg-slate-100 disabled:text-teachify-muted" />
                            </label>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-4 text-sm font-semibold text-teachify-coral">
                            <span v-if="form.errors[`entries.${index}.starts_at`]">{{ form.errors[`entries.${index}.starts_at`] }}</span>
                            <span v-if="form.errors[`entries.${index}.ends_at`]">{{ form.errors[`entries.${index}.ends_at`] }}</span>
                        </div>
                    </article>
                </div>

                <p v-if="form.errors.entries" class="mt-3 text-sm font-semibold text-teachify-coral">{{ form.errors.entries }}</p>
            </section>

            <Button type="submit" :disabled="form.processing">Create Timetable</Button>
        </form>

        <EmptyState v-else title="All groups already have timetables" message="Create another group first or edit an existing timetable instead." />
    </AppShell>
</template>
