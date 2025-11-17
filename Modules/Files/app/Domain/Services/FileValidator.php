<?php

namespace Modules\Files\Domain\Services;

use InvalidArgumentException;

/**
 * File Validator Domain Service
 *
 * File iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - File title is required and max 255 characters
 * - File path is required
 */
class FileValidator
{
    /**
     * File data'nın validasyonunu yap
     *
     * @param array $data File data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Dosya başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Dosya başlığı maksimum 255 karakter olabilir.');
        }

        // File path validation (if provided)
        if (isset($data['file_path']) && empty($data['file_path'])) {
            throw new InvalidArgumentException('Dosya yolu zorunludur.');
        }
    }
}

