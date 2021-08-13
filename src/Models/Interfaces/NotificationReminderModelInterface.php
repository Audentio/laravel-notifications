<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelBase\Foundation\Interfaces\ContentTypeInterface;
use Audentio\LaravelNotifications\NotificationReminders\AbstractReminder;

interface NotificationReminderModelInterface extends ContentTypeInterface
{
    public function getHandler(): AbstractReminder;
}