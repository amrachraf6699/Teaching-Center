<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, RotateCcw, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import Pagination from '../../../Components/Pagination.vue';
import ResourceActions from '../../../Components/ResourceActions.vue';
import SearchableSelect from '../../../Components/SearchableSelect.vue';
import SelectInput from '../../../Components/SelectInput.vue';
import TextInput from '../../../Components/TextInput.vue';
import ToggleSwitch from '../../../Components/ToggleSwitch.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({
    students: Object,
    createUrl: String,
    indexUrl: String,
    parentOptions: {
        type: Array,
        default: () => [],
    },
    groupOptions: {
        type: Array,
        default: () => [],
    },
    exportUrls: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const parentId = ref(props.filters.parent_id ?? '');
const groupId = ref(props.filters.group_id ?? '');
const togglingIds = ref([]);
let applyTimer = null;

const statusOptions = [
    { value: 'active', label: 'Active only' },
    { value: 'inactive', label: 'Inactive only' },
];

const hasFilters = computed(() => Boolean(search.value || status.value || parentId.value || groupId.value));

const columns = [
    { key: 'name', label: '' },
    { key: 'code', label: 'Code' },
    { key: 'parent', label: 'Parent' },
    { key: 'groups', label: 'Groups' },
    { key: 'phone', label: 'Phone' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: 'Actions' },
];

function submitSearch() {
    router.get(props.indexUrl, {
        search: search.value || undefined,
        status: status.value || undefined,
        parent_id: parentId.value || undefined,
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
    if (status.value) params.set('status', status.value);
    if (parentId.value) params.set('parent_id', parentId.value);
    if (groupId.value) params.set('group_id', groupId.value);

    const query = params.toString();

    return query ? `${props.exportUrls[format]}?${query}` : props.exportUrls[format];
}

function downloadExport(format) {
    window.location.href = exportUrl(format);
}

function resetSearch() {
    search.value = '';
    status.value = '';
    parentId.value = '';
    groupId.value = '';
}

function isToggling(studentId) {
    return togglingIds.value.includes(studentId);
}

function truncate(value, length = 3) {
    if (!value || value.length <= length) {
        return value ?? '';
    }

    return `${value.slice(0, length)}...`;
}

function toggleStatus(row, nextValue) {
    togglingIds.value = [...togglingIds.value, row.id];

    router.patch(row.toggle_status_url, {
        is_active: nextValue,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            togglingIds.value = togglingIds.value.filter((id) => id !== row.id);
        },
    });
}

watch(search, () => {
    scheduleApply(300);
});

watch([status, parentId, groupId], () => {
    scheduleApply();
});

onBeforeUnmount(() => {
    if (applyTimer) {
        window.clearTimeout(applyTimer);
    }
});
</script>

<template>
    <Head title="Students" />
    <AppShell title="Students">
        <section class="mb-5 overflow-visible rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(15,23,42,0.03))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between lg:p-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <Filter class="h-3.5 w-3.5" />
                        Student filters
                    </div>
                </div>
                <Button :href="createUrl">Add Student</Button>
            </div>

            <form class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6" @submit.prevent="submitSearch">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
                    <div class="md:col-span-2 xl:col-span-1">
                        <TextInput v-model="search" label="Student search" placeholder="Name, code, phone, or parent email" />
                    </div>
                    <SearchableSelect
                        v-model="parentId"
                        label="Parent"
                        :options="parentOptions"
                        placeholder="Search parent by name or email"
                        empty-message="No matching parent found."
                    />
                    <SearchableSelect
                        v-model="groupId"
                        label="Group"
                        :options="groupOptions"
                        placeholder="Search group by name or subject"
                        empty-message="No matching group found."
                    />
                    <SelectInput v-model="status" label="Status" :options="statusOptions" placeholder="All statuses" />
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-teachify-line/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-teachify-muted">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 shadow-sm">
                            <Search class="h-3.5 w-3.5" />
                            {{ hasFilters ? 'Filters applied' : 'All students' }}
                        </span>
                        <span v-if="search" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">Text</span>
                        <span v-if="parentId" class="rounded-full bg-emerald-50 px-3 py-2 text-emerald-700">Parent</span>
                        <span v-if="groupId" class="rounded-full bg-amber-50 px-3 py-2 text-amber-700">Group</span>
                        <span v-if="status" class="rounded-full bg-slate-100 px-3 py-2 text-slate-700">{{ status }}</span>
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
        </section>

        <DataTable v-if="students.data.length" :columns="columns" :rows="students.data">
            <template #parent="{ row }">{{ row.parent?.name || '-' }}</template>
            <template #groups="{ row }">
                <div v-if="row.groups?.length" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="group in row.groups"
                        :key="group.id"
                        class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700"
                    >
                        {{ truncate(group.name, 3) }}
                    </span>
                </div>
                <span v-else>-</span>
            </template>
            <template #status="{ row }">
                <div class="flex items-center justify-end gap-3 md:justify-start">
                    <ToggleSwitch :model-value="row.is_active" :disabled="isToggling(row.id)" @change="toggleStatus(row, $event)" />
                    <span class="text-xs font-black uppercase tracking-[0.16em]" :class="row.is_active ? 'text-emerald-600' : 'text-slate-500'">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.name" />
            </template>
        </DataTable>
        <EmptyState
            v-else
            :title="hasFilters ? 'No students found' : 'No students yet'"
            :message="hasFilters ? 'Try a different search term or filter.' : 'Add students and connect them to parent accounts.'"
        />
        <Pagination :links="students.links" />
    </AppShell>
</template>
