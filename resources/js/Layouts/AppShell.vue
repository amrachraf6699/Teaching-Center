<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { BrowserQRCodeReader } from '@zxing/browser';
import {
    Bell,
    BookOpen,
    CalendarDays,
    FileText,
    GraduationCap,
    House,
    KeyRound,
    LayoutDashboard,
    LogOut,
    ScanLine,
    Settings,
    Users,
    UserRound,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FlashMessage from '../Components/FlashMessage.vue';
import LanguageToggle from '../Components/LanguageToggle.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const page = usePage();
const { t } = useI18n();
const user = computed(() => page.props.auth?.user);
const brand = computed(() => page.props.brand ?? { name: 'Teachify' });
const routes = computed(() => page.props.routes ?? {});
const direction = computed(() => page.props.direction ?? 'ltr');
const currentPath = computed(() => {
    const url = typeof page.url === 'string' ? page.url : window.location.pathname;

    return new URL(url, window.location.origin).pathname;
});

const unreadCount = computed(() => page.props.parentUnreadCount ?? 0);
const recentNotifications = computed(() => page.props.parentRecentNotifications ?? []);

const bellOpen = ref(false);
const attendanceModalOpen = ref(false);
const scannerReady = ref(false);
const scannerSupported = ref(false);
const scanning = ref(false);
const scannerError = ref('');
const video = ref(null);
const codeForm = useForm({
    code: '',
});

const codeReader = new BrowserQRCodeReader();
let scannerControls = null;
let mediaStream = null;

function toggleBell() {
    bellOpen.value = !bellOpen.value;
    if (bellOpen.value && unreadCount.value > 0) {
        router.post(routes.value.parentNotificationsMarkRead, {}, { preserveState: true, preserveScroll: true });
    }
}

function closeBell(e) {
    if (!e.target.closest('[data-bell]')) {
        bellOpen.value = false;
    }
}

async function openAttendanceModal() {
    attendanceModalOpen.value = true;
    codeForm.reset();
    codeForm.clearErrors();
    scannerError.value = '';
    if (user.value?.role === 'student') {
        await nextTick();
        startScanner();
    }
}

function closeAttendanceModal() {
    attendanceModalOpen.value = false;
    stopScanner();
}

async function startScanner() {
    if (!attendanceModalOpen.value) {
        return;
    }

    if (scanning.value) {
        return;
    }

    if (!window.isSecureContext) {
        scannerError.value = t('scanner.requiresHttps');
        return;
    }

    if (!('mediaDevices' in navigator) || !('getUserMedia' in navigator.mediaDevices)) {
        scannerError.value = t('scanner.notAvailable');
        return;
    }

    try {
        scannerError.value = '';
        scannerSupported.value = true;
        scanning.value = true;

        const preview = await waitForVideoPreview();

        if (!preview) {
            scanning.value = false;
            scannerError.value = t('scanner.previewNotReady');
            return;
        }

        mediaStream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: { facingMode: { ideal: 'environment' } },
        });

        scannerControls = await codeReader.decodeFromStream(
            mediaStream,
            preview,
            (result, error, controls) => {
                if (!result) {
                    return;
                }

                controls.stop();
                scannerControls = null;
                scannerReady.value = false;
                scanning.value = false;
                window.location.href = result.getText();
            },
        );
        scannerReady.value = true;
    } catch (error) {
        scannerSupported.value = false;
        scanning.value = false;
        scannerError.value = t('scanner.unableToOpen');
    }
}

async function waitForVideoPreview() {
    for (let attempt = 0; attempt < 20; attempt += 1) {
        await nextTick();

        if (video.value) {
            return video.value;
        }

        await new Promise((resolve) => window.setTimeout(resolve, 50));
    }

    return null;
}

function stopScanner() {
    scanning.value = false;
    scannerReady.value = false;

    if (scannerControls) {
        scannerControls.stop();
        scannerControls = null;
    }

    if (mediaStream) {
        mediaStream.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }
}

