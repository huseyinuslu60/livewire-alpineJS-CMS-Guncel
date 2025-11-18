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
     * @param  array  $data  File data
     * @param  bool  $isUpdate  Update işlemi mi? (true ise title zorunlu değil)
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data, bool $isUpdate = false): void
    {
        // Title validation (sadece create işleminde zorunlu)
        if (! $isUpdate && empty($data['title'])) {
            throw new InvalidArgumentException('Dosya başlığı zorunludur.');
        }

        if (isset($data['title']) && strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Dosya başlığı maksimum 255 karakter olabilir.');
        }

        // File path validation (if provided)
        if (isset($data['file_path']) && empty($data['file_path'])) {
            throw new InvalidArgumentException('Dosya yolu zorunludur.');
        }

        // Alt text validation (if provided)
        if (isset($data['alt_text']) && strlen($data['alt_text']) > 255) {
            throw new InvalidArgumentException('Alt metin maksimum 255 karakter olabilir.');
        }

        // Caption validation (if provided)
        if (isset($data['caption']) && strlen($data['caption']) > 10000) {
            throw new InvalidArgumentException('Açıklama maksimum 10000 karakter olabilir.');
        }
    }
}
