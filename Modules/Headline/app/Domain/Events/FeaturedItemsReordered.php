<?php

namespace Modules\Headline\Domain\Events;

class FeaturedItemsReordered
{
    public string $zone;

    public array $orderedItems;

    public function __construct(string $zone, array $orderedItems)
    {
        $this->zone = $zone;
        $this->orderedItems = $orderedItems;
    }
}
