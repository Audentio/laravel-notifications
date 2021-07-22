<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface NotificationPreferenceGroupModelInterface
{
    public function notificationPreferences(): HasMany;
    public function getName(): string;
}