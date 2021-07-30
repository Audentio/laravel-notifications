<?php

namespace Audentio\LaravelNotifications\Utils;

class TimerUtil
{
    private float $start;
    private float $limit;

    public function getTimeDiff(): float
    {
        return microtime(true) - $this->start;
    }

    public function isLimitReached(): bool
    {
        return $this->getTimeDiff() >= $this->limit;
    }

    public function reset(): void
    {
        $this->start = microtime(true);
    }

    public function __construct(float $limit)
    {
        $this->limit = $limit;
        $this->reset();
    }
}