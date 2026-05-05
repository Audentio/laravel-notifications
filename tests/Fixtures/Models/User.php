<?php

namespace App\Models;

use Audentio\LaravelNotifications\Models\Interfaces\NotifiableUserInterface;
use Audentio\LaravelNotifications\Models\Traits\NotifiableUserTrait;
use Audentio\LaravelNotifications\Notifications\AbstractNotification;

class User extends AbstractModel implements NotifiableUserInterface
{
    use NotifiableUserTrait;

    protected $fillable = ['email'];

    public function isEmailVerified(): bool
    {
        return !empty($this->email);
    }
}
