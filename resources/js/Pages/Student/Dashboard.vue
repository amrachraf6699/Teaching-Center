<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    student: Object,
});
</script>

<template>
    <Head title="Student Dashboard" />
    <AppShell title="Student Dashboard">
        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Name</div><div class="mt-1 font-bold">{{ student.name }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Code</div><div class="mt-1 font-bold">{{ student.code }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Groups</div><div class="mt-1 font-bold">{{ student.groups.length }}</div></div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">My groups</h2>
            <div v-if="student.groups.length" class="mt-4 grid gap-3 sm:grid-cols-2">
                <article v-for="group in student.groups" :key="group.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black text-teachify-blue">{{ group.name }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ group.subject || 'General' }}</div>
                </article>
            </div>
            <EmptyState v-else title="No groups yet" message="Your active teaching groups will appear here." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Recent attendance</h2>
            <div v-if="student.attendance.length" class="mt-4 grid gap-3">
                <article v-for="attendance in student.attendance" :key="attendance.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="font-black">{{ attendance.session }}</div>
                            <div class="text-sm font-semibold text-teachify-muted">{{ attendance.group || '-' }} · {{ attendance.starts_at || '-' }}</div>
                        </div>
                        <span class="rounded-full bg-teachify-blue-soft px-3 py-1 text-sm font-black capitalize text-teachify-blue">{{ attendance.status }}</span>
                    </div>
                    <p v-if="attendance.notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ attendance.notes }}</p>
                </article>
            </div>
            <EmptyState v-else title="No attendance yet" message="Scan the session QR code when you arrive at class." />
        </section>
    </AppShell>
</template>
