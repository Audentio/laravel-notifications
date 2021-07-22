<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface NotificationPreferenceModelInterface
{
    public function notificationPreferenceGroup(): BelongsTo;
    public function getName(): string;
}