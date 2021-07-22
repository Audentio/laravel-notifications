<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use App\Models\Notification;

interface NotificationContentInterface
{
    public function getNotificationMessage(Notification $notification): ?string;
}