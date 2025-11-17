<?php

namespace Modules\Settings\Services;

use App\Helpers\LogHelper;
use Modules\Settings\Domain\Services\SettingValidator;
use Modules\Settings\Models\SiteSetting;

class SettingsService
{
    protected SettingValidator $settingValidator;

    public function __construct(?SettingValidator $settingValidator = null)
    {
        $this->settingValidator = $settingValidator ?? app(SettingValidator::class);
    }

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
            // Validate setting
            $this->settingValidator->validate($settingId, $value);

            $setting = SiteSetting::find($settingId);

            if (! $setting) {
                throw new \InvalidArgumentException('Ayar bulunamadı');
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
            // Validate all settings
            $this->settingValidator->validateBulk($settings);

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

                        // Value validation (already validated in validateBulk, but double-check for safety)
                        try {
                            $this->settingValidator->validate($setting['id'], $value);
                        } catch (\InvalidArgumentException $e) {
                            LogHelper::warning('Ayar değeri geçersiz, atlanıyor', [
                                'id' => $setting['id'],
                                'error' => $e->getMessage(),
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

