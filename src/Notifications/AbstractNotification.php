<?php

namespace Audentio\LaravelNotifications\Notifications;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Audentio\LaravelBase\Foundation\AbstractModel;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class AbstractNotification extends Notification
{
    protected bool $bypassChecks = false;

    public function getContentTypeId(): array
    {
        /** @var AbstractModel $content */
        $content = $this->getContent();
        if (!$content) {
            return [
                'content_type' => null,
                'content_id' => null
            ];
        }

        return [
            'content_type' => $content->getContentType(),
            'content_id' => $content->getKey(),
        ];
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
    abstract public function getContent(): ?AbstractModel;
    abstract public function getKind(): string;
}