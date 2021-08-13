<?php

namespace Audentio\LaravelNotifications\Notifications\Interfaces;

use Illuminate\Support\Collection;

interface MassNotificationInterface
{
    public function getUsers(): Collection;
}