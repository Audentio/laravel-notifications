<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait NotificationPreferenceGroupModelTrait
{
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }
}