<?php

namespace Modules\AgencyNews\Domain\Events;

use Modules\AgencyNews\Models\AgencyNews;

class AgencyNewsUpdated
{
    public AgencyNews $agencyNews;
    public array $changedAttributes;

    public function __construct(AgencyNews $agencyNews, array $changedAttributes = [])
    {
        $this->agencyNews = $agencyNews;
        $this->changedAttributes = $changedAttributes;
    }
}

