<?php

namespace Modules\Banks\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Banks\Models\Stock;

class StockService
{
    /**
     * Create a new stock
     */
    public function create(array $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::create($data);

            Log::info('Stock created', [
                'stock_id' => $stock->stock_id,
                'name' => $stock->name,
            ]);

            return $stock;
        });
    }

    /**
     * Update an existing stock
     */
    public function update(Stock $stock, array $data): Stock
    {
        try {
            return DB::transaction(function () use ($stock, $data) {
                $stock->update($data);

                Log::info('Stock updated', [
                    'stock_id' => $stock->stock_id,
                    'name' => $stock->name,
                ]);

                return $stock;
            });
        } catch (\Exception $e) {
            Log::error('StockService update error:', [
                'stock_id' => $stock->stock_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a stock
     */
    public function delete(Stock $stock): void
    {
        try {
            DB::transaction(function () use ($stock) {
                $stock->delete();

                Log::info('Stock deleted', [
                    'stock_id' => $stock->stock_id,
                    'name' => $stock->name,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('StockService delete error:', [
                'stock_id' => $stock->stock_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk delete stocks
     */
    public function bulkDelete(array $stockIds): int
    {
        try {
            return DB::transaction(function () use ($stockIds) {
                $stocks = Stock::whereIn('stock_id', $stockIds)->get();
                $deletedCount = 0;

                foreach ($stocks as $stock) {
                    $this->delete($stock);
                    $deletedCount++;
                }

                Log::info('Stocks bulk deleted', [
                    'count' => $deletedCount,
                    'stock_ids' => $stockIds,
                ]);

                return $deletedCount;
            });
        } catch (\Exception $e) {
            Log::error('StockService bulkDelete error:', [
                'stock_ids' => $stockIds,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk update stock status
     */
    public function bulkUpdateStatus(array $stockIds, string $status): int
    {
        try {
            return DB::transaction(function () use ($stockIds, $status) {
                $updated = Stock::whereIn('stock_id', $stockIds)
                    ->update(['last_status' => $status]);

                Log::info('Stocks bulk status updated', [
                    'count' => $updated,
                    'status' => $status,
                    'stock_ids' => $stockIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            Log::error('StockService bulkUpdateStatus error:', [
                'stock_ids' => $stockIds,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

