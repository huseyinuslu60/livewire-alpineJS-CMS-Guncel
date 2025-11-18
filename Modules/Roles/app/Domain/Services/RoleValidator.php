<?php

namespace Modules\Roles\Domain\Services;

use InvalidArgumentException;

/**
 * Role Validator Domain Service
 *
 * Role iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Role name is required and max 255 characters
 * - Role name must be unique
 */
class RoleValidator
{
    /**
     * Role data'nın validasyonunu yap
     *
     * @param  array  $data  Role data
     * @param  int|null  $excludeId  Role ID to exclude (for updates)
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data, ?int $excludeId = null): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Rol adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Rol adı maksimum 255 karakter olabilir.');
        }

        // Display name validation (if provided)
        if (isset($data['display_name']) && strlen($data['display_name']) > 255) {
            throw new InvalidArgumentException('Rol görünen adı maksimum 255 karakter olabilir.');
        }
    }
}
