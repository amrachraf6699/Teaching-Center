<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Bell,
    CalendarDays,
    Camera,
    Clock3,
    FileText,
    GraduationCap,
    LayoutDashboard,
    LogOut,
    Settings,
    UserRound,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
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

onMounted(() => document.addEventListener('click', closeBell));
onUnmounted(() => document.removeEventListener('click', closeBell));

const adminNav = computed(() => [
    { label: 'Dashboard', href: routes.value.adminDashboard, icon: LayoutDashboard },
    { label: 'Students', href: routes.value.adminStudents, icon: GraduationCap },
    { label: 'Parents', href: routes.value.adminParents, icon: Users },
    { label: 'Groups', href: routes.value.adminGroups, icon: BookOpen },
    { label: 'Timetables', href: routes.value.adminTimetables, icon: Clock3 },
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
    { label: 'My Week', href: routes.value.studentDashboard, icon: CalendarDays },
    { label: 'Scan Attendance', href: routes.value.studentScanAttendance, icon: Camera },
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
            <Link :href="user?.role === 'student' ? routes.studentDashboard : routes.adminDashboard" class="flex items-center gap-3">
                <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="h-10 w-auto rounded-xl" />
                <span v-else class="grid h-11 w-11 place-items-center rounded-2xl bg-teachify-blue text-lg font-bold text-white">T</span>
                <span>
                    <span class="block text-lg font-bold">{{ brand.name }}</span>
                    <span class="block text-xs font-medium text-teachify-muted">{{ user?.role === 'student' ? 'Student workspace' : brand.tagline || 'Teacher workspace' }}</span>
                </span>
            </Link>

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
                    <Link :href="user?.role === 'parent' ? routes.parentDashboard : user?.role === 'student' ? routes.studentDashboard : routes.adminDashboard" class="flex items-center gap-3 lg:hidden">
                        <img v-if="brand.logoUrl" :src="brand.logoUrl" :alt="brand.name" class="h-9 w-auto rounded-xl" />
                        <span v-else class="grid h-10 w-10 place-items-center rounded-2xl bg-teachify-blue text-base font-bold text-white">T</span>
                        <span class="font-bold">{{ brand.name }}</span>
                    </Link>

                    <div class="hidden lg:block">
                        <h1 class="text-xl font-bold tracking-normal">{{ props.title }}</h1>
                    </div>

                    <div class="ml-auto flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-bold">{{ user?.name }}</div>
                            <div class="text-xs font-medium capitalize text-teachify-muted">{{ user?.role }}</div>
                        </div>

                        <!-- Bell icon (parents only) -->
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

                            <!-- Dropdown -->
                            <div
                                v-if="bellOpen"
                                class="absolute right-0 top-full z-50 mt-2 w-80 rounded-[1.4rem] border border-teachify-line bg-white shadow-[0_12px_40px_rgba(37,99,235,0.14)]"
                                data-bell
                            >
                                <div class="flex items-center justify-between border-b border-teachify-line px-4 py-3">
                                    <span class="font-black">Notifications</span>
                                    <Link
                                        :href="routes.parentNotifications"
                                        class="text-xs font-bold text-teachify-blue hover:underline"
                                        @click="bellOpen = false"
                                    >
                                        View all
                                    </Link>
                                </div>

                                <div v-if="recentNotifications.length" class="max-h-80 overflow-y-auto p-3 space-y-2">
                                    <div
                                        v-for="n in recentNotifications"
                                        :key="n.id"
                                        class="rounded-2xl p-3 transition"
                                        :class="n.read_at ? 'bg-gray-50' : 'bg-teachify-blue-soft'"
                                    >
                                        <div class="text-[10px] font-black uppercase text-teachify-blue">{{ n.type }}</div>
                                        <div class="mt-0.5 text-sm font-black">{{ n.title }}</div>
                                        <p class="mt-0.5 text-xs font-medium text-teachify-muted line-clamp-2">{{ n.body }}</p>
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
    </div>
</template>
