<?php

namespace Modules\Settings\Domain\Events;

use Modules\Settings\Models\SiteSetting;

class SettingUpdated
{
    public SiteSetting $setting;
    public array $changedAttributes;

    public function __construct(SiteSetting $setting, array $changedAttributes = [])
    {
        $this->setting = $setting;
        $this->changedAttributes = $changedAttributes;
    }
}

