<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterLog;

class EloquentNewsletterLogRepository implements NewsletterLogRepositoryInterface
{
    public function findById(int $logId): ?NewsletterLog
    {
        return NewsletterLog::find($logId);
    }

    public function delete(NewsletterLog $log): bool
    {
        return $log->delete();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return NewsletterLog::query();
    }
}
