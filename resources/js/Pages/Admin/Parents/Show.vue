<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import AppShell from '../../../Layouts/AppShell.vue';

defineProps({ parent: Object });
</script>

<template>
    <Head :title="parent.name" />
    <AppShell :title="parent.name">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="parent.edit_url">{{ $t('admin.parents.edit') }}</Button>
            <Button variant="secondary" :href="parent.index_url">{{ $t('admin.parents.back') }}</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.email') }}</div><div class="mt-1 font-bold">{{ parent.email }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.children') }}</div><div class="mt-1 font-bold">{{ parent.children_count }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">{{ $t('fields.created') }}</div><div class="mt-1 font-bold">{{ parent.created_at }}</div></div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">{{ $t('fields.children') }}</h2>
            <div v-if="parent.children.length" class="mt-4 grid gap-4">
                <article v-for="child in parent.children" :key="child.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <Link :href="child.show_url" class="font-black text-teachify-blue">{{ child.name }}</Link>
                            <div class="text-sm font-semibold text-teachify-muted">{{ child.code || $t('common.noData') }} {{ $t('common.separator') }} {{ child.phone || $t('common.noData') }}</div>
                        </div>
                        <div class="text-sm font-bold text-teachify-muted">{{ $t('admin.parents.childSummary', { groups: child.groups.length, results: child.exam_results.length, notifications: child.notifications.length, separator: $t('common.separator') }) }}</div>
                    </div>
                    <div v-if="child.groups.length" class="mt-3 flex flex-wrap gap-2">
                        <span v-for="group in child.groups" :key="group.id" class="rounded-full bg-teachify-blue-soft px-3 py-1 text-xs font-black text-teachify-blue">{{ group.name }}</span>
                    </div>
                </article>
            </div>
            <EmptyState v-else :title="$t('admin.parents.noChildren')" :message="$t('admin.parents.noChildrenMessage')" />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">{{ $t('admin.parents.notifications') }}</h2>
            <div v-if="parent.notifications.length" class="mt-4 grid gap-3">
                <article v-for="notification in parent.notifications" :key="notification.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="text-xs font-black uppercase text-teachify-blue">{{ $t(`admin.notifications.recipients.${notification.recipient_role}`) }} {{ $t('common.separator') }} {{ notification.type }} {{ $t('common.separator') }} {{ notification.student || $t('common.noStudent') }}</div>
                    <h3 class="mt-1 font-black">{{ notification.title }}</h3>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ notification.body }}</p>
                    <div class="mt-2 text-xs font-bold text-teachify-muted">{{ notification.created_at }}</div>
                </article>
            </div>
            <EmptyState v-else :title="$t('admin.parents.noNotifications')" :message="$t('admin.parents.noNotificationsMessage')" />
        </section>
    </AppShell>
</template>
