<?php

namespace Modules\Logs\Domain\Services;

use InvalidArgumentException;

/**
 * Log Validator Domain Service
 *
 * Log iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Log ID must be positive
 */
class LogValidator
{
    /**
     * Log ID validasyonu
     *
     * @param  int  $logId  Log ID
     *
     * @throws InvalidArgumentException
     */
    public function validateLogId(int $logId): void
    {
        if ($logId <= 0) {
            throw new InvalidArgumentException('Geçersiz log ID');
        }
    }

    /**
     * Bulk log IDs validasyonu
     *
     * @param  array  $logIds  Log IDs array
     *
     * @throws InvalidArgumentException
     */
    public function validateLogIds(array $logIds): void
    {
        if (empty($logIds)) {
            throw new InvalidArgumentException('Log ID listesi boş olamaz.');
        }

        foreach ($logIds as $logId) {
            if (! is_numeric($logId) || $logId <= 0) {
                throw new InvalidArgumentException('Geçersiz log ID: '.$logId);
            }
        }
    }
}
