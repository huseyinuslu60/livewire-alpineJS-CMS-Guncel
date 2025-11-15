<?php

namespace Modules\Banks\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Banks\Models\Stock;

class StockService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Stock::query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('last_status', $filters['status']);
        }

        return $query->orderBy('stock_id', 'desc');
    }

    /**
     * Stock oluştur
     *
     * @param  array<string, mixed>  $data  Stock verileri
     * @return Stock
     */
    public function create(array $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::create($data);

            Log::info('Stock created via StockService', [
                'stock_id' => $stock->stock_id,
            ]);

            return $stock;
        });
    }

    /**
     * Stock güncelle
     *
     * @param  Stock  $stock  Stock modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @return Stock
     */
    public function update(Stock $stock, array $data): Stock
    {
        return DB::transaction(function () use ($stock, $data) {
            $stock->update($data);

            Log::info('Stock updated via StockService', [
                'stock_id' => $stock->stock_id,
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Stock sil
     *
     * @param  Stock  $stock  Stock modeli
     * @return void
     */
    public function delete(Stock $stock): void
    {
        DB::transaction(function () use ($stock) {
            $stockId = $stock->stock_id;
            $stock->delete();

            Log::info('Stock deleted via StockService', [
                'stock_id' => $stockId,
            ]);
        });
    }

    /**
     * Toplu işlem uygula
     *
     * @param  string  $action  İşlem tipi (delete, activate, deactivate)
     * @param  array<int>  $ids  Stock ID'leri
     * @return string Mesaj
     */
    public function applyBulkAction(string $action, array $ids): string
    {
        return DB::transaction(function () use ($action, $ids) {
            $stocks = Stock::whereIn('stock_id', $ids);
            $selectedCount = count($ids);

            switch ($action) {
                case 'delete':
                    $stocks->delete();
                    $message = $selectedCount.' hisse senedi başarıyla silindi.';
                    break;
                case 'activate':
                    $stocks->update(['last_status' => 'active']);
                    $message = $selectedCount.' hisse senedi aktif yapıldı.';
                    break;
                case 'deactivate':
                    $stocks->update(['last_status' => 'inactive']);
                    $message = $selectedCount.' hisse senedi pasif yapıldı.';
                    break;
                default:
                    throw new \Exception('Geçersiz toplu işlem: '.$action);
            }

            Log::info('Bulk action applied via StockService', [
                'action' => $action,
                'count' => $selectedCount,
            ]);

            return $message;
        });
    }
}

