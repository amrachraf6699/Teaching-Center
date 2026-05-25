<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Core\Models\SettingMedia;
use Modules\Core\Settings\GeneralSettings;
use Modules\Notifications\Models\ParentNotification;

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
                'studentDashboard' => route('student.dashboard'),
                'studentScanAttendance' => route('student.scan-attendance'),
                'adminParents' => route('admin.parents.index'),
                'adminStudents' => route('admin.students.index'),
                'adminGroups' => route('admin.groups.index'),
                'adminTimetables' => route('admin.timetables.index'),
                'adminSessions' => route('admin.sessions.index'),
                'adminExams' => route('admin.exams.index'),
                'adminNotifications' => route('admin.notifications.index'),
                'adminSettings' => route('admin.settings.edit'),
            ],
            'parentUnreadCount' => fn () => $user?->role === 'parent'
                ? ParentNotification::where('parent_id', $user->id)
                    ->where('recipient_role', 'parent')
                    ->whereNull('read_at')
                    ->count()
                : null,
            'parentRecentNotifications' => fn () => $user?->role === 'parent'
                ? ParentNotification::where('parent_id', $user->id)
                    ->where('recipient_role', 'parent')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (ParentNotification $n): array => [
                        'id' => $n->id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'body' => $n->body,
                        'read_at' => $n->read_at,
                        'created_at' => $n->created_at?->diffForHumans(),
                    ])
                : null,
        ];
    }
}
