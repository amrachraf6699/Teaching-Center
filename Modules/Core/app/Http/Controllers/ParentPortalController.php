<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Notifications\Models\ParentNotification;

class ParentPortalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $children = $request->user()
            ->children()
            ->with(['groups.sessions.attendanceRecords', 'groups'])
            ->get();

        $notifications = ParentNotification::query()
            ->where('parent_id', $request->user()->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('core::parent.dashboard', [
            'children' => $children,
            'notifications' => $notifications,
        ]);
    }
}
