<?php

namespace Audentio\LaravelNotifications\Notifications\Sample;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelNotifications\Models\Interfaces\NotificationModelInterface;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;

class SampleNotification extends AbstractNotification
{
    public function getNotificationPreferenceId(): ?string
    {
        return null;
    }

    public function getContent(): ?AbstractModel
    {
        return null;
    }

    public function getKind(): string
    {
        return 'sample';
    }

    public function getNotificationMessage(?NotificationModelInterface $notification, $notifiable): ?string
    {
        return 'This is a sample notification.';
    }
}