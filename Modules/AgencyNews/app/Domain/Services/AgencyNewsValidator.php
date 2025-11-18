<?php

namespace Modules\AgencyNews\Domain\Services;

use InvalidArgumentException;

/**
 * AgencyNews Validator Domain Service
 *
 * AgencyNews iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - AgencyNews title is required and max 255 characters
 */
class AgencyNewsValidator
{
    /**
     * AgencyNews data'nın validasyonunu yap
     *
     * @param  array  $data  AgencyNews data
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('AgencyNews başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('AgencyNews başlığı maksimum 255 karakter olabilir.');
        }
    }
}
