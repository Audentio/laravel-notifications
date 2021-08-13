<?php

namespace Audentio\LaravelNotifications\Notifications;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelNotifications\NotificationChannels\MailChannel;
use Audentio\LaravelNotifications\PushNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use function Deployer\Support\array_merge_alternate;

abstract class AbstractNotification extends Notification
{
    protected bool $bypassChecks = false;

    public function getContentTypeId(bool $withKeys = false): array
    {
        /** @var AbstractModel $content */
        $content = $this->getContent();

        $return = [
            'content_type' => $content ? $content->getContentType() : null,
            'content_id' => $content ? $content->getKey() : null,
        ];

        if (!$withKeys) {
            return array_values($return);
        }
        return $return;
    }

    public function getChannelsToBypass(User $user):  array
    {
        $bypass = $this->getNotificationChannelBlacklist();
        if ($this->bypassChecks) {
            return $bypass;
        }

        /** @var UserNotificationPreference $userNotificationPreference */
        $userNotificationPreference = $this->getUserNotificationPreference($user);
        if ($userNotificationPreference) {
            $bypass = array_merge($bypass, $userNotificationPreference->disabled_channels);
        }

        return $bypass;
    }

    public function getSender(): ?User
    {
        return null;
    }

    public function isImportant(): bool
    {
        return false;
    }

    public function isSystem(): bool
    {
        return false;
    }

    public function via($notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return [MailChannel::class];
        }

        $bypass = $this->getChannelsToBypass($notifiable);

        return $notifiable->getAvailableNotificationChannels($bypass);
    }

    public function toMail($notifiable): ?MailMessage
    {
        return null;
    }

    public function toPushNotification(User $user): ?PushNotification
    {
        if (!config('audentioNotifications.push_enabled')) {
            return null;
        }

        return new PushNotification($user, $this);
    }

    public function toDatabase(User $user): array
    {
        return [];
    }

    protected function getUserNotificationPreference(User $user): ?UserNotificationPreference
    {
        $userNotificationPreferences = $user->userNotificationPreferences;

        return $userNotificationPreferences->where('notification_preference_id', $this->getNotificationPreferenceId())->first();
    }

    protected function getNotificationChannelBlacklist(): array
    {
        return [];
    }

    abstract public function getNotificationPreferenceId(): ?string;
    abstract public function getKind(): string;
    abstract public function getNotificationMessage($notifiable): ?string;
    abstract public function getContent(): ?AbstractModel;
}