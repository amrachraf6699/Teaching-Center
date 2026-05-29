<?php

namespace Modules\Notifications\Support;

use Illuminate\Notifications\DatabaseNotification;
use Modules\Notifications\Notifications\PortalNotification;

class PortalNotificationData
{
    public static function type(): string
    {
        return PortalNotification::class;
    }

    public static function isPortal(DatabaseNotification $notification): bool
    {
        return $notification->type === static::type();
    }

    /**
     * @return array<string, mixed>
     */
    public static function from(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'audience' => $data['audience'] ?? null,
            'body' => $data['body'] ?? '',
            'parent_id' => $data['parent_id'] ?? null,
            'reference_key' => $data['reference_key'] ?? null,
            'student_code' => $data['student_code'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'student_name' => $data['student_name'] ?? null,
            'title' => $data['title'] ?? '',
            'type' => $data['type'] ?? 'general',
        ];
    }
}
