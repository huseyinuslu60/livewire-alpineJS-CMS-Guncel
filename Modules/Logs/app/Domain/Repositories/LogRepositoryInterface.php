<?php

namespace Modules\Logs\Domain\Repositories;

use Modules\Logs\Models\UserLog;

interface LogRepositoryInterface
{
    public function findById(int $logId): ?UserLog;
    public function delete(UserLog $log): bool;
    public function deleteBulk(array $logIds): int;
    public function clearAll(): int;
}

