<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface UserNotificationPreferenceModelInterface
{
    public function user(): BelongsTo;
}