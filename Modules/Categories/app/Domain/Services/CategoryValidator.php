<?php

namespace Modules\Categories\Domain\Services;

use InvalidArgumentException;
use Modules\Categories\Domain\ValueObjects\CategoryStatus;
use Modules\Categories\Domain\ValueObjects\CategoryType;

/**
 * Category Validator Domain Service
 *
 * Category iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Category name is required and max 255 characters
 * - Category type must be valid
 * - Category status must be valid
 */
class CategoryValidator
{
    /**
     * Category data'nın validasyonunu yap
     *
     * @param  array  $data  Category data
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Category adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Category adı maksimum 255 karakter olabilir.');
        }

        // Type validation
        if (isset($data['type'])) {
            try {
                CategoryType::fromString($data['type']);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Geçersiz category tipi: {$data['type']}");
            }
        }

        // Status validation
        if (isset($data['status'])) {
            try {
                CategoryStatus::fromString($data['status']);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Geçersiz category durumu: {$data['status']}");
            }
        }
    }

    /**
     * Category type validasyonu
     *
     * @param  string  $type  Category type
     *
     * @throws InvalidArgumentException
     */
    public function validateType(string $type): void
    {
        try {
            CategoryType::fromString($type);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Geçersiz category tipi: {$type}");
        }
    }

    /**
     * Category status validasyonu
     *
     * @param  string  $status  Category status
     *
     * @throws InvalidArgumentException
     */
    public function validateStatus(string $status): void
    {
        try {
            CategoryStatus::fromString($status);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Geçersiz category durumu: {$status}");
        }
    }
}
