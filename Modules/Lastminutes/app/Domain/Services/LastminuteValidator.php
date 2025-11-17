<?php

namespace Modules\Lastminutes\Domain\Services;

use InvalidArgumentException;

/**
 * Lastminute Validator Domain Service
 * 
 * Lastminute iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Lastminute title is required and max 255 characters
 */
class LastminuteValidator
{
    /**
     * Lastminute data'nın validasyonunu yap
     * 
     * @param array $data Lastminute data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Lastminute başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Lastminute başlığı maksimum 255 karakter olabilir.');
        }
    }
}

