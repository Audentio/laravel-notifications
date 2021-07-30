<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

interface NotificationContentInterface
{
    public function getNotificationMessage(): ?string;
}