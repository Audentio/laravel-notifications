<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\UserNotificationPreference;
use Audentio\LaravelNotifications\NotificationChannels\DatabaseChannel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notifiable;

trait NotifiableUserTrait
{
    use Notifiable;

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function readNotifications(): HasMany
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function userNotificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function getAvailableNotificationChannels(array $bypassChannels): array
    {
        $notificationChannels = [
            'notification' => DatabaseChannel::class,
        ];

        $attributes = $this->attributesToArray();

        if (!empty($attributes['email'])) {
            $notificationChannels['email'] = MailChannel::class;
        }

        foreach ($bypassChannels as $bypassChannel) {
            if (array_key_exists($bypassChannel, $notificationChannels)) {
                unset($notificationChannels[$bypassChannel]);
            }
        }

        return array_values($notificationChannels);
    }

    public function getUserNotificationPreferenceValues(): array
    {
        $notificationPreferences = NotificationPreference::getCached();
        $userNotificationPreferences = $this->userNotificationPreferences;

        $return = [];
        foreach ($notificationPreferences as $notificationPreference) {
            $disabledChannels = [];

            /** @var UserNotificationPreference|null $userNotificationPreference */
            $userNotificationPreference = $userNotificationPreferences->where('notification_preference_id', $notificationPreference->id)->first();
            if ($userNotificationPreference) {
                $disabledChannels = $userNotificationPreference->disabled_channels ?? [];
            }
            $return[] = [
                'notification_preference_id' => $notificationPreference->id,
                'disabled_channels' => $disabledChannels,
            ];
        }

        return $return;
    }
}