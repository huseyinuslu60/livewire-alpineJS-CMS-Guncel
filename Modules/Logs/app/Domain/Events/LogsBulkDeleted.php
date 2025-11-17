<?php

namespace Modules\Logs\Domain\Events;

class LogsBulkDeleted
{
    public int $deletedCount;
    public array $logIds;

    public function __construct(int $deletedCount, array $logIds)
    {
        $this->deletedCount = $deletedCount;
        $this->logIds = $logIds;
    }
}

