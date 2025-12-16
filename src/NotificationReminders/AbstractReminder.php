<?php

namespace Audentio\LaravelNotifications\NotificationReminders;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelNotifications\LaravelNotifications;
use Audentio\LaravelNotifications\Models\Interfaces\NotificationReminderModelInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

abstract class AbstractReminder
{
    protected CarbonImmutable $dueDate;
    protected array $intervals;

    protected array $reminderTimes;
    protected array $futureReminderTimes;

    /**
     * @return CarbonInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function getReminderTimes(bool $excludePastTimes = true): array
    {
        if (!isset($this->reminderTimes)) {
            $reminderTimes = [];
            $futureReminderTimes = [];

            foreach ($this->getIntervals() as $interval) {
                $time = $this->dueDate->sub($interval);

                $reminderTimes[] = $time;
                if ($time > now()) {
                    $futureReminderTimes[] = $time;
                }
            }

            $this->reminderTimes = $reminderTimes;
            $this->futureReminderTimes = $futureReminderTimes;
        }

        if (!$excludePastTimes) {
            return $this->reminderTimes;
        }

        return $this->futureReminderTimes;
    }

    public function getNextSendTime(?CarbonInterface $lastSendAt = null): ?CarbonImmutable
    {
        $upcomingReminderTimes = $this->getReminderTimes();
        if (empty($upcomingReminderTimes)) {
            return null;
        }

        // If no previous send, return the first upcoming time
        if ($lastSendAt === null) {
            return $upcomingReminderTimes[0];
        }

        foreach ($upcomingReminderTimes as $reminderTime) {
            if ($reminderTime > $lastSendAt) {
                return $reminderTime;
            }
        }

        return null;
    }

    protected function validateIntervals(): void
    {
        foreach ($this->intervals as $key => $interval) {
            if (!$interval instanceof CarbonInterval) {
                throw new \RuntimeException('Invalid interval specified in ' . get_class($this) . ' at position ' . number_format($key));
            }
        }

        usort($this->intervals, function (CarbonInterval $a, CarbonInterval $b) {
            $aSec = $a->total('seconds');
            $bSec = $b->total('seconds');

            if ($aSec < $bSec) {
                return 1;
            } elseif ($aSec > $bSec) {
                return -1;
            }

            return 0;
        });
    }

    abstract public function getNotificationClassName(): string;

    abstract protected function setUpIntervals(): void;

    public function __construct(CarbonInterface $dueDate)
    {
        $this->dueDate = new CarbonImmutable($dueDate->toAtomString(), $dueDate->getTimezone());
        $this->setUpIntervals();
        $this->validateIntervals();
    }

    public function dismissPreviousNotifications(NotificationReminderModelInterface $notificationReminder): void
    {
        // Dismiss all previous notifications of this kind as we're about to send a new one

        /** @var AbstractModel $content */
        $content = $notificationReminder->content;
        LaravelNotifications::massDelete($content->getContentType(), $content->getKey(), $notificationReminder->getNotificationClassName());
    }

    public function onBeforeNotificationFire(NotificationReminderModelInterface $notificationReminder): void
    {
        // Optionally do something when this reminder is fired
    }

    public function onAfterNotificationFire(NotificationReminderModelInterface $notificationReminder): void
    {
        // Optionally do something when this reminder is fired
    }
}