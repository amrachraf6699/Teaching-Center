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
        $locale = app()->getLocale();
        $locale = in_array($locale, ['en', 'ar'], true) ? $locale : 'en';
        $brandName = rescue(fn () => $settings?->name, config('app.name', 'Teachify'), false) ?: config('app.name', 'Teachify');
        $brandTagline = rescue(fn () => $settings?->tagline, null, false);
        $brandLogoUrl = rescue(fn () => $media?->getFirstMediaUrl('logo') ?: null, null, false);
        $brandFaviconUrl = rescue(fn () => $media?->getFirstMediaUrl('favicon') ?: null, null, false);

        return [
            ...parent::share($request),
            'locale' => $locale,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
            'availableLocales' => [
                ['code' => 'en', 'name' => 'English', 'direction' => 'ltr'],
                ['code' => 'ar', 'name' => 'العربية', 'direction' => 'rtl'],
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
            'brand' => [
                'name' => $brandName,
                'tagline' => $brandTagline,
                'logoUrl' => $brandLogoUrl,
                'faviconUrl' => $brandFaviconUrl,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'routes' => [
                'login' => route('login'),
                'logout' => route('logout'),
                'localeSwitch' => route('locale.switch'),
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
                'studentScanAttendance' => route('student.scan-attendance'),
                'studentAttendanceLookupByCode' => route('student.attendance.lookup-by-code'),
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
