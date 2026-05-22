<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ parent: Object });
</script>

<template>
    <Head :title="parent.name" />
    <AppShell :title="parent.name" subtitle="Parent account, linked children, and portal notifications.">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="parent.edit_url">Edit Parent</Button>
            <Button variant="secondary" :href="parent.index_url">Back to Parents</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Email</div><div class="mt-1 font-bold">{{ parent.email }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Children</div><div class="mt-1 font-bold">{{ parent.children_count }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Created</div><div class="mt-1 font-bold">{{ parent.created_at }}</div></div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Children</h2>
            <div v-if="parent.children.length" class="mt-4 grid gap-4">
                <article v-for="child in parent.children" :key="child.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <Link :href="child.show_url" class="font-black text-teachify-blue">{{ child.name }}</Link>
                            <div class="text-sm font-semibold text-teachify-muted">{{ child.code || '-' }} · {{ child.phone || '-' }}</div>
                        </div>
                        <div class="text-sm font-bold text-teachify-muted">{{ child.groups.length }} groups · {{ child.exam_results.length }} results · {{ child.notifications.length }} notifications</div>
                    </div>
                    <div v-if="child.groups.length" class="mt-3 flex flex-wrap gap-2">
                        <span v-for="group in child.groups" :key="group.id" class="rounded-full bg-teachify-blue-soft px-3 py-1 text-xs font-black text-teachify-blue">{{ group.name }}</span>
                    </div>
                </article>
            </div>
            <EmptyState v-else title="No children linked" message="Add students to connect them to this parent." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Notifications</h2>
            <div v-if="parent.notifications.length" class="mt-4 grid gap-3">
                <article v-for="notification in parent.notifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="text-xs font-black uppercase text-teachify-blue">{{ notification.type }} · {{ notification.student || 'No student' }}</div>
                    <h3 class="mt-1 font-black">{{ notification.title }}</h3>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                    <div class="mt-2 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</div>
                </article>
            </div>
            <EmptyState v-else title="No notifications" message="Attendance and exam updates will appear here." />
        </section>
    </AppShell>
</template>
