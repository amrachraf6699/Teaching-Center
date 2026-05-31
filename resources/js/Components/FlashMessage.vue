<script setup>
import { CheckCircle2, X } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const page = usePage();
const status = computed(() => page.props.flash?.status);
const visible = ref(false);

let timeoutId = null;

function clearTimer() {
    if (timeoutId) {
        window.clearTimeout(timeoutId);
        timeoutId = null;
    }
}

function dismiss() {
    visible.value = false;
    clearTimer();
}

watch(status, (message) => {
    clearTimer();

    if (!message) {
        visible.value = false;

        return;
    }

    visible.value = true;
    timeoutId = window.setTimeout(() => {
        visible.value = false;
        timeoutId = null;
    }, 3200);
}, { immediate: true });

onBeforeUnmount(() => {
    clearTimer();
});
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="status && visible"
            class="pointer-events-auto fixed right-4 top-4 z-50 w-[calc(100vw-2rem)] max-w-sm overflow-hidden rounded-[1.5rem] border border-emerald-200 bg-white/95 shadow-[0_20px_55px_rgba(16,185,129,0.22)] backdrop-blur sm:right-6 sm:top-6"
        >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 via-teachify-mint to-teachify-blue" />
            <div class="flex items-start gap-3 p-4">
                <div class="mt-0.5 grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <CheckCircle2 class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">{{ $t('common.success') }}</p>
                    <p class="mt-1 text-sm font-semibold leading-6 text-teachify-ink">{{ status }}</p>
                </div>
                <button
                    type="button"
                    class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-teachify-muted transition hover:bg-slate-100 hover:text-teachify-ink"
                    :aria-label="$t('common.dismissNotification')"
                    @click="dismiss"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </transition>
</template>
