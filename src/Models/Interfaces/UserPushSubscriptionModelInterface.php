<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelNotifications\PushHandlers\AbstractPushHandler;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface UserPushSubscriptionModelInterface
{
    public function user(): BelongsTo;

    public function getHandler(): AbstractPushHandler;

    public function logSuccess(): void;
    public function logCancel(): void;
}