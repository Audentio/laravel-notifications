<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use App\Models\NotificationReminder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface NotificationReminderContentInterface
{
    public function notificationReminders(): MorphMany;
    public function getNotificationReminder(string $handlerClass): ?NotificationReminder;
}