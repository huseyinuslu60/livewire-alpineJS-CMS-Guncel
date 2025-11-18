<?php

namespace Modules\Settings\Domain\Services;

use InvalidArgumentException;

/**
 * Setting Validator Domain Service
 *
 * Setting iş kurallarını ve validasyon mantığını yönetir.
 * Business rules:
 * - Setting ID must be valid
 * - Setting value must not exceed 65535 characters
 */
class SettingValidator
{
    /**
     * Setting data'nın validasyonunu yap
     *
     * @param  int  $settingId  Setting ID
     * @param  mixed  $value  Setting value
     *
     * @throws InvalidArgumentException
     */
    public function validate(int $settingId, $value): void
    {
        // Setting ID validation
        if (empty($settingId)) {
            throw new InvalidArgumentException('Geçersiz ayar ID');
        }

        // Value validation
        if (is_string($value) && strlen($value) > 65535) {
            throw new InvalidArgumentException('Ayar değeri çok uzun (maksimum 65535 karakter)');
        }
    }

    /**
     * Bulk settings validation
     *
     * @param  array  $settings  Array of ['id' => int, 'value' => mixed]
     *
     * @throws InvalidArgumentException
     */
    public function validateBulk(array $settings): void
    {
        foreach ($settings as $setting) {
            if (! isset($setting['id']) || ! isset($setting['value'])) {
                throw new InvalidArgumentException('Geçersiz ayar formatı. Her ayar id ve value içermelidir.');
            }

            $this->validate($setting['id'], $setting['value']);
        }
    }
}
