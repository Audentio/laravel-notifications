<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface NotificationModelInterface
{
    public function user(): BelongsTo;
    public function sender(): BelongsTo;
    public function getNotificationHandler(): ?AbstractNotification;
    public function getMessage(): ?string;
    public function isRead(): bool;
    public function markRead(): void;
}