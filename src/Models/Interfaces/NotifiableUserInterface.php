<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface NotifiableUserInterface
{
    public function notifications(): HasMany;
    public function readNotifications(): HasMany;
    public function unreadNotifications(): HasMany;
    public function userNotificationPreferences(): HasMany;
    public function getAvailableNotificationChannels(array $bypassChannels): array;
    public function getUserNotificationPreferenceValues(): array;
    public function routeNotificationForPush(AbstractNotification $notification): ?array;
}