<?php

namespace Modules\Logs\Domain\Events;

use Modules\Logs\Models\UserLog;

class LogDeleted
{
    public UserLog $log;

    public function __construct(UserLog $log)
    {
        $this->log = $log;
    }
}
