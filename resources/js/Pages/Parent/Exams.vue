<script setup>
import { Head } from '@inertiajs/vue3';
import EmptyState from '../../Components/EmptyState.vue';
import AppShell from '../../Layouts/AppShell.vue';

defineProps({
    children: Array,
});
</script>

<template>
    <Head :title="$t('parentPortal.examResultsTitle')" />
    <AppShell :title="$t('parentPortal.examResultsTitle')">
        <div class="space-y-5">
            <article v-for="child in children" :key="child.id" class="teachify-card rounded-[1.6rem] p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-black">{{ child.name }}</h2>
                        <p class="mt-1 text-sm font-bold text-teachify-muted">
                            {{ child.exam_results.length }} {{ child.exam_results.length === 1 ? $t('parentPortal.examResult') : $t('parentPortal.examResults') }}
                        </p>
                    </div>
                    <span
                        v-if="child.code"
                        class="rounded-full bg-teachify-blue-soft px-3 py-1 text-sm font-black text-teachify-blue"
                    >
                        {{ child.code }}
                    </span>
                </div>

                <div v-if="child.exam_results.length" class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="result in child.exam_results"
                        :key="result.id"
                        class="rounded-[1.3rem] border border-teachify-line bg-white p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <span class="font-black">{{ result.title }}</span>
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-sm font-black"
                                :class="result.percentage >= 80
                                    ? 'bg-teachify-mint-soft text-teachify-ink'
                                    : result.percentage >= 50
                                        ? 'bg-teachify-yellow-soft text-teachify-ink'
                                        : 'bg-red-50 text-red-600'"
                            >
                                {{ result.percentage }}%
                            </span>
                        </div>
                        <div class="mt-2 text-sm font-semibold text-teachify-muted">
                            {{ result.score }} / {{ result.max_score }} - {{ result.group }}
                        </div>
                        <div class="mt-1 text-xs font-semibold text-teachify-muted">{{ result.schedule }}</div>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm font-medium text-teachify-muted">{{ $t('parentPortal.noExamResultsPosted') }}</p>
            </article>

            <EmptyState
                v-if="!children.length"
                :title="$t('dashboard.noChildrenTitle')"
                :message="$t('dashboard.noChildrenMessage')"
            />
        </div>
    </AppShell>
</template>
