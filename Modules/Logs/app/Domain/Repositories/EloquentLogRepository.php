<?php

namespace Modules\Logs\Domain\Repositories;

use Modules\Logs\Models\UserLog;

class EloquentLogRepository implements LogRepositoryInterface
{
    public function findById(int $logId): ?UserLog
    {
        return UserLog::find($logId);
    }

    public function delete(UserLog $log): bool
    {
        return $log->delete();
    }

    public function deleteBulk(array $logIds): int
    {
        return UserLog::whereIn('log_id', $logIds)->delete();
    }

    public function clearAll(): int
    {
        $count = UserLog::count();
        UserLog::truncate();
        return $count;
    }
}

