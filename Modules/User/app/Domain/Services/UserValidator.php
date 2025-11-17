<?php

namespace Modules\User\Domain\Services;

use InvalidArgumentException;

/**
 * User Validator Domain Service
 * 
 * User iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - User name is required and max 255 characters
 * - User email is required and must be valid email format
 * - Password must be at least 8 characters (if provided)
 */
class UserValidator
{
    /**
     * User data'nın validasyonunu yap
     * 
     * @param array $data User data
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(array $data): void
    {
        // Name validation
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Kullanıcı adı zorunludur.');
        }

        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException('Kullanıcı adı maksimum 255 karakter olabilir.');
        }

        // Email validation
        if (empty($data['email'])) {
            throw new InvalidArgumentException('E-posta adresi zorunludur.');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Geçersiz e-posta adresi formatı.');
        }

        // Password validation (if provided)
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new InvalidArgumentException('Şifre en az 8 karakter olmalıdır.');
            }
        }
    }
}

