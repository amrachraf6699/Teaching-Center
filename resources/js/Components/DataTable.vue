<script setup>
defineProps({
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
                        <slot :name="column.key" :row="row">{{ row[column.key] || '-' }}</slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="divide-y divide-teachify-line md:hidden">
            <article v-for="row in rows" :key="row.id" class="p-4">
                <div v-for="column in columns" :key="column.key" class="flex justify-between gap-4 py-1.5 text-sm">
                    <span class="font-bold text-teachify-muted">{{ column.label }}</span>
                    <span class="text-right font-semibold">
                        <slot :name="column.key" :row="row">{{ row[column.key] || '-' }}</slot>
                    </span>
                </div>
            </article>
        </div>
    </div>
</template>
