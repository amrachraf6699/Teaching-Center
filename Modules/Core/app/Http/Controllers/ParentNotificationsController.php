<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Notifications\Models\ParentNotification;

class ParentNotificationsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $notifications = ParentNotification::query()
            ->where('parent_id', $request->user()->id)
            ->where('recipient_role', 'parent')
            ->latest()
            ->paginate(20)
            ->through(fn (ParentNotification $n): array => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at?->diffForHumans(),
            ]);

        ParentNotification::query()
            ->where('parent_id', $request->user()->id)
            ->where('recipient_role', 'parent')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Parent/Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        ParentNotification::query()
            ->where('parent_id', $request->user()->id)
            ->where('recipient_role', 'parent')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
