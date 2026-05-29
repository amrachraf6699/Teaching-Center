<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Middleware;
use Modules\Core\Models\SettingMedia;
use Modules\Core\Settings\GeneralSettings;
use Modules\Notifications\Support\PortalNotificationData;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = rescue(fn () => app(GeneralSettings::class), null, false);
        $media = rescue(fn () => SettingMedia::brand(), null, false);
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
            'brand' => [
                'name' => $settings?->name ?? config('app.name', 'Teachify'),
                'tagline' => $settings?->tagline,
                'logoUrl' => $media?->getFirstMediaUrl('logo') ?: null,
                'faviconUrl' => $media?->getFirstMediaUrl('favicon') ?: null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'routes' => [
                'login' => route('login'),
                'logout' => route('logout'),
                'adminDashboard' => route('admin.dashboard'),
                'parentDashboard' => route('parent.dashboard'),
                'parentAttendance' => route('parent.attendance'),
                'parentExams' => route('parent.exams'),
                'parentNotifications' => route('parent.notifications'),
                'parentNotificationsMarkRead' => route('parent.notifications.mark-read'),
                'studentHome' => route('student.home'),
                'studentDashboard' => route('student.dashboard'),
                'studentSessions' => route('student.sessions'),
                'studentExams' => route('student.exams.index'),
                'studentPassword' => route('student.password.edit'),
                'studentAttendanceLookupByCode' => route('student.attendance.lookup-by-code'),
                'adminParents' => route('admin.parents.index'),
                'adminStudents' => route('admin.students.index'),
                'adminGroups' => route('admin.groups.index'),
                'adminTimetables' => route('admin.timetables.index'),
                'adminSessions' => route('admin.sessions.index'),
                'adminExams' => route('admin.exams.index'),
                'adminNotifications' => route('admin.notifications.index'),
                'adminImports' => route('admin.imports.index'),
                'adminExports' => route('admin.exports.index'),
                'adminSettings' => route('admin.settings.edit'),
            ],
            'parentUnreadCount' => fn () => $user?->role === 'parent'
                ? $user->parentNotifications()
                    ->whereNull('read_at')
                    ->count()
                : null,
            'parentRecentNotifications' => fn () => $user?->role === 'parent'
                ? $user->parentNotifications()
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function (DatabaseNotification $notification): array {
                        $data = PortalNotificationData::from($notification);

                        return [
                            'id' => $notification->id,
                            'type' => $data['type'],
                            'title' => $data['title'],
                            'body' => $data['body'],
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at?->diffForHumans(),
                        ];
                    })
                : null,
        ];
    }
}
