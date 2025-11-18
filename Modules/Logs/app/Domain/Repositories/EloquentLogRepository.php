<?php

namespace Modules\Logs\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Logs\Models\UserLog>
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Logs\Models\UserLog> $query */
        $query = UserLog::query()->with(['user']);

        // Search filter
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Action filter
        if (! empty($filters['action'])) {
            $query->ofAction($filters['action']);
        }

        // User filter
        if (! empty($filters['user_id'])) {
            $query->ofUser($filters['user_id']);
        }

        // Date range filters
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Logs\Models\UserLog>
     */
    public function getQuery(): Builder
    {
        return UserLog::query();
    }

    public function getStatsQuery(): Builder
    {
        return UserLog::query();
    }
}
