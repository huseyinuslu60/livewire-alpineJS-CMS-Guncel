<?php

namespace Modules\Posts\Domain\Services;

use InvalidArgumentException;
use Modules\Posts\Domain\ValueObjects\PostType;

/**
 * Post Validator Domain Service
 *
 * Post iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Video posts must have embed_code
 * - Gallery posts should have at least one file (validated in Livewire)
 */
class PostValidator
{
    /**
     * Post type'a göre özel validasyon kurallarını kontrol et
     *
     * @param  array  $data  Post data
     *
     * @throws InvalidArgumentException
     */
    public function validatePostType(array $data): void
    {
        $postTypeValue = $data['post_type'] ?? null;

        if (! $postTypeValue) {
            return;
        }

        try {
            $postType = PostType::fromString($postTypeValue);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Geçersiz post tipi: {$postTypeValue}");
        }

        // Video posts must have embed_code
        if ($postType->isVideo()) {
            if (empty($data['embed_code'])) {
                throw new InvalidArgumentException('Video posts must have embed code.');
            }
        }

        // Gallery posts should have at least one file
        // This is validated in the Livewire component, not here
        // because we don't have access to files array in this context
    }

    /**
     * Post data'nın genel validasyonunu yap
     *
     * @param  array  $data  Post data
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Title validation
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Post başlığı zorunludur.');
        }

        if (strlen($data['title']) > 255) {
            throw new InvalidArgumentException('Post başlığı maksimum 255 karakter olabilir.');
        }

        // Post type validation
        $this->validatePostType($data);
    }
}
