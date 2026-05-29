<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, RotateCcw, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import ImportActions from '../../../Components/ImportActions.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import SearchableSelect from '../../../Components/SearchableSelect.vue';
import TextInput from '../../../Components/TextInput.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    timetables: Object,
    createUrl: String,
    indexUrl: String,
    filters: {
        type: Object,
        default: () => ({}),
    },
    groupOptions: {
        type: Array,
        default: () => [],
    },
    exportUrls: {
        type: Object,
        default: () => ({}),
    },
    importOptions: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const groupId = ref(props.filters.group_id ?? '');
const hasFilters = computed(() => Boolean(search.value || groupId.value));
let applyTimer = null;

const columns = [
    { key: 'group_name', label: 'Group' },
    { key: 'subject', label: 'Subject' },
    { key: 'active_days_count', label: 'Days' },
    { key: 'weekly_summary', label: 'Weekly schedule' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions' },
];

function submitSearch() {
    router.get(props.indexUrl, {
        search: search.value || undefined,
        group_id: groupId.value || undefined,
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

function exportUrl(format) {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (groupId.value) params.set('group_id', groupId.value);

    const query = params.toString();

    return query ? `${props.exportUrls[format]}?${query}` : props.exportUrls[format];
}

function downloadExport(format) {
    window.location.href = exportUrl(format);
}

function resetSearch() {
    search.value = '';
    groupId.value = '';
}

watch(search, () => {
    scheduleApply(300);
});

watch(groupId, () => {
    scheduleApply();
});

onBeforeUnmount(() => {
    if (applyTimer) {
        window.clearTimeout(applyTimer);
    }
});
</script>

<template>
    <Head title="Timetables" />
    <AppShell title="Timetables">
        <section class="mb-5 overflow-visible rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(15,23,42,0.03))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between lg:p-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <Filter class="h-3.5 w-3.5" />
                        Timetable filters
                    </div>
                </div>
                <Button :href="createUrl">Add Timetable</Button>
            </div>

            <form class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6" @submit.prevent="submitSearch">
                <div class="grid gap-3 xl:grid-cols-2">
                    <TextInput v-model="search" label="Timetable search" placeholder="Search by group or subject" />
                    <SearchableSelect
                        v-model="groupId"
                        label="Group"
                        :options="groupOptions"
                        placeholder="Filter by group"
                        empty-message="No groups found."
                    />
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-teachify-line/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-teachify-muted">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 shadow-sm">
                            <Search class="h-3.5 w-3.5" />
                            {{ hasFilters ? 'Filters applied' : 'All timetables' }}
                        </span>
                        <span v-if="search" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">Text</span>
                        <span v-if="groupId" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">Group</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="secondary" @click="downloadExport('csv')">
                            <Download class="h-4 w-4" />
                            CSV
                        </Button>
                        <Button type="button" variant="secondary" @click="downloadExport('pdf')">
                            <Download class="h-4 w-4" />
                            PDF
                        </Button>
                        <Button v-if="hasFilters" type="button" variant="secondary" @click="resetSearch">
                            <RotateCcw class="h-4 w-4" />
                            Reset
                        </Button>
                    </div>
                </div>
            </form>

            <div class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6">
                <ImportActions :imports="importOptions" />
            </div>
        </section>

        <DataTable v-if="timetables.data.length" :columns="columns" :rows="timetables.data">
            <template #weekly_summary="{ row }">
                <span class="text-sm leading-6">{{ row.weekly_summary || '-' }}</span>
            </template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.group_name" />
            </template>
        </DataTable>
        <EmptyState
            v-else
            :title="hasFilters ? 'No timetables found' : 'No timetables yet'"
            :message="hasFilters ? 'Try a different search term.' : 'Create the first weekly timetable for a teaching group.'"
        />
        <Pagination :links="timetables.links" />
    </AppShell>
</template>
