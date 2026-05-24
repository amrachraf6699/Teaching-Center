<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ timetable: Object });
</script>

<template>
    <Head :title="timetable.group?.name ? `${timetable.group.name} Timetable` : 'Timetable'" />
    <AppShell :title="timetable.group?.name ? `${timetable.group.name} Timetable` : 'Timetable'">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="timetable.edit_url">Edit Timetable</Button>
            <Button variant="secondary" :href="timetable.index_url">Back to Timetables</Button>
            <Button v-if="timetable.group" variant="secondary" :href="timetable.group.show_url">View Group</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">Group</div>
                    <div class="mt-1 font-bold">{{ timetable.group?.name || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">Subject</div>
                    <div class="mt-1 font-bold">{{ timetable.group?.subject || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">Created</div>
                    <div class="mt-1 font-bold">{{ timetable.created_at }}</div>
                </div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Weekly schedule</h2>
            <div v-if="timetable.entries.length" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <article v-for="entry in timetable.entries" :key="entry.day_label" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="text-sm font-black text-teachify-blue">{{ entry.day_label }}</div>
                    <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ entry.time_range }}</div>
                </article>
            </div>
            <EmptyState v-else title="No active days" message="Edit the timetable and activate at least one weekday." />
        </section>
    </AppShell>
</template>
