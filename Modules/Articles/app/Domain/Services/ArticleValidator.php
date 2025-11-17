<?php

namespace Modules\Articles\Domain\Services;

use InvalidArgumentException;
use Modules\Articles\Domain\ValueObjects\ArticleStatus;

/**
 * Article Validator Domain Service
 * 
 * Article iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Article title is required and max 255 characters
 * - Article status must be valid
 * - Published articles must have published_at date
 */
class ArticleValidator
{
    /**
     * Article data'nın validasyonunu yap
     * 
     * @param array $data Article data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Article başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Article başlığı maksimum 255 karakter olabilir.');
        }

        // Status validation
        if (isset($data['status'])) {
            try {
                $status = ArticleStatus::fromString($data['status']);
                
                // Published articles must have published_at
                if ($status->isPublished() && empty($data['published_at'])) {
                    $data['published_at'] = now();
                }
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Geçersiz article durumu: {$data['status']}");
            }
        }
    }

    /**
     * Article status validasyonu
     * 
     * @param string $status Article status
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateStatus(string $status): void
    {
        try {
            ArticleStatus::fromString($status);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Geçersiz article durumu: {$status}");
        }
    }

    /**
     * Published article için published_at kontrolü
     * 
     * @param array $data Article data
     * @return array Updated data with published_at if needed
     */
    public function ensurePublishedAt(array $data): array
    {
        if (isset($data['status'])) {
            try {
                $status = ArticleStatus::fromString($data['status']);
                if ($status->isPublished() && empty($data['published_at'])) {
                    $data['published_at'] = now();
                }
            } catch (InvalidArgumentException $e) {
                // Status invalid, skip
            }
        }

        return $data;
    }
}

