<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ timetable: Object });
const { t } = useI18n();
const pageTitle = computed(() => (props.timetable.group?.name ? t('admin.timetables.showTitle', { group: props.timetable.group.name }) : t('admin.timetables.title')));
</script>

<template>
    <Head :title="pageTitle" />
    <AppShell :title="pageTitle">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="timetable.edit_url">{{ $t('admin.timetables.edit') }}</Button>
            <Button variant="secondary" :href="timetable.index_url">{{ $t('admin.timetables.back') }}</Button>
            <Button v-if="timetable.group" variant="secondary" :href="timetable.group.show_url">{{ $t('admin.timetables.viewGroup') }}</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.group') }}</div>
                    <div class="mt-1 font-bold">{{ timetable.group?.name || $t('common.noData') }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.subject') }}</div>
                    <div class="mt-1 font-bold">{{ timetable.group?.subject || $t('common.noData') }}</div>
                </div>
                <div>
                    <div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.created') }}</div>
                    <div class="mt-1 font-bold">{{ timetable.created_at }}</div>
                </div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">{{ $t('fields.weeklySchedule') }}</h2>
            <div v-if="timetable.entries.length" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <article v-for="entry in timetable.entries" :key="entry.day" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="text-sm font-black text-teachify-blue">{{ $t(`weekdays.${entry.day}`) }}</div>
                    <div class="mt-1 text-sm font-semibold text-teachify-muted">{{ entry.time_range }}</div>
                </article>
            </div>
            <EmptyState v-else :title="$t('admin.timetables.noActiveDays')" :message="$t('admin.timetables.noActiveDaysMessage')" />
        </section>
    </AppShell>
</template>