function submitAttendanceCode() {
    codeForm.transform((data) => ({
        ...data,
        code: String(data.code || '').toUpperCase().trim(),
    })).post(routes.value.studentAttendanceLookupByCode, {
        onSuccess: () => {
            closeAttendanceModal();
        },
    });
}

onMounted(() => document.addEventListener('click', closeBell));
onUnmounted(() => document.removeEventListener('click', closeBell));
onBeforeUnmount(() => stopScanner());

const adminNav = computed(() => [
    { label: t('nav.dashboard'), href: routes.value.adminDashboard, icon: LayoutDashboard },
    { label: t('nav.students'), href: routes.value.adminStudents, icon: GraduationCap },
    { label: t('nav.parents'), href: routes.value.adminParents, icon: Users },
    { label: t('nav.groups'), href: routes.value.adminGroups, icon: BookOpen },
    { label: t('nav.timetables'), href: routes.value.adminTimetables, icon: CalendarDays },
    { label: t('nav.sessions'), href: routes.value.adminSessions, icon: CalendarDays },
    { label: t('nav.exams'), href: routes.value.adminExams, icon: FileText },
    { label: t('nav.notifications'), href: routes.value.adminNotifications, icon: Bell },
    { label: t('nav.settings'), href: routes.value.adminSettings, icon: Settings },
]);

const parentNav = computed(() => [
    { label: t('nav.portal'), href: routes.value.parentDashboard, icon: UserRound },
    { label: t('nav.attendance'), href: routes.value.parentAttendance, icon: CalendarDays },
    { label: t('nav.exams'), href: routes.value.parentExams, icon: FileText },
]);

const studentNav = computed(() => [
    { label: t('nav.home'), href: routes.value.studentHome, icon: House },
    { label: t('nav.sessions'), href: routes.value.studentSessions, icon: CalendarDays },
    { label: t('nav.exams'), href: routes.value.studentExams, icon: FileText },
    { label: t('nav.password'), href: routes.value.studentPassword, icon: KeyRound },
]);

const navItems = computed(() => {
    const items = user.value?.role === 'teacher'
        ? adminNav.value
        : user.value?.role === 'student'
            ? studentNav.value
            : parentNav.value;

    return items.filter((item) => typeof item.href === 'string' && item.href.length > 0);
});

function isActive(href) {
    try {
        return currentPath.value.startsWith(new URL(href, window.location.origin).pathname);
    } catch {
        return false;
    }
}

function logout() {
    if (routes.value.logout) {
        router.post(routes.value.logout);
    }
}
</script>

