<script setup>
import { ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    label: String,
    modelValue: [String, Number],
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
    emptyMessage: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
const { t } = useI18n();

const isOpen = ref(false);
const query = ref('');

const normalizedValue = computed(() => String(props.modelValue ?? ''));
const selectedOption = computed(() => props.options.find((option) => String(option.value ?? option.id) === normalizedValue.value) ?? null);
const placeholderLabel = computed(() => props.placeholder || t('forms.searchAndSelect'));
const emptyLabel = computed(() => props.emptyMessage || t('forms.noMatches'));
const filteredOptions = computed(() => {
    const term = query.value.trim().toLowerCase();

    if (term === '') {
        return props.options;
    }

    return props.options.filter((option) => {
        const label = String(option.label ?? option.name ?? '').toLowerCase();
        const description = String(option.description ?? '').toLowerCase();

        return label.includes(term) || description.includes(term);
    });
});

watch(selectedOption, (option) => {
    if (!isOpen.value) {
        query.value = option?.label ?? option?.name ?? '';
    }
}, { immediate: true });

function open() {
    isOpen.value = true;
    query.value = '';
}

function close() {
    isOpen.value = false;
    query.value = selectedOption.value?.label ?? selectedOption.value?.name ?? '';
}

function selectOption(option) {
    emit('update:modelValue', String(option.value ?? option.id));
    isOpen.value = false;
    query.value = option.label ?? option.name ?? '';
}

function clearSelection() {
    emit('update:modelValue', '');
    isOpen.value = false;
    query.value = '';
}
</script>

<template>
    <label class="block">
        <span class="text-sm font-bold text-teachify-ink">{{ label }}</span>

        <div class="relative z-30 mt-2">
            <div class="flex min-h-12 items-center rounded-2xl border border-teachify-line bg-white px-3 shadow-sm transition focus-within:border-teachify-blue focus-within:ring-4 focus-within:ring-teachify-blue-soft">
                <Search class="h-4 w-4 shrink-0 text-teachify-muted" />
                <input
                    v-model="query"
                    type="text"
                    :placeholder="selectedOption ? selectedOption.label : placeholderLabel"
                    class="w-full bg-transparent px-3 py-3 text-sm font-medium text-teachify-ink outline-none"
                    @focus="open"
                    @blur="setTimeout(close, 150)"
                    @input="isOpen = true"
                />
                <button
                    v-if="selectedOption"
                    type="button"
                    class="grid h-8 w-8 place-items-center rounded-full text-teachify-muted transition hover:bg-slate-100 hover:text-teachify-ink"
                    :aria-label="t('forms.clearNamed', { name: label })"
                    @mousedown.prevent
                    @click="clearSelection"
                >
                    <X class="h-4 w-4" />
                </button>
                <ChevronDown class="h-4 w-4 shrink-0 text-teachify-muted" />
            </div>

            <div
                v-if="isOpen"
                class="absolute left-0 right-0 top-[calc(100%+0.45rem)] z-50 overflow-hidden rounded-[1.3rem] border border-teachify-line bg-white shadow-[0_18px_50px_rgba(15,23,42,0.14)]"
            >
                <div v-if="filteredOptions.length" class="max-h-72 overflow-y-auto p-2">
                    <button
                        v-for="option in filteredOptions"
                        :key="option.value ?? option.id"
                        type="button"
                        class="flex w-full items-start justify-between gap-3 rounded-2xl px-3 py-3 text-left transition hover:bg-teachify-blue-soft"
                        @mousedown.prevent
                        @click="selectOption(option)"
                    >
                        <span>
                            <span class="block text-sm font-bold text-teachify-ink">{{ option.label ?? option.name }}</span>
                            <span v-if="option.description" class="mt-0.5 block text-xs font-medium text-teachify-muted">{{ option.description }}</span>
                        </span>
                        <span
                            v-if="String(option.value ?? option.id) === normalizedValue"
                            class="rounded-full bg-teachify-blue-soft px-2 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-teachify-blue"
                        >
                            {{ t('common.selected') }}
                        </span>
                    </button>
                </div>
                <div v-else class="px-4 py-5 text-sm font-medium text-teachify-muted">{{ emptyLabel }}</div>
            </div>
        </div>
    </label>
</template>
