<?php

namespace Modules\Settings\Services;

use App\Helpers\LogHelper;
use Modules\Settings\Models\SiteSetting;

class SettingsService
{
    /**
     * Update a single setting
     *
     * @param  int  $settingId
     * @param  mixed  $value
     * @return bool
     */
    public function updateSetting(int $settingId, $value): bool
    {
        try {
            // Validation
            if (empty($settingId)) {
                throw new \InvalidArgumentException('Geçersiz ayar ID');
            }

            $setting = SiteSetting::find($settingId);

            if (! $setting) {
                throw new \InvalidArgumentException('Ayar bulunamadı');
            }

            // Value validation
            if (is_string($value) && strlen($value) > 65535) {
                throw new \InvalidArgumentException('Ayar değeri çok uzun (maksimum 65535 karakter)');
            }

            $setting->update(['value' => $value]);

            LogHelper::info('Ayar güncellendi', [
                'id' => $settingId,
                'key' => $setting->key,
            ]);

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Ayar güncellenirken hata oluştu', [
                'settingId' => $settingId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update multiple settings (bulk update)
     *
     * @param  array  $settings  Array of ['id' => int, 'value' => mixed]
     * @return int  Number of updated settings
     */
    public function updateSettings(array $settings): int
    {
        try {
            // Tüm ID'leri topla (N+1 query önleme)
            $settingIds = [];
            foreach ($settings as $setting) {
                if (isset($setting['id']) && isset($setting['value'])) {
                    $settingIds[] = $setting['id'];
                }
            }

            if (empty($settingIds)) {
                return 0;
            }

            // Tüm settings'i tek seferde yükle (N+1 query önleme)
            $siteSettings = SiteSetting::whereIn('id', $settingIds)->get()->keyBy('id');

            $updatedCount = 0;

            foreach ($settings as $setting) {
                if (isset($setting['id']) && isset($setting['value'])) {
                    $siteSetting = $siteSettings->get($setting['id']);
                    if ($siteSetting) {
                        $value = $setting['value'];

                        // Value validation
                        if (is_string($value) && strlen($value) > 65535) {
                            LogHelper::warning('Ayar değeri çok uzun, atlanıyor', [
                                'id' => $setting['id'],
                                'length' => strlen($value),
                            ]);
                            continue;
                        }

                        $siteSetting->update(['value' => $value]);
                        $updatedCount++;

                        LogHelper::info('Ayar güncellendi', [
                            'id' => $setting['id'],
                            'key' => $siteSetting->key,
                        ]);
                    }
                }
            }

            return $updatedCount;
        } catch (\Exception $e) {
            LogHelper::error('Ayarlar güncellenirken hata oluştu', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get setting by key
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return SiteSetting::getSetting($key, $default);
    }

    /**
     * Set setting by key
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function setSetting(string $key, $value): bool
    {
        try {
            return SiteSetting::setSetting($key, $value);
        } catch (\Exception $e) {
            LogHelper::error('Ayar kaydedilirken hata oluştu', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get all settings as key-value array
     *
     * @return array
     */
    public function getAllSettings(): array
    {
        return SiteSetting::getAllSettings();
    }

    /**
     * Get settings by group
     *
     * @param  string  $group
     * @return array
     */
    public function getSettingsByGroup(string $group): array
    {
        return SiteSetting::getSettingsByGroup($group);
    }
}

