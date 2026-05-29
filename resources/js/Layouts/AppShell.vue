<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
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
import { computed, onBeforeUnmount, onMounted, onUnmounted, ref } from 'vue';
import FlashMessage from '../Components/FlashMessage.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const brand = computed(() => page.props.brand ?? { name: 'Teachify' });
const routes = computed(() => page.props.routes ?? {});
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
const detector = ref(null);
const codeForm = useForm({
    code: '',
});

let mediaStream = null;
let animationFrameId = null;

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

function openAttendanceModal() {
    attendanceModalOpen.value = true;
    codeForm.reset();
    codeForm.clearErrors();
    scannerError.value = '';
    if (user.value?.role === 'student') {
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

    if (!('mediaDevices' in navigator) || !('getUserMedia' in navigator.mediaDevices)) {
        scannerError.value = 'Camera access is not available in this browser.';
        return;
    }

    if (!('BarcodeDetector' in window)) {
        scannerError.value = 'This browser does not support in-app QR scanning. Use the session code instead.';
        return;
    }

    try {
        detector.value = new window.BarcodeDetector({ formats: ['qr_code'] });
        scannerSupported.value = true;
        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });

        if (!video.value) {
            return;
        }

        video.value.srcObject = mediaStream;
        await video.value.play();
        scannerReady.value = true;
        scanning.value = true;
        scanFrame();
    } catch {
        scannerError.value = 'Unable to open the camera. Allow access or use the session code instead.';
    }
}

async function scanFrame() {
    if (!video.value || !detector.value || !scanning.value) {
        return;
    }

    try {
        const barcodes = await detector.value.detect(video.value);
        const qrCode = barcodes.find((barcode) => barcode.rawValue);

        if (qrCode?.rawValue) {
            stopScanner();
            window.location.href = qrCode.rawValue;
            return;
        }
    } catch {
        scannerError.value = 'Scanning failed on this device. Use the session code instead.';
        stopScanner();
        return;
    }

    animationFrameId = window.requestAnimationFrame(scanFrame);
}

function stopScanner() {
    scanning.value = false;
    scannerReady.value = false;

    if (animationFrameId) {
        window.cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
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
    { label: 'Dashboard', href: routes.value.adminDashboard, icon: LayoutDashboard },
    { label: 'Students', href: routes.value.adminStudents, icon: GraduationCap },
    { label: 'Parents', href: routes.value.adminParents, icon: Users },
    { label: 'Groups', href: routes.value.adminGroups, icon: BookOpen },
    { label: 'Timetables', href: routes.value.adminTimetables, icon: CalendarDays },
    { label: 'Sessions', href: routes.value.adminSessions, icon: CalendarDays },
    { label: 'Exams', href: routes.value.adminExams, icon: FileText },
    { label: 'Notifications', href: routes.value.adminNotifications, icon: Bell },
    { label: 'Settings', href: routes.value.adminSettings, icon: Settings },
]);

const parentNav = computed(() => [
    { label: 'Portal', href: routes.value.parentDashboard, icon: UserRound },
    { label: 'Attendance', href: routes.value.parentAttendance, icon: CalendarDays },
    { label: 'Exams', href: routes.value.parentExams, icon: FileText },
]);

