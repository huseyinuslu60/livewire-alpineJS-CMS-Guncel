<?php

namespace Modules\Headline\Domain\Events;

use Modules\Headline\app\Models\Featured;

class FeaturedUpdated
{
    public Featured $featured;
    public array $changedAttributes;

    public function __construct(Featured $featured, array $changedAttributes = [])
    {
        $this->featured = $featured;
        $this->changedAttributes = $changedAttributes;
    }
}

