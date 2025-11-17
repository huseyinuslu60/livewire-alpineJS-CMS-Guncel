<?php

namespace Modules\Newsletters\Domain\Services;

use InvalidArgumentException;

/**
 * NewsletterTemplate Validator Domain Service
 * 
 * NewsletterTemplate iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Template name is required and max 255 characters
 * - Template slug is required and max 255 characters
 * - HTML fields are required
 */
class NewsletterTemplateValidator
{
    /**
     * NewsletterTemplate data'nın validasyonunu yap
     * 
     * @param array $data NewsletterTemplate data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Template adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Template adı maksimum 255 karakter olabilir.');
        }

        // Slug validation
        if (isset($data['slug']) && strlen($data['slug']) > 255) {
            throw new InvalidArgumentException('Template slug maksimum 255 karakter olabilir.');
        }

        // HTML fields validation
        if (isset($data['header_html']) && empty($data['header_html'])) {
            throw new InvalidArgumentException('Header HTML zorunludur.');
        }

        if (isset($data['content_html']) && empty($data['content_html'])) {
            throw new InvalidArgumentException('Content HTML zorunludur.');
        }

        if (isset($data['footer_html']) && empty($data['footer_html'])) {
            throw new InvalidArgumentException('Footer HTML zorunludur.');
        }
    }
}

