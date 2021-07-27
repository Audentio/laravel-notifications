<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\User;
use Audentio\LaravelBase\Foundation\Traits\ContentTypeTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait NotificationModelTrait
{
    use ContentTypeTrait;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function getMessage(): ?string
    {
        if (!$this->content) {
            return null;
        }

        return $this->content->getNotificationMessage($this);
    }

    public function isRead(): bool
    {
        return !!$this->read_at;
    }

    public function markRead(bool $save = true): void
    {
        $this->read_at = now();

        if ($save) {
            $this->save();
        }
    }

    protected function initializeNotificationModelTrait(): void
    {
        $this->fillable = array_merge($this->fillable, [
            'id',
            'type',
            'kind',
            'user_id',
            'sender_user_id',
            'content_type',
            'content_id',
            'data',
            'is_important',
            'is_system',
        ]);

        $this->casts = array_merge($this->casts, [
            'data' => 'json',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ]);
    }
}