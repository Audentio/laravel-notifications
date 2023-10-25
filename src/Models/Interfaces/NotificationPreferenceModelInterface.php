<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

interface NotificationPreferenceModelInterface
{
    public function notificationPreferenceGroup(): BelongsTo;
    public function getDefaultUserNotificationPreferenceValue(): array;
    public function getName(): string;
    public static function getCached(): Collection;
    public function shouldDisplay(): bool;
}