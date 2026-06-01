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
const group = ref(props.filters.group ?? '');
let applyTimer = null;

const hasFilters = computed(() => Boolean(search.value || group.value));

const columns = computed(() => [
    { key: 'title', label: t('fields.sessions') },
    { key: 'source_label', label: t('fields.source') },
    { key: 'group', label: t('fields.group') },
    { key: 'starts_at', label: t('fields.starts') },
    { key: 'ends_at', label: t('fields.ends') },
    { key: 'actions', label: t('common.actions') },
]);

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

function exportUrl(format) {
    const params = new URLSearchParams();

    if (search.value) params.set('search', search.value);
    if (group.value) params.set('group', group.value);

    const query = params.toString();

    return query ? `${props.exportUrls[format]}?${query}` : props.exportUrls[format];
}

function downloadExport(format) {
    window.location.href = exportUrl(format);
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
    <Head :title="$t('admin.sessions.title')" />
    <AppShell :title="$t('admin.sessions.title')">
        <section class="mb-5 overflow-visible rounded-[1.8rem] border border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.95)_42%,rgba(15,23,42,0.03))] shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between lg:p-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-teachify-blue">
                        <Filter class="h-3.5 w-3.5" />
                        {{ $t('admin.sessions.filters') }}
                    </div>
                </div>
                <Button :href="createUrl">{{ $t('admin.sessions.addManual') }}</Button>
            </div>

            <form class="border-t border-white/70 bg-white/75 p-4 sm:p-5 lg:p-6" @submit.prevent="submitSearch">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div class="w-full">
                        <TextInput v-model="search" :label="$t('admin.sessions.searchLabel')" :placeholder="$t('admin.sessions.searchPlaceholder')" />
                    </div>
                    <SearchableSelect
                        v-model="group"
                        :label="$t('fields.group')"
                        :options="groupOptions"
                        :placeholder="$t('admin.sessions.groupPlaceholder')"
                        :empty-message="$t('admin.sessions.noGroupMatch')"
                    />
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-teachify-line/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-teachify-muted">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 shadow-sm">
                            <Search class="h-3.5 w-3.5" />
                            {{ hasFilters ? $t('admin.filters.applied') : $t('admin.filters.allSessions') }}
                        </span>
                        <span v-if="search" class="rounded-full bg-teachify-blue-soft px-3 py-2 text-teachify-blue">{{ $t('common.text') }}</span>
                        <span v-if="group" class="rounded-full bg-amber-50 px-3 py-2 text-amber-700">{{ $t('fields.group') }}</span>
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

        <DataTable v-if="sessions.data.length" :columns="columns" :rows="sessions.data">
            <template #source_label="{ row }">
                <span class="rounded-full px-3 py-1 text-xs font-black" :class="row.source_type === 'timetable' ? 'bg-teachify-blue-soft text-teachify-blue' : 'bg-amber-50 text-amber-700'">
                    {{ row.source_type === 'timetable' ? $t('admin.sessions.source.timetable') : $t('admin.sessions.source.manual') }}
                </span>
            </template>
            <template #group="{ row }">{{ row.group?.name || $t('common.noData') }}</template>
            <template #actions="{ row }">
                <ResourceActions :show-url="row.show_url" :edit-url="row.edit_url" :delete-url="row.delete_url" :label="row.title" />
            </template>
        </DataTable>
        <EmptyState
            v-else
            :title="hasFilters ? $t('admin.sessions.noneFound') : $t('admin.sessions.noneYet')"
            :message="hasFilters ? $t('admin.filters.tryDifferentSearchOrFilter') : $t('admin.sessions.noneYetMessage')"
        />
        <Pagination :links="sessions.links" />
    </AppShell>
</template>
