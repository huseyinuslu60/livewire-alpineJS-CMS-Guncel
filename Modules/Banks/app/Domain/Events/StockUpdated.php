<?php

namespace Modules\Banks\Domain\Events;

use Modules\Banks\Models\Stock;

class StockUpdated
{
    public Stock $stock;
    public array $changedAttributes;

    public function __construct(Stock $stock, array $changedAttributes = [])
    {
        $this->stock = $stock;
        $this->changedAttributes = $changedAttributes;
    }
}

