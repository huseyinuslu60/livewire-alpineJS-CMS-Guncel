<?php

namespace Modules\Banks\Domain\Repositories;

use Modules\Banks\Models\Stock;

interface StockRepositoryInterface
{
    public function findById(int $stockId): ?Stock;
    public function create(array $data): Stock;
    public function update(Stock $stock, array $data): Stock;
    public function delete(Stock $stock): bool;
}

