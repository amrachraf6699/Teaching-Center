<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Core\Models\SettingMedia;
use Modules\Core\Settings\GeneralSettings;

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
                'studentDashboard' => route('student.dashboard'),
                'studentScanAttendance' => route('student.scan-attendance'),
                'adminParents' => route('admin.parents.index'),
                'adminStudents' => route('admin.students.index'),
                'adminGroups' => route('admin.groups.index'),
                'adminTimetables' => route('admin.timetables.index'),
                'adminSessions' => route('admin.sessions.index'),
                'adminExams' => route('admin.exams.index'),
                'adminSettings' => route('admin.settings.edit'),
            ],
        ];
    }
}
