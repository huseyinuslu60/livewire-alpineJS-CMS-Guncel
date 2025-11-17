<?php

namespace Modules\Headline\Domain\Services;

use InvalidArgumentException;

/**
 * Featured Validator Domain Service
 * 
 * Featured iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Zone is required
 * - Subject type is required
 * - Subject ID must be positive
 * - Ends at must be after starts at
 */
class FeaturedValidator
{
    /**
     * Featured data'nın validasyonunu yap
     * 
     * @param string $zone Zone name
     * @param string $subjectType Subject type
     * @param int $subjectId Subject ID
     * @param \DateTime|null $startsAt Start date
     * @param \DateTime|null $endsAt End date
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(
        string $zone,
        string $subjectType,
        int $subjectId,
        ?\DateTime $startsAt = null,
        ?\DateTime $endsAt = null
    ): void {
        // Zone validation
        if (empty($zone)) {
            throw new InvalidArgumentException('Zone zorunludur.');
        }

        // Subject type validation
        if (empty($subjectType)) {
            throw new InvalidArgumentException('Subject type zorunludur.');
        }

        // Subject ID validation
        if ($subjectId <= 0) {
            throw new InvalidArgumentException('Subject ID pozitif bir sayı olmalıdır.');
        }

        // Date validation
        if ($startsAt && $endsAt && $endsAt < $startsAt) {
            throw new InvalidArgumentException('Bitiş tarihi başlangıç tarihinden önce olamaz.');
        }
    }
}

