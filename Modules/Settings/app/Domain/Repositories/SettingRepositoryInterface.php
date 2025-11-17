<?php

namespace Modules\Settings\Domain\Repositories;

use Modules\Settings\Models\SiteSetting;

interface SettingRepositoryInterface
{
    public function findById(int $id): ?SiteSetting;
    public function findByKey(string $key): ?SiteSetting;
    public function update(SiteSetting $setting, array $data): SiteSetting;
    public function updateBulk(array $settings): int;
}

