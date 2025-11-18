<?php

namespace Modules\Banks\Domain\Repositories;

use Modules\Banks\Models\Stock;

interface StockRepositoryInterface
{
    public function findById(int $stockId): ?Stock;

    public function create(array $data): Stock;

    public function update(Stock $stock, array $data): Stock;

    public function delete(Stock $stock): bool;

    public function bulkUpdateStatus(array $stockIds, string $status): int;

    public function findByIds(array $stockIds): \Illuminate\Database\Eloquent\Collection;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
