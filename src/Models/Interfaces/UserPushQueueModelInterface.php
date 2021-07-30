<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface UserPushQueueModelInterface
{
    public function user(): BelongsTo;

    public function userPushSubscription(): BelongsTo;

    public function logSuccess(): void;
    public function logDelay(): void;
    public function logCancel(): void;
    public function logCancelPushSubscription(): void;
}