const studentNav = computed(() => [
    { label: 'Home', href: routes.value.studentHome, icon: House },
    { label: 'Sessions', href: routes.value.studentSessions, icon: CalendarDays },
    { label: 'Exams', href: routes.value.studentExams, icon: FileText },
    { label: 'Password', href: routes.value.studentPassword, icon: KeyRound },
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
                    <span class="block text-xs font-medium text-teachify-muted">{{ user?.role === 'student' ? 'Student workspace' : brand.tagline || 'Teacher workspace' }}</span>
                </span>
            </Link>

            <button
                v-if="user?.role === 'student'"
                type="button"
                class="mt-6 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-teachify-blue px-4 py-2.5 text-sm font-bold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700"
                @click="openAttendanceModal"
            >
                <ScanLine class="h-4 w-4" />
                Scan or Enter Session Code
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
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                    <Link :href="user?.role === 'parent' ? routes.parentDashboard : user?.role === 'student' ? routes.studentHome : routes.adminDashboard" class="flex items-center gap-3 lg:hidden">
                        <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="h-9 w-auto rounded-xl" />
                        <span v-else class="grid h-10 w-10 place-items-center rounded-2xl bg-teachify-blue text-base font-bold text-white">T</span>
                        <span class="font-bold">{{ brand.name }}</span>
                    </Link>

                    <div class="hidden lg:block">
                        <h1 class="text-xl font-bold tracking-normal">{{ props.title }}</h1>
                    </div>

                    <div class="ml-auto flex items-center gap-3">
                        <button
                            v-if="user?.role === 'student'"
                            type="button"
                            class="hidden min-h-11 items-center gap-2 rounded-2xl border border-teachify-line bg-white px-4 py-2.5 text-sm font-bold text-teachify-ink shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue sm:inline-flex"
                            @click="openAttendanceModal"
                        >
                            <ScanLine class="h-4 w-4" />
                            Scan / Code
                        </button>

                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-bold">{{ user?.name }}</div>
                            <div class="text-xs font-medium capitalize text-teachify-muted">{{ user?.role }}</div>
                        </div>

                        <div v-if="user?.role === 'parent'" class="relative" data-bell>
                            <button
                                type="button"
                                class="relative grid h-11 w-11 place-items-center rounded-2xl border border-teachify-line bg-white text-teachify-muted shadow-sm transition hover:border-teachify-blue hover:text-teachify-blue"
                                aria-label="Notifications"
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
                                    <span class="font-black">Notifications</span>
                                    <Link :href="routes.parentNotifications" class="text-xs font-bold text-teachify-blue hover:underline" @click="bellOpen = false">View all</Link>
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
                                <p v-else class="px-4 py-5 text-sm font-medium text-teachify-muted">No notifications yet.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="grid h-11 w-11 place-items-center rounded-2xl border border-teachify-line bg-white text-teachify-muted shadow-sm transition hover:border-teachify-coral hover:text-teachify-coral"
                            aria-label="Logout"
                            @click="logout"
                        >
                            <LogOut class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
                <div class="mb-5 lg:hidden">
                    <h1 class="text-2xl font-bold tracking-normal">{{ props.title }}</h1>
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
                    <span>Scan</span>
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

        <div v-if="attendanceModalOpen && user?.role === 'student'" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6">
            <div class="w-full max-w-4xl rounded-[1.8rem] border border-teachify-line bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="flex items-start justify-between gap-4 border-b border-teachify-line px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-xl font-black text-teachify-ink">Student self check-in</h2>
                        <p class="mt-1 text-sm font-medium text-teachify-muted">Scan the teacher QR code or enter the session code from the session page.</p>
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
                            <span class="font-black text-teachify-ink">Status:</span>
                            <span v-if="scannerReady && scanning" class="text-teachify-blue"> Camera is live and scanning.</span>
                            <span v-else-if="scannerSupported && !scanning" class="text-teachify-muted"> Scanner stopped.</span>
                            <span v-else> Preparing camera...</span>
                        </div>

                        <div v-if="scannerError" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                            {{ scannerError }}
                        </div>

                        <form class="space-y-3 rounded-[1.4rem] border border-teachify-line bg-white p-4" @submit.prevent="submitAttendanceCode">
                            <label class="block">
                                <span class="text-sm font-bold text-teachify-ink">Session code</span>
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
                                Open Session
                            </button>
                        </form>

                        <div class="space-y-3 text-sm font-medium text-teachify-muted">
                            <div class="rounded-2xl bg-white px-4 py-3">The teacher can disable self check-in per session. If that happens, use teacher attendance review instead.</div>
                            <div class="rounded-2xl bg-white px-4 py-3">If scanning fails here, enter the short session code shown on the teacher session page.</div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</template>