<template>
    <div class="min-h-screen pb-24 text-teachify-ink lg:pb-0">
        <aside
            v-if="user?.role === 'teacher' || user?.role === 'student'"
            class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-teachify-line bg-white/92 px-5 py-5 shadow-[12px_0_35px_rgba(37,99,235,0.06)] backdrop-blur lg:block"
        >
            <Link :href="user?.role === 'student' ? routes.studentHome : routes.adminDashboard" class="flex items-center gap-3">
                <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="h-10 w-auto rounded-xl" />
                <span v-else class="grid h-11 w-11 place-items-center rounded-2xl bg-teachify-blue text-lg font-bold text-white">T</span>
                <span>
                    <span class="block text-lg font-bold">{{ brand.name }}</span>
                    <span class="block text-xs font-medium text-teachify-muted">{{ user?.role === 'student' ? $t('brand.studentWorkspace') : brand.tagline || $t('brand.teacherWorkspace') }}</span>
                </span>
            </Link>

            <button
                v-if="user?.role === 'student'"
                type="button"
                class="mt-6 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-teachify-blue px-4 py-2.5 text-sm font-bold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700"
                @click="openAttendanceModal"
            >
                <ScanLine class="h-4 w-4" />
                {{ $t('nav.scanOrEnterCode') }}
            </button>

            <nav class="mt-8 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold text-teachify-muted transition hover:bg-teachify-blue-soft hover:text-teachify-blue"
                    :class="{ 'bg-teachify-blue text-white hover:bg-teachify-blue hover:text-white': isActive(item.href) }"
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div :class="user?.role === 'teacher' || user?.role === 'student' ? 'lg:pl-72' : ''">
            <header class="sticky top-0 z-20 border-b border-teachify-line bg-white/88 backdrop-blur">
                <div dir="ltr" class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <Link :href="user?.role === 'parent' ? routes.parentDashboard : user?.role === 'student' ? routes.studentHome : routes.adminDashboard" class="flex items-center gap-3 lg:hidden">
                        <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="h-9 w-auto rounded-xl" />
                        <span v-else class="grid h-10 w-10 place-items-center rounded-2xl bg-teachify-blue text-base font-bold text-white">T</span>
                        <span class="font-bold">{{ brand.name }}</span>
                    </Link>

                    <div class="hidden min-w-0 flex-1 lg:block" :dir="direction">
                        <h1 class="truncate text-left text-xl font-bold tracking-normal">{{ props.title }}</h1>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <button
                            v-if="user?.role === 'student'"
                            type="button"
                            class="hidden min-h-11 items-center gap-2 rounded-2xl border border-teachify-line bg-white px-4 py-2.5 text-sm font-bold text-teachify-ink shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue sm:inline-flex"
                            @click="openAttendanceModal"
                        >
                            <ScanLine class="h-4 w-4" />
                            {{ $t('nav.scanOrCode') }}
                        </button>

                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-bold">{{ user?.name }}</div>
                            <div class="text-xs font-medium capitalize text-teachify-muted">{{ user?.role }}</div>
                        </div>

                        <div v-if="user?.role === 'parent'" class="relative" data-bell>
                            <button
                                type="button"
                                class="relative grid h-11 w-11 place-items-center rounded-2xl border border-teachify-line bg-white text-teachify-muted shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
                                :aria-label="$t('notifications.title')"
                                @click.stop="toggleBell"
                            >
                                <Bell class="h-5 w-5" />
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-teachify-blue text-[10px] font-black text-white"
                                >
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>

                            <div
                                v-if="bellOpen"
                                class="absolute right-0 top-full z-50 mt-2 w-80 rounded-[1.4rem] border border-teachify-line bg-white shadow-[0_12px_40px_rgba(37,99,235,0.14)]"
                                data-bell
                            >
                                <div class="flex items-center justify-between border-b border-teachify-line px-4 py-3">
                                    <span class="font-black">{{ $t('notifications.title') }}</span>
                                    <Link :href="routes.parentNotifications" class="text-xs font-bold text-teachify-blue hover:underline" @click="bellOpen = false">{{ $t('actions.viewAll') }}</Link>
                                </div>

                                <div v-if="recentNotifications.length" class="max-h-80 space-y-2 overflow-y-auto p-3">
                                    <div
                                        v-for="n in recentNotifications"
                                        :key="n.id"
                                        class="rounded-2xl p-3 transition"
                                        :class="n.read_at ? 'bg-gray-50' : 'bg-teachify-blue-soft'"
                                    >
                                        <div class="text-[10px] font-black uppercase text-teachify-blue">{{ n.type }}</div>
                                        <div class="mt-0.5 text-sm font-black">{{ n.title }}</div>
                                        <p class="mt-0.5 line-clamp-2 text-xs font-medium text-teachify-muted">{{ n.body }}</p>
                                        <div class="mt-1 text-[10px] font-bold text-teachify-muted">{{ n.created_at }}</div>
                                    </div>
                                </div>
                                <p v-else class="px-4 py-5 text-sm font-medium text-teachify-muted">{{ $t('notifications.noNotificationsYet') }}</p>
                            </div>
                        </div>

                        <LanguageToggle />

                        <button
                            type="button"
                            class="grid h-11 w-11 place-items-center rounded-2xl border border-teachify-line bg-white text-teachify-muted shadow-sm transition hover:border-teachify-coral hover:text-teachify-coral"
                            :aria-label="$t('actions.logout')"
                            @click="logout"
                        >
                            <LogOut class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
                <div class="mb-5 lg:hidden" :dir="direction">
                    <h1 class="text-left text-2xl font-bold tracking-normal">{{ props.title }}</h1>
                </div>

                <FlashMessage />
                <slot />
            </main>
        </div>

        <nav class="fixed inset-x-3 bottom-3 z-40 overflow-x-auto rounded-[1.6rem] border border-teachify-line bg-white/95 p-2 shadow-[0_18px_45px_rgba(37,99,235,0.16)] backdrop-blur lg:hidden">
            <div class="flex min-w-max gap-1">
                <button
                    v-if="user?.role === 'student'"
                    type="button"
                    class="flex min-w-20 flex-col items-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-bold text-teachify-blue"
                    @click="openAttendanceModal"
                >
                    <ScanLine class="h-5 w-5" />
                    <span>{{ $t('nav.scan') }}</span>
                </button>

                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex min-w-20 flex-col items-center gap-1 rounded-2xl px-2 py-2 text-[11px] font-bold text-teachify-muted"
                    :class="{ 'bg-teachify-blue text-white': isActive(item.href) }"
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    <span>{{ item.label }}</span>
                </Link>
            </div>
        </nav>

        <div v-if="attendanceModalOpen && user?.role === 'student'" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[calc(100vh-3rem)] w-full max-w-4xl overflow-y-auto rounded-[1.8rem] border border-teachify-line bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="flex items-start justify-between gap-4 border-b border-teachify-line px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-xl font-black text-teachify-ink">{{ $t('scanner.title') }}</h2>
                        <p class="mt-1 text-sm font-medium text-teachify-muted">{{ $t('scanner.description') }}</p>
                    </div>
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-2xl border border-teachify-line text-teachify-muted transition hover:border-teachify-blue hover:text-teachify-blue" @click="closeAttendanceModal">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)]">
                    <div class="overflow-hidden rounded-[1.6rem] border border-teachify-line bg-slate-950">
                        <video ref="video" playsinline muted class="aspect-[4/3] w-full object-cover"></video>
                    </div>

                    <aside class="space-y-4 rounded-[1.6rem] border border-teachify-line bg-slate-50 p-4">
                        <div class="rounded-2xl bg-white px-4 py-3 text-sm font-medium text-teachify-muted">
                            <span class="font-black text-teachify-ink">{{ $t('scanner.status') }}</span>
                            <span v-if="scannerReady && scanning" class="text-teachify-blue">{{ $t('scanner.live') }}</span>
                            <span v-else-if="scanning && !scannerError" class="text-teachify-blue">{{ $t('scanner.waitingPermission') }}</span>
                            <span v-else-if="scannerSupported && !scanning" class="text-teachify-muted">{{ $t('scanner.stopped') }}</span>
                            <span v-else-if="scannerError" class="text-amber-700">{{ $t('scanner.unavailable') }}</span>
                            <span v-else>{{ $t('scanner.preparing') }}</span>
                        </div>

                        <div v-if="scannerError" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                            {{ scannerError }}
                        </div>

                        <form class="space-y-3 rounded-[1.4rem] border border-teachify-line bg-white p-4" @submit.prevent="submitAttendanceCode">
                            <label class="block">
                                <span class="text-sm font-bold text-teachify-ink">{{ $t('scanner.sessionCode') }}</span>
                                <input
                                    v-model="codeForm.code"
                                    type="text"
                                    placeholder="SES-1234"
                                    class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium uppercase outline-none transition focus:border-teachify-blue focus:ring-4 focus:ring-teachify-blue-soft"
                                />
                            </label>
                            <span v-if="codeForm.errors.code" class="block text-sm font-semibold text-teachify-coral">{{ codeForm.errors.code }}</span>
                            <button
                                type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-teachify-blue px-4 py-2.5 text-sm font-bold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="codeForm.processing"
                            >
                                {{ $t('actions.openSession') }}
                            </button>
                        </form>

                        <div class="space-y-3 text-sm font-medium text-teachify-muted">
                            <div class="rounded-2xl bg-white px-4 py-3">{{ $t('scanner.teacherCanDisable') }}</div>
                            <div class="rounded-2xl bg-white px-4 py-3">{{ $t('scanner.enterCodeFallback') }}</div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</template>
