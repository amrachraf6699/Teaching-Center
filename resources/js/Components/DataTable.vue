<script setup>
const props = defineProps({
    columns: {
        type: Array,
        default: () => [],
    },
    rows: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="overflow-hidden rounded-[1.4rem] border border-teachify-line bg-white shadow-sm">
        <table class="hidden w-full text-left text-sm md:table">
            <thead class="bg-teachify-blue-soft text-xs font-black uppercase text-teachify-blue">
                <tr>
                    <th v-for="column in columns" :key="column.key" class="px-4 py-3">{{ column.label }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows" :key="row.id" class="border-t border-teachify-line">
                    <td v-for="column in columns" :key="column.key" class="px-4 py-4 font-medium">
                        <slot :name="column.key" :row="row">{{ row[column.key] ?? '-' }}</slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="space-y-3 bg-slate-50/55 p-3 md:hidden">
            <article v-for="row in rows" :key="row.id" class="p-4">
                <article class="overflow-hidden rounded-[1.45rem] border border-teachify-line bg-white shadow-[0_14px_34px_rgba(15,23,42,0.06)]">
                    <div v-if="props.columns[0]" class="border-b border-teachify-line bg-[linear-gradient(135deg,rgba(37,99,235,0.08),rgba(255,255,255,0.94))] px-4 py-3.5">
                        <div class="text-[11px] font-black uppercase tracking-[0.18em] text-teachify-blue">{{ props.columns[0].label }}</div>
                        <div class="mt-1 text-base font-black leading-6 text-teachify-ink">
                            <slot :name="props.columns[0].key" :row="row">{{ row[props.columns[0].key] ?? '-' }}</slot>
                        </div>
                    </div>

                    <div class="grid gap-3 px-4 py-4">
                        <template v-for="column in props.columns.slice(1)" :key="column.key">
                            <div v-if="column.key !== 'actions'" class="rounded-2xl border border-slate-100 bg-slate-50/75 px-3 py-2.5">
                                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-teachify-muted">{{ column.label }}</div>
                                <div class="mt-1 text-sm font-semibold leading-6 text-teachify-ink">
                                    <slot :name="column.key" :row="row">{{ row[column.key] ?? '-' }}</slot>
                                </div>
                            </div>
                        </template>

                        <div v-if="props.columns.some((column) => column.key === 'actions')" class="border-t border-dashed border-teachify-line pt-3">
                            <div class="mb-2 text-[11px] font-black uppercase tracking-[0.14em] text-teachify-muted">Actions</div>
                            <div class="flex justify-start">
                                <slot name="actions" :row="row">{{ row.actions ?? '-' }}</slot>
                            </div>
                        </div>
                    </div>
                </article>
            </article>
        </div>
    </div>
</template>
