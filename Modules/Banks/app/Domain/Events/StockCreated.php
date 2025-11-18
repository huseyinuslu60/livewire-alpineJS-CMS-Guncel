<?php

namespace Modules\Banks\Domain\Events;

use Modules\Banks\Models\Stock;

class StockCreated
{
    public Stock $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }
}
