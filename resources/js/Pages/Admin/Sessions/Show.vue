<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Button from '../../../Components/Button.vue';
import EmptyState from '../../../Components/EmptyState.vue';
import QrCodePanel from '../../../Components/QrCodePanel.vue';
import ToggleSwitch from '../../../Components/ToggleSwitch.vue';
import AppShell from '../../../Layouts/AppShell.vue';

const props = defineProps({ session: Object });
const attendanceForms = reactive(
    Object.fromEntries(
        props.session.students.map((student) => [
            student.id,
            {
                status: student.attendance || 'present',
                notes: student.attendance_notes || '',
            },
        ]),
    ),
);

function saveAttendance(studentId) {
    router.post(props.session.attendance_action, {
        teaching_session_id: props.session.id,
        student_id: studentId,
        status: attendanceForms[studentId].status,
        notes: attendanceForms[studentId].notes,
    }, {
        preserveScroll: true,
    });
}

function regenerateAttendanceCode() {
    router.post(props.session.regenerate_attendance_code_url, {}, { preserveScroll: true });
}

function toggleSelfCheckIn(nextValue) {
    router.patch(props.session.update_attendance_entry_url, {
        attendance_entry_enabled: nextValue,
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="session.title" />
    <AppShell :title="session.title">
        <div class="mb-4 flex flex-wrap gap-3">
            <Button :href="session.edit_url">Edit Session</Button>
            <Button variant="secondary" :href="session.index_url">Back to Sessions</Button>
        </div>

        <section class="teachify-card rounded-[1.6rem] p-5">
            <div class="grid gap-4 sm:grid-cols-4">
                <div><div class="text-xs font-black uppercase text-teachify-muted">Group</div><Link v-if="session.group" :href="session.group.show_url" class="mt-1 block font-bold text-teachify-blue">{{ session.group.name }}</Link></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Starts</div><div class="mt-1 font-bold">{{ session.starts_at }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Ends</div><div class="mt-1 font-bold">{{ session.ends_at || '-' }}</div></div>
                <div><div class="text-xs font-black uppercase text-teachify-muted">Source</div><div class="mt-1 font-bold">{{ session.source_label }}</div></div>
            </div>
            <p v-if="session.notes" class="mt-4 text-sm font-medium text-teachify-muted">{{ session.notes }}</p>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-black">Session QR code</h2>
                    <p class="mt-1 text-sm font-medium text-teachify-muted">Students scan this code or use the session code below, sign in with their student code and password, then mark themselves present.</p>
                </div>
                <QrCodePanel :value="session.scan_url" />
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                <div class="rounded-[1.4rem] border border-teachify-line bg-white p-4">
                    <div class="text-xs font-black uppercase text-teachify-muted">Manual session code</div>
                    <div class="mt-2 text-2xl font-black tracking-[0.2em] text-teachify-ink">{{ session.manual_attendance_code }}</div>
                    <div class="mt-2 text-sm font-medium text-teachify-muted">Students can enter this code from the global student scan modal.</div>
                </div>
                <div class="flex flex-col gap-3">
                    <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-teachify-line bg-white px-4 py-3">
                        <span>
                            <span class="block text-sm font-bold text-teachify-ink">Self check-in enabled</span>
                            <span class="block text-xs font-medium text-teachify-muted">Controls both QR and code entry.</span>
                        </span>
                        <ToggleSwitch :model-value="session.attendance_entry_enabled" @change="toggleSelfCheckIn" />
                    </label>
                    <Button type="button" variant="secondary" @click="regenerateAttendanceCode">Regenerate Code</Button>
                </div>
            </div>
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Students and attendance review</h2>
            <div v-if="session.students.length" class="mt-4 grid gap-4">
                <article v-for="student in session.students" :key="student.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <Link :href="student.show_url" class="font-black text-teachify-blue">{{ student.name }}</Link>
                            <div class="text-sm font-semibold text-teachify-muted">{{ student.code || '-' }} · {{ student.parent || 'No parent' }}</div>
                        </div>
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black capitalize text-teachify-ink">
                            {{ student.attendance || 'pending' }}
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-[200px_minmax(0,1fr)_auto] md:items-end">
                        <label class="block">
                            <span class="text-sm font-bold text-teachify-ink">Status</span>
                            <select v-model="attendanceForms[student.id].status" class="mt-2 min-h-12 w-full rounded-2xl border border-teachify-line bg-white px-4 text-sm font-medium outline-none transition focus:border-teachify-blue focus:ring-4 focus:ring-teachify-blue-soft">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-teachify-ink">Notes</span>
                            <textarea v-model="attendanceForms[student.id].notes" rows="2" class="mt-2 w-full rounded-2xl border border-teachify-line bg-white px-4 py-3 text-sm font-medium outline-none transition focus:border-teachify-blue focus:ring-4 focus:ring-teachify-blue-soft"></textarea>
                        </label>
                        <Button type="button" @click="saveAttendance(student.id)">Save</Button>
                    </div>
                </article>
            </div>
            <EmptyState v-else title="No students" message="Students assigned to the group will appear here." />
        </section>

        <section class="mt-6 teachify-card rounded-[1.6rem] p-5">
            <h2 class="text-lg font-black">Attendance Records</h2>
            <div v-if="session.attendance.length" class="mt-4 grid gap-3">
                <article v-for="attendance in session.attendance" :key="attendance.id" class="rounded-2xl border border-teachify-line bg-white p-4">
                    <div class="font-black">{{ attendance.student }}</div>
                    <div class="text-sm font-semibold text-teachify-muted">{{ attendance.parent || 'No parent' }} · <span class="capitalize">{{ attendance.status }}</span></div>
                    <p v-if="attendance.notes" class="mt-2 text-sm font-medium text-teachify-muted">{{ attendance.notes }}</p>
                </article>
            </div>
            <EmptyState v-else title="No attendance yet" message="Attendance records will appear here after saving them." />
        </section>
    </AppShell>
</template>
