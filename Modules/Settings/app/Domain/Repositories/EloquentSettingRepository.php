<?php

namespace Modules\Settings\Domain\Repositories;

use Modules\Settings\Models\SiteSetting;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function findById(int $id): ?SiteSetting
    {
        return SiteSetting::find($id);
    }

    public function findByKey(string $key): ?SiteSetting
    {
        return SiteSetting::where('key', $key)->first();
    }

    public function update(SiteSetting $setting, array $data): SiteSetting
    {
        $setting->update($data);
        return $setting->fresh();
    }

    public function updateBulk(array $settings): int
    {
        $updatedCount = 0;
        $siteSettings = SiteSetting::whereIn('id', array_column($settings, 'id'))->get()->keyBy('id');

        foreach ($settings as $setting) {
            if (isset($setting['id']) && isset($setting['value']) && isset($siteSettings[$setting['id']])) {
                $siteSettings[$setting['id']]->update(['value' => $setting['value']]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}

