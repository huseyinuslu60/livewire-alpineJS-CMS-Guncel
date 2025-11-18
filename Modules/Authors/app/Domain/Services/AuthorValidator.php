<?php

namespace Modules\Authors\Domain\Services;

use InvalidArgumentException;

/**
 * Author Validator Domain Service
 *
 * Author iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Author title is required and max 255 characters
 */
class AuthorValidator
{
    /**
     * Author data'nın validasyonunu yap
     *
     * @param  array  $data  Author data
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Author başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Author başlığı maksimum 255 karakter olabilir.');
        }
    }
}
