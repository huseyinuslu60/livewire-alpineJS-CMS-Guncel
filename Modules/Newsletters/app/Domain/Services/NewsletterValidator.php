<?php

namespace Modules\Newsletters\Domain\Services;

use InvalidArgumentException;

/**
 * Newsletter Validator Domain Service
 * 
 * Newsletter iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Newsletter name is required and max 255 characters
 * - Mail subject is required and max 255 characters
 */
class NewsletterValidator
{
    /**
     * Newsletter data'nın validasyonunu yap
     * 
     * @param array $data Newsletter data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Newsletter adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Newsletter adı maksimum 255 karakter olabilir.');
        }

        // Mail subject validation
        if (isset($data['mail_subject']) && strlen($data['mail_subject']) > 255) {
            throw new InvalidArgumentException('E-posta konusu maksimum 255 karakter olabilir.');
        }
    }
}

