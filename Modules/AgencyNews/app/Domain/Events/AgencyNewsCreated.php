<?php

namespace Modules\AgencyNews\Domain\Events;

use Modules\AgencyNews\Models\AgencyNews;

class AgencyNewsCreated
{
    public AgencyNews $agencyNews;

    public function __construct(AgencyNews $agencyNews)
    {
        $this->agencyNews = $agencyNews;
    }
}

