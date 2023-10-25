<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface NotificationModelInterface
{
    public function user(): BelongsTo;
    public function sender(): BelongsTo;

    public function scopeDismissed(Builder $query): void;
    public function scopeNotDismissed(Builder $query): void;
    public function scopeRead(Builder $query): void;
    public function scopeNotRead(Builder $query): void;

    public function getNotificationHandler(): ?AbstractNotification;
    public function getMessage(): ?string;
    public function getUrl(): ?string;

    public function isRead(): bool;
    public function isDismissed(): bool;

    public function markRead(): void;
    public function dismiss(bool $save = true): void;
}
