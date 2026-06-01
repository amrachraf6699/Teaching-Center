<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    children: Array,
});
</script>

<template>
    <Head :title="$t('parentPortal.attendanceTitle')" />
    <AppShell :title="$t('parentPortal.attendanceTitle')">
        <div class="space-y-5">
            <article v-for="child in children" :key="child.id" class="teachify-card rounded-[1.6rem] p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-black">{{ child.name }}</h2>
                        <p class="mt-1 text-sm font-bold text-teachify-muted">
                            {{ child.groups.length }} {{ child.groups.length === 1 ? $t('parentPortal.group') : $t('parentPortal.groups') }}
                        </p>
                    </div>
                    <span
                        v-if="child.code"
                        class="rounded-full bg-teachify-blue-soft px-3 py-1 text-sm font-black text-teachify-blue"
                    >
                        {{ child.code }}
                    </span>
                </div>

                <div v-if="child.groups.length" class="mt-5 space-y-4">
                    <div
                        v-for="group in child.groups"
                        :key="group.id"
                        class="rounded-[1.3rem] border border-teachify-line bg-white p-4"
                    >
                        <div class="font-black">{{ group.name }}</div>
                        <div class="text-sm font-semibold text-teachify-muted">{{ group.subject || $t('parentPortal.general') }}</div>

                        <div v-if="group.sessions.length" class="mt-3 space-y-2">
                            <div
                                v-for="session in group.sessions"
                                :key="session.id"
                                class="flex items-center justify-between gap-3 rounded-xl bg-teachify-blue-soft/40 px-3 py-2 text-sm"
                            >
                                <div>
                                    <span class="font-bold">{{ session.title }}</span>
                                    <span v-if="session.starts_at" class="ml-2 text-xs font-medium text-teachify-muted">
                                        {{ session.starts_at }}
                                    </span>
                                </div>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-black capitalize"
                                    :class="session.attendance === 'present'
                                        ? 'bg-teachify-mint-soft text-teachify-ink'
                                        : 'bg-teachify-yellow-soft text-teachify-ink'"
                                >
                                    {{ $t(`attendance.status.${session.attendance || 'pending'}`) }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="mt-3 text-sm font-medium text-teachify-muted">{{ $t('parentPortal.noSessionsRecorded') }}</p>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm font-medium text-teachify-muted">{{ $t('parentPortal.noGroupsAssigned') }}</p>
            </article>

            <EmptyState
                v-if="!children.length"
                :title="$t('dashboard.noChildrenTitle')"
                :message="$t('dashboard.noChildrenMessage')"
            />
        </div>
    </AppShell>
</template>
