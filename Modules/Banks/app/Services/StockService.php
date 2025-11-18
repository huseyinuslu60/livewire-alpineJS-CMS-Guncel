<?php

namespace Modules\Banks\Services;

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Banks\Domain\Events\StockCreated;
use Modules\Banks\Domain\Events\StockDeleted;
use Modules\Banks\Domain\Events\StockUpdated;
use Modules\Banks\Domain\Repositories\StockRepositoryInterface;
use Modules\Banks\Domain\Services\StockValidator;
use Modules\Banks\Models\Stock;

class StockService
{
    protected StockValidator $stockValidator;

    protected StockRepositoryInterface $stockRepository;

    public function __construct(
        ?StockValidator $stockValidator = null,
        ?StockRepositoryInterface $stockRepository = null
    ) {
        $this->stockValidator = $stockValidator ?? app(StockValidator::class);
        $this->stockRepository = $stockRepository ?? app(StockRepositoryInterface::class);
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
                $stock = $this->stockRepository->create($data);

                // Fire domain event
                Event::dispatch(new StockCreated($stock));

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
                $stock = $this->stockRepository->update($stock, $data);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new StockUpdated($stock, $changedAttributes));

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
                $this->stockRepository->delete($stock);

                // Fire domain event
                Event::dispatch(new StockDeleted($stock));

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
                $stocks = $this->stockRepository->findByIds($stockIds);
                $deletedCount = 0;

                /** @var \Modules\Banks\Models\Stock $stock */
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
     * Find a stock by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $stockId): Stock
    {
        $stock = $this->stockRepository->findById($stockId);

        if (! $stock) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Stock not found');
        }

        return $stock;
    }

    /**
     * Get query builder for stocks
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Banks\Models\Stock>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->stockRepository->getQuery();
    }

    /**
     * Bulk update stock status
     */
    public function bulkUpdateStatus(array $stockIds, string $status): int
    {
        try {
            return DB::transaction(function () use ($stockIds, $status) {
                $updated = $this->stockRepository->bulkUpdateStatus($stockIds, $status);

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
