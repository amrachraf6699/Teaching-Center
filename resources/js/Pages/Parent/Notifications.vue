<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    notifications: Object,
});
</script>

<template>
    <Head title="Notifications" />
    <AppShell title="Notifications">
        <div class="space-y-3">
            <template v-if="notifications.data.length">
                <article
                    v-for="n in notifications.data"
                    :key="n.id"
                    class="teachify-card rounded-[1.6rem] p-5"
                    :class="{ 'opacity-75': n.read_at }"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="text-xs font-black uppercase text-teachify-blue">{{ n.type }}</div>
                        <div v-if="!n.read_at" class="h-2 w-2 shrink-0 rounded-full bg-teachify-blue mt-1" />
                    </div>
                    <h3 class="mt-1 font-black">{{ n.title }}</h3>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">{{ n.body }}</p>
                    <div class="mt-3 text-xs font-bold text-teachify-muted">{{ n.created_at }}</div>
                </article>

                <div v-if="notifications.last_page > 1" class="flex justify-center gap-2 pt-2">
                    <a
                        v-for="link in notifications.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="flex h-9 min-w-9 items-center justify-center rounded-xl border border-teachify-line px-2 text-sm font-bold transition"
                        :class="link.active
                            ? 'bg-teachify-blue text-white border-teachify-blue'
                            : link.url
                                ? 'text-teachify-muted hover:bg-teachify-blue-soft'
                                : 'cursor-default text-teachify-muted/40'"
                    />
                </div>
            </template>

            <EmptyState
                v-else
                title="No notifications"
                message="You have no notifications yet."
            />
        </div>
    </AppShell>
</template>
