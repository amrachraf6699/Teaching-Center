<?php

namespace Modules\Notifications\Contracts;

use Modules\Notifications\Models\ParentNotification;

interface ParentNotificationChannel
{
    public function send(ParentNotification $notification): void;
}
