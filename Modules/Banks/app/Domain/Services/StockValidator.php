<?php

namespace Modules\Banks\Domain\Services;

use InvalidArgumentException;

/**
 * Stock Validator Domain Service
 * 
 * Stock iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Stock name is required and max 255 characters
 */
class StockValidator
{
    /**
     * Stock data'nın validasyonunu yap
     * 
     * @param array $data Stock data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Hisse senedi adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Hisse senedi adı maksimum 255 karakter olabilir.');
        }
    }
}

