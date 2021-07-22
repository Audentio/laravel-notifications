<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UserNotificationPreferenceModelTrait
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function initializeUserNotificationPreferenceModelTrait(): void
    {
        $this->fillable = array_merge([
            'user_id',
            'notification_preference_id',
            'disabled_channels',
        ], $this->fillable);

        $this->casts = array_merge([
            'disabled_channels' => 'json',
        ], $this->casts);

        $this->attributes = array_merge([
            'disabled_channels' => json_encode([]),
        ], $this->attributes);
    }
}