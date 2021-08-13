<?php

namespace Audentio\LaravelNotifications\Notifications;

use Audentio\LaravelNotifications\Notifications\Interfaces\MassNotificationInterface;
use Audentio\LaravelNotifications\Notifications\Traits\MassNotificationTrait;

abstract class AbstractMassNotification extends AbstractNotification implements MassNotificationInterface
{
    use MassNotificationTrait;

}