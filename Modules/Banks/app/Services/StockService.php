<?php

namespace Modules\Banks\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Modules\Banks\Domain\Services\StockValidator;
use Modules\Banks\Models\Stock;

class StockService
{
    protected StockValidator $stockValidator;

    public function __construct(?StockValidator $stockValidator = null)
    {
        $this->stockValidator = $stockValidator ?? app(StockValidator::class);
    }

    /**
     * Create a new stock
     */
    public function create(array $data): Stock
    {
        try {
            // Validate stock data
            $this->stockValidator->validate($data);

            return DB::transaction(function () use ($data) {
                $stock = Stock::create($data);

                LogHelper::info('Hisse senedi oluşturuldu', [
                    'stock_id' => $stock->stock_id,
                    'name' => $stock->name,
                ]);

                return $stock;
            });
        } catch (\Exception $e) {
            LogHelper::error('StockService create error', [
                'name' => $data['name'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing stock
     */
    public function update(Stock $stock, array $data): Stock
    {
        try {
            // Validate stock data
            $this->stockValidator->validate($data);

            return DB::transaction(function () use ($stock, $data) {
                $stock->update($data);

                LogHelper::info('Hisse senedi güncellendi', [
                    'stock_id' => $stock->stock_id,
                    'name' => $stock->name,
                ]);

                return $stock;
            });
        } catch (\Exception $e) {
            LogHelper::error('StockService update error', [
                'stock_id' => $stock->stock_id,
                'error' => $e->getMessage(),
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

                LogHelper::info('Hisse senedi silindi', [
                    'stock_id' => $stock->stock_id,
                    'name' => $stock->name,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('StockService delete error', [
                'stock_id' => $stock->stock_id,
                'error' => $e->getMessage(),
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

                LogHelper::info('Hisse senetleri toplu silindi', [
                    'count' => $deletedCount,
                    'stock_ids' => $stockIds,
                ]);

                return $deletedCount;
            });
        } catch (\Exception $e) {
            LogHelper::error('StockService bulkDelete error', [
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

                LogHelper::info('Hisse senetleri toplu durum güncellendi', [
                    'count' => $updated,
                    'status' => $status,
                    'stock_ids' => $stockIds,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            LogHelper::error('StockService bulkUpdateStatus error', [
                'stock_ids' => $stockIds,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

