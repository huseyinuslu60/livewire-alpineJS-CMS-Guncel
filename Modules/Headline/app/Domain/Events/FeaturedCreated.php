<?php

namespace Modules\Headline\Domain\Events;

use Modules\Headline\app\Models\Featured;

class FeaturedCreated
{
    public Featured $featured;

    public function __construct(Featured $featured)
    {
        $this->featured = $featured;
    }
}

