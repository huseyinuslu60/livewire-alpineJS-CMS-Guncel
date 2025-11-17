<?php

namespace Modules\Settings\Domain\Events;

class SettingsBulkUpdated
{
    public int $updatedCount;
    public array $settingIds;

    public function __construct(int $updatedCount, array $settingIds)
    {
        $this->updatedCount = $updatedCount;
        $this->settingIds = $settingIds;
    }
}

