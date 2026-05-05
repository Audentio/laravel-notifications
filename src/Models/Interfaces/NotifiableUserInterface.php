<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

interface NotifiableUserInterface
{
    public function notifications(): HasMany;
    public function readNotifications(): HasMany;
    public function unreadNotifications(): HasMany;
    public function userNotificationPreferences(): HasMany;
    public function getAvailableNotificationChannels(array $bypassChannels): array;
    public function getNotificationPreferenceTenantDefaults(): Collection;
    public function getUserNotificationPreferenceValues(): array;
    public function isEmailVerified(): bool;
    public function routeNotificationForPush(AbstractNotification $notification): ?array;
}