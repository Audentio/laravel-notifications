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

    public function shouldDisplay(): bool
    {
        if ($this->should_display_callback && is_callable($this->should_display_callback)) {
            return call_user_func($this->should_display_callback);
        }
        return true;
    }

    public static function getCached(): Collection
    {
        if (!isset(self::$cachedValues)) {
            self::$cachedValues = self::get();
        }

        return self::$cachedValues;
    }

    protected function initializeNotificationPreferenceModelTrait(): void
    {
        $this->fillable = array_merge([
            'available_channels', 'required_channels'
        ], $this->fillable);

        $this->casts = array_merge([
            'available_channels' => 'json',
            'required_channels' => 'json'
        ], $this->casts);

    }
}