<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface NotificationModelInterface
{
    public function user(): BelongsTo;
    public function sender(): BelongsTo;
    public function getMessage(): ?string;
    public function isRead(): bool;
    public function markRead(): void;
}