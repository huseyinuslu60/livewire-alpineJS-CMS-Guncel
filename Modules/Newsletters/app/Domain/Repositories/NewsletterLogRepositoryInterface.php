<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterLog;

interface NewsletterLogRepositoryInterface
{
    public function findById(int $logId): ?NewsletterLog;

    public function delete(NewsletterLog $log): bool;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
