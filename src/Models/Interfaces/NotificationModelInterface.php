<?php

namespace Audentio\LaravelNotifications\Models\Interfaces;

interface NotificationModelInterface
{
    public function getMessage(): ?string;
    public function isRead(): bool;
    public function markRead(): void;
}