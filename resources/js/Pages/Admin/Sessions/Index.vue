<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Filter, RotateCcw, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import SearchableSelect from '../../../Components/SearchableSelect.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    sessions: Object,
    createUrl: String,
    indexUrl: String,
    groupOptions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');
const group = ref(props.filters.group ?? '');
let applyTimer = null;

const hasFilters = computed(() => Boolean(search.value || group.value));

const columns = [
    { key: 'title', label: 'Session' },
    { key: 'group', label: 'Group' },
    { key: 'starts_at', label: 'Starts' },
    { key: 'ends_at', label: 'Ends' },
    { key: 'actions', label: 'Actions' },
];

function submitSearch() {
    router.get(props.indexUrl, {
        search: search.value || undefined,
        group: group.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function scheduleApply(delay = 0) {
    if (applyTimer) {
        window.clearTimeout(applyTimer);
    }

    applyTimer = window.setTimeout(() => {
        submitSearch();
        applyTimer = null;
    }, delay);
}

function resetSearch() {
    search.value = '';
    group.value = '';
}

watch(search, () => {
    scheduleApply(300);
});

watch(group, () => {
    scheduleApply();
});

onBeforeUnmount(() => {
    if (applyTimer) {
        window.clearTimeout(applyTimer);
    }
});
</script>

<template>
    <Head title="Sessions" />
    <AppShell title="Sessions">
        <section class="mb-5 overflow-visible rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(15,23,42,0.03))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between lg:p-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <Filter class="h-3.5 w-3.5" />
                        Session filters
                    </div>
                </div>
                <Button :href="createUrl">Add Session</Button>
            </div>

            <form class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6" @submit.prevent="submitSearch">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div class="w-full">
                        <TextInput v-model="search" label="Session search" placeholder="Title, notes, or group subject" />
                    </div>
                    <SearchableSelect
                        v-model="group"
                        label="Group"
                        :options="groupOptions"
                        placeholder="Search by group name"
                        empty-message="No matching group found."
                    />
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-teachify-line/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-teachify-muted">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 shadow-sm">
                            <Search class="h-3.5 w-3.5" />
                            {{ hasFilters ? 'Filters applied' : 'All sessions' }}
                        </span>
                        <span v-if="search" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">Text</span>
                        <span v-if="group" class="rounded-full bg-amber-50 px-3 py-2 text-amber-700">Group</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button v-if="hasFilters" type="button" variant="secondary" @click="resetSearch">
                            <RotateCcw class="h-4 w-4" />
                            Reset
                        </Button>
                    </div>
                </div>
            </form>
        </section>

        <DataTable v-if="sessions.data.length" :columns="columns" :rows="sessions.data">
            <template #group="{ row }">{{ row.group?.name || '-' }}</template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.title" />
            </template>
        </DataTable>
        <EmptyState
            v-else
            :title="hasFilters ? 'No sessions found' : 'No sessions yet'"
            :message="hasFilters ? 'Try a different search term or filter.' : 'Schedule lessons before recording attendance.'"
        />
        <Pagination :links="sessions.links" />
    </AppShell>
</template>
