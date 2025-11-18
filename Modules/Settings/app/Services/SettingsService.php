<?php

namespace Modules\Settings\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Settings\Domain\Events\SettingsBulkUpdated;
use Modules\Settings\Domain\Events\SettingUpdated;
use Modules\Settings\Domain\Repositories\SettingRepositoryInterface;
use Modules\Settings\Domain\Services\SettingValidator;
use Modules\Settings\Models\SiteSetting;

class SettingsService
{
    protected SettingValidator $settingValidator;

    protected SettingRepositoryInterface $settingRepository;

    public function __construct(
        ?SettingValidator $settingValidator = null,
        ?SettingRepositoryInterface $settingRepository = null
    ) {
        $this->settingValidator = $settingValidator ?? app(SettingValidator::class);
        $this->settingRepository = $settingRepository ?? app(SettingRepositoryInterface::class);
    }

    /**
     * Update a single setting
     *
     * @param  mixed  $value
     */
    public function updateSetting(int $settingId, $value): bool
    {
        try {
            // Validate setting
            $this->settingValidator->validate($settingId, $value);

            return DB::transaction(function () use ($settingId, $value) {
                $setting = $this->settingRepository->findById($settingId);

                if (! $setting) {
                    throw new \InvalidArgumentException('Ayar bulunamadı');
                }

                $setting = $this->settingRepository->update($setting, ['value' => $value]);

                // Fire domain event
                Event::dispatch(new SettingUpdated($setting, ['value']));

                LogHelper::info('Ayar güncellendi', [
                    'id' => $settingId,
                    'key' => $setting->key,
                ]);

                return true;
            });
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
     * @return int Number of updated settings
     */
    public function updateSettings(array $settings): int
    {
        try {
            // Validate all settings
            $this->settingValidator->validateBulk($settings);

            return DB::transaction(function () use ($settings) {
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

                // Update via repository
                $updatedCount = $this->settingRepository->updateBulk($settings);

                // Fire domain event
                Event::dispatch(new SettingsBulkUpdated($updatedCount, $settingIds));

                return $updatedCount;
            });
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
     * @param  mixed  $value
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
     */
    public function getAllSettings(): array
    {
        return SiteSetting::getAllSettings();
    }

    /**
     * Get settings by group
     */
    public function getSettingsByGroup(string $group): array
    {
        return SiteSetting::getSettingsByGroup($group);
    }

    /**
     * Find a setting by ID
     */
    public function getSettingById(int $settingId): ?\Modules\Settings\Models\SiteSetting
    {
        return $this->settingRepository->findById($settingId);
    }
}
