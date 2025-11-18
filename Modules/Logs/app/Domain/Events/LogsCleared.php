<?php

namespace Modules\Logs\Domain\Events;

class LogsCleared
{
    public int $deletedCount;

    public function __construct(int $deletedCount)
    {
        $this->deletedCount = $deletedCount;
    }
}
