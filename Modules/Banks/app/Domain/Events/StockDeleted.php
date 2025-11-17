<?php

namespace Modules\Banks\Domain\Events;

use Modules\Banks\Models\Stock;

class StockDeleted
{
    public Stock $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }
}

