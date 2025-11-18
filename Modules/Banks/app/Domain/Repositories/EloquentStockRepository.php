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

    public function bulkUpdateStatus(array $stockIds, string $status): int
    {
        return Stock::whereIn('stock_id', $stockIds)
            ->update(['last_status' => $status]);
    }

    public function findByIds(array $stockIds): \Illuminate\Database\Eloquent\Collection
    {
        return Stock::whereIn('stock_id', $stockIds)->get();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Stock::query();
    }
}
