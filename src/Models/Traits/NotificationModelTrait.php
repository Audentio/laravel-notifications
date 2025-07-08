<?php

namespace Audentio\LaravelNotifications\Models\Traits;

use App\Models\User;
use Audentio\LaravelBase\Foundation\Traits\ContentTypeTrait;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait NotificationModelTrait
{
    use ContentTypeTrait;

    protected AbstractNotification $notificationHandler;

    public function content(): MorphTo {
        return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function scopeDismissed(Builder $query): void
    {
        $query->whereNotNull('dismissed_at');
    }

    public function scopeNotDismissed(Builder $query): void
    {
        $query->whereNull('dismissed_at');
    }

    public function scopeRead(Builder $query): void
    {
        $query->whereNotNull('read_at');
    }

    public function scopeNotRead(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function getNotificationHandler(): ?AbstractNotification
    {
        $handlerClass = $this->type;
        if (!class_exists($handlerClass)) {
            return null;
        }

        $content = $this->content ?? null;

        if (method_exists($handlerClass, 'createInstanceForNotification')) {
            return $handlerClass::createInstanceForNotification($this);
        }

        if ($content) {
            return new $handlerClass($content);
        }

        try {
            return new $handlerClass;
        } catch (\ArgumentCountError $e) {
            return null;
        }
    }

    public function getMessage(): ?string
    {
        $handler = $this->getNotificationHandler();
        if (!$handler) {
            return null;
        }

        return $handler->getNotificationMessage($this, $this->user);
    }

    public function getUrl(): ?string
    {
        $handler = $this->getNotificationHandler();
        if (!$handler) {
            return null;
        }

        return $handler->getUrl($this, $this->user);
    }

    public function isRead(): bool
    {
        return !!$this->read_at;
    }

    public function isDismissed(): bool
    {
        return !!$this->dismissed_at;
    }

    public function markRead(bool $save = true): void
    {
        $this->read_at = now();

        if ($save) {
            $this->save();
        }
    }

    public function dismiss(bool $save = true): void
    {
        if (!$this->isRead()) {
            $this->markRead(false);
        }

        $this->dismissed_at = now();

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
