<?php

namespace Modules\Banks\Domain\Repositories;

use Modules\Banks\Models\Stock;

class EloquentStockRepository implements StockRepositoryInterface
{
    public function findById(int $stockId): ?Stock
    {
        return Stock::find($stockId);
    }

    public function create(array $data): Stock
    {
        return Stock::create($data);
    }

    public function update(Stock $stock, array $data): Stock
    {
        $stock->update($data);
        return $stock->fresh();
    }

    public function delete(Stock $stock): bool
    {
        return $stock->delete();
    }
}

