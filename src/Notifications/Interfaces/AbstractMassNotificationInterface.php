<?php

namespace Audentio\LaravelNotifications\Notifications\Interfaces;

use Illuminate\Support\Collection;

interface AbstractMassNotificationInterface
{
    public function getUsers(): Collection;
}