<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\NotificationPreferenceGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

trait NotificationPreferenceModelTrait
{
    protected static Collection $cachedValues;

    public function notificationPreferenceGroup(): BelongsTo
    {
        return $this->belongsTo(NotificationPreferenceGroup::class);
    }

    public function getDefaultUserNotificationPreferenceValue(): array
    {
        return [
            'notification_preference_id' => $this->id,
            'disabled_channels' => [],
        ];
    }

    public static function getCached(): Collection
    {
        if (!isset(self::$cachedValues)) {
            self::$cachedValues = self::get();
        }

        return self::$cachedValues;
    }
}