<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    timetable: Object,
    action: String,
    showUrl: String,
});

const form = useForm({
    entries: props.timetable.entries,
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

    form.put(props.action);
}
</script>

<template>
    <Head :title="$t('admin.timetables.edit')" />
    <AppShell :title="$t('admin.timetables.edit')">
        <form class="teachify-card max-w-4xl space-y-5 rounded-[1.6rem] p-5" @submit.prevent="submit">
            <div>
                <h2 class="text-lg font-black">{{ $t('admin.timetables.weekly') }}</h2>
                <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('admin.timetables.editHelp') }}</p>
            </div>
            <section class="rounded-[1.4rem] border border-teachify-line bg-white p-4">
                <div class="text-xs font-black uppercase tracking-[0.18em] text-teachify-muted">{{ $t('fields.group') }}</div>
                <div class="mt-2 text-lg font-black text-teachify-ink">{{ timetable.group?.name }}</div>
                <div class="mt-1 text-sm font-medium text-teachify-muted">{{ timetable.group?.subject || $t('common.noSubject') }}</div>
            </section>

            <section>
                <h2 class="text-sm font-black">{{ $t('fields.weeklySchedule') }}</h2>
                <div class="mt-3 space-y-3">
                    <article v-for="(entry, index) in form.entries" :key="entry.day" class="rounded-[1.4rem] border border-teachify-line bg-white p-4">
                        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto_minmax(0,0.9fr)_minmax(0,0.9fr)] md:items-center">
                            <div>
                                <div class="text-sm font-black text-teachify-ink">{{ $t(`weekdays.${entry.day}`) }}</div>
                                <div class="mt-1 text-xs font-medium text-teachify-muted">{{ $t('admin.timetables.inactiveDayHelp') }}</div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-bold text-teachify-muted">
                                <input v-model="entry.active" type="checkbox" class="h-4 w-4 rounded border-teachify-line text-teachify-blue" />
                                {{ $t('common.active') }}
                            </label>
                            <label class="block">
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">{{ $t('fields.starts') }}</span>
                                <input v-model="entry.starts_at" type="time" :disabled="!entry.active" class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium outline-none transition disabled:bg-slate-100 disabled:text-teachify-muted" />
                            </label>
                            <label class="block">
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-teachify-muted">{{ $t('fields.ends') }}</span>
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

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">{{ $t('admin.timetables.save') }}</Button>
                <Button variant="secondary" :href="showUrl">{{ $t('actions.cancel') }}</Button>
            </div>
        </form>
    </AppShell>
</template>
