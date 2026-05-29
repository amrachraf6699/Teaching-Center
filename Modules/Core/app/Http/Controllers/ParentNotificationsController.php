<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Notifications\Support\PortalNotificationData;

class ParentNotificationsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $notifications = $request->user()->parentNotifications()
            ->latest()
            ->paginate(20)
            ->through(function (DatabaseNotification $notification): array {
                $data = PortalNotificationData::from($notification);

                return [
                    'id' => $notification->id,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            });

        $request->user()->parentNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Parent/Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request): RedirectResponse
    {
        $request->user()->parentNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
