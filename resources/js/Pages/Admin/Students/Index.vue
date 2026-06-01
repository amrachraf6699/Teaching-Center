<script setup>
import { Head, router } from '@inertiajs/vue3';
import { Download, Filter, RotateCcw, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../../../Components/Button.vue';
import DataTable from '../../../Components/DataTable.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import ImportActions from '../../../Components/ImportActions.vue';
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
    importOptions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const { t } = useI18n();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const parentId = ref(props.filters.parent_id ?? '');
const groupId = ref(props.filters.group_id ?? '');
const togglingIds = ref([]);
let applyTimer = null;

const statusOptions = computed(() => [
    { value: 'active', label: t('admin.filters.activeOnly') },
    { value: 'inactive', label: t('admin.filters.inactiveOnly') },
]);

const hasFilters = computed(() => Boolean(search.value || status.value || parentId.value || groupId.value));

const columns = computed(() => [
    { key: 'name', label: '' },
    { key: 'code', label: t('fields.code') },
    { key: 'parent', label: t('fields.parent') },
    { key: 'groups', label: t('fields.groups') },
    { key: 'phone', label: t('fields.phone') },
    { key: 'status', label: t('fields.status') },
    { key: 'created_at', label: t('fields.created') },
    { key: 'actions', label: t('common.actions') },
]);

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
    <Head :title="$t('admin.students.title')" />
    <AppShell :title="$t('admin.students.title')">
        <section class="mb-5 overflow-visible rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(15,23,42,0.03))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between lg:p-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <Filter class="h-3.5 w-3.5" />
                        {{ $t('admin.students.filters') }}
                    </div>
                </div>
                <Button :href="createUrl">{{ $t('admin.students.add') }}</Button>
            </div>

            <form class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6" @submit.prevent="submitSearch">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
                    <div class="md:col-span-2 xl:col-span-1">
                        <TextInput v-model="search" :label="$t('admin.students.searchLabel')" :placeholder="$t('admin.students.searchPlaceholder')" />
                    </div>
                    <SearchableSelect
                        v-model="parentId"
                        :label="$t('fields.parent')"
                        :options="parentOptions"
                        :placeholder="$t('admin.students.parentPlaceholder')"
                        :empty-message="$t('admin.students.noParentMatch')"
                    />
                    <SearchableSelect
                        v-model="groupId"
                        :label="$t('fields.group')"
                        :options="groupOptions"
                        :placeholder="$t('admin.students.groupPlaceholder')"
                        :empty-message="$t('admin.students.noGroupMatch')"
                    />
                    <SelectInput v-model="status" :label="$t('fields.status')" :options="statusOptions" :placeholder="$t('admin.filters.allStatuses')" />
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-teachify-line/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-teachify-muted">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 shadow-sm">
                            <Search class="h-3.5 w-3.5" />
                            {{ hasFilters ? $t('admin.filters.applied') : $t('admin.filters.allStudents') }}
                        </span>
                        <span v-if="search" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">{{ $t('common.text') }}</span>
                        <span v-if="parentId" class="rounded-full bg-emerald-50 px-3 py-2 text-emerald-700">{{ $t('fields.parent') }}</span>
                        <span v-if="groupId" class="rounded-full bg-amber-50 px-3 py-2 text-amber-700">{{ $t('fields.group') }}</span>
                        <span v-if="status" class="rounded-full bg-slate-100 px-3 py-2 text-slate-700">{{ status === 'active' ? $t('common.active') : $t('common.inactive') }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="secondary" @click="downloadExport('csv')">
                            <Download class="h-4 w-4" />
                            {{ $t('actions.csv') }}
                        </Button>
                        <Button type="button" variant="secondary" @click="downloadExport('pdf')">
                            <Download class="h-4 w-4" />
                            {{ $t('actions.pdf') }}
                        </Button>
                        <Button v-if="hasFilters" type="button" variant="secondary" @click="resetSearch">
                            <RotateCcw class="h-4 w-4" />
                            {{ $t('actions.reset') }}
                        </Button>
                    </div>
                </div>
            </form>

            <div class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6">
                <ImportActions :imports="importOptions" />
            </div>
        </section>

        <DataTable v-if="students.data.length" :columns="columns" :rows="students.data">
            <template #parent="{ row }">{{ row.parent?.name || $t('common.noData') }}</template>
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
                <span v-else>{{ $t('common.noData') }}</span>
            </template>
            <template #status="{ row }">
                <div class="flex items-center justify-end gap-3 md:justify-start">
                    <ToggleSwitch :model-value="row.is_active" :disabled="isToggling(row.id)" @change="toggleStatus(row, $event)" />
                    <span class="text-xs font-black uppercase tracking-[0.16em]" :class="row.is_active ? 'text-emerald-600' : 'text-slate-500'">
                        {{ row.is_active ? $t('common.active') : $t('common.inactive') }}
                    </span>
                </div>
            </template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.name" />
            </template>
        </DataTable>
        <EmptyState
            v-else
            :title="hasFilters ? $t('admin.students.noneFound') : $t('admin.students.noneYet')"
            :message="hasFilters ? $t('admin.filters.tryDifferentSearchOrFilter') : $t('admin.students.noneYetMessage')"
        />
        <Pagination :links="students.links" />
    </AppShell>
</template>
