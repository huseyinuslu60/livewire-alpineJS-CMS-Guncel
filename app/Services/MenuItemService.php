<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MenuItemService
{
    /**
     * Find menu item by ID
     */
    public function findById(int $itemId): ?MenuItem
    {
        return MenuItem::find($itemId);
    }

    /**
     * Create a new menu item
     */
    public function create(array $data): MenuItem
    {
        try {
            return DB::transaction(function () use ($data) {
                $item = MenuItem::create($data);

                LogHelper::info('Menü öğesi oluşturuldu', [
                    'item_id' => $item->id,
                    'name' => $item->name,
                ]);

                return $item;
            });
        } catch (\Exception $e) {
            LogHelper::error('MenuItemService create error', [
                'name' => $data['name'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing menu item
     */
    public function update(MenuItem $item, array $data): MenuItem
    {
        try {
            return DB::transaction(function () use ($item, $data) {
                $item->update($data);

                LogHelper::info('Menü öğesi güncellendi', [
                    'item_id' => $item->id,
                    'name' => $item->name,
                ]);

                return $item->fresh();
            });
        } catch (\Exception $e) {
            LogHelper::error('MenuItemService update error', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a menu item
     */
    public function delete(MenuItem $item): bool
    {
        try {
            return DB::transaction(function () use ($item) {
                $itemId = $item->id;
                $name = $item->name;

                $result = $item->delete();

                LogHelper::info('Menü öğesi silindi', [
                    'item_id' => $itemId,
                    'name' => $name,
                ]);

                return $result;
            });
        } catch (\Exception $e) {
            LogHelper::error('MenuItemService delete error', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get query builder for menu items
     */
    public function getQuery(): Builder
    {
        return MenuItem::query();
    }

    /**
     * Get root menu items with children
     */
    public function getRootItemsWithChildren(): \Illuminate\Database\Eloquent\Collection
    {
        return MenuItem::with('parent', 'children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Bulk update menu items
     */
    public function bulkUpdate(array $items): int
    {
        try {
            return DB::transaction(function () use ($items) {
                $count = 0;
                foreach ($items as $itemData) {
                    if (isset($itemData['id'])) {
                        $item = MenuItem::find($itemData['id']);
                        if ($item) {
                            $item->update($itemData);
                            $count++;
                        }
                    }
                }

                LogHelper::info('Menü öğeleri toplu güncellendi', [
                    'count' => $count,
                ]);

                return $count;
            });
        } catch (\Exception $e) {
            LogHelper::error('MenuItemService bulkUpdate error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
