<?php

namespace Modules\Banks\Livewire;

use App\Livewire\Concerns\HasBulkActions;
use App\Livewire\Concerns\HasColumnPreferences;
use App\Livewire\Concerns\HasSearchAndFilters;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Banks\Models\Stock;
use Modules\Banks\Services\StockService;

class StockIndex extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast;
    use HasBulkActions, HasColumnPreferences, HasSearchAndFilters;

    protected StockService $stockService;

    public ?int $perPage = null;

    public ?string $status = null;

    /** @var array<int> */
    public array $selectedStocks = [];

    /** @var array<int> Mevcut sayfadaki görünen stock ID'leri - performans için */
    public array $visibleStockIds = [];

    /** @var array<string, bool> */
    public array $visibleColumns = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function boot(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Get filter properties for HasSearchAndFilters trait
     */
    protected function getFilterProperties(): array
    {
        return ['search', 'status'];
    }

    /**
     * Get selected items property name for HasBulkActions trait
     */
    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedStocks';
    }

    /**
     * Get visible item IDs for HasBulkActions trait
     */
    protected function getVisibleItemIds(): array
    {
        return $this->visibleStockIds;
    }

    /**
     * Get default columns for HasColumnPreferences trait
     */
    protected function getDefaultColumns(): array
    {
        return [
            'checkbox' => true,
            'id' => true,
            'name' => true,
            'unvan' => true,
            'kurulus_tarihi' => true,
            'status' => true,
            'creator' => true,
            'updater' => true,
            'date' => true,
            'actions' => true,
        ];
    }

    /**
     * Handle updated method - combine both traits
     */
    public function updated($propertyName): void
    {
        // Handle search and filters
        if (in_array($propertyName, $this->getFilterProperties())) {
            $this->onFilterUpdated($propertyName);
        }

        // Handle bulk actions
        $selectedPropertyName = $this->getSelectedItemsPropertyName();
        if ($propertyName === $selectedPropertyName) {
            if (! is_array($this->$propertyName)) {
                $this->$propertyName = [];
            }

            $visibleIds = $this->getVisibleItemIds();
            $diff = array_diff($visibleIds, $this->$propertyName);
            $this->selectAll = empty($diff);
        }
    }

    public function applyBulkAction(): void
    {
        if (! Auth::user()->can('edit stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        if (empty($this->selectedStocks) || empty($this->bulkAction)) {
            return;
        }

        try {
            $message = $this->stockService->applyBulkAction($this->bulkAction, $this->selectedStocks);

            $this->clearBulkActionState();

            $this->toastSuccess($message);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Toplu işlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', [
                'selected_ids' => $this->selectedStocks ?? null,
                'bulk_action' => $this->bulkAction ?? null,
            ]);
        }
    }

    public function deleteStock($id)
    {
        if (! Auth::user()->can('delete stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $stock = Stock::findOrFail($id);
            $this->stockService->delete($stock);

            $this->toastSuccess('Hisse senedi başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Hisse senedi silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'stock_id' => $id,
            ]);
        }
    }

    public function getStocks()
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->status,
        ];

        $query = $this->stockService->getFilteredQuery($filters);

        return $query->with(['creator', 'updater'])->paginate(Pagination::clamp($this->perPage));
    }

    public function mount()
    {
        $this->loadUserColumnPreferences();
    }

    public function render()
    {
        if (! Auth::user()->can('view stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        /** @var view-string $view */
        $view = 'banks::livewire.stock-index';

        $stocks = $this->getStocks();

        // Mevcut sayfadaki görünen stock ID'lerini kaydet - performans için
        $this->visibleStockIds = $stocks->pluck('stock_id')->all();

        return view($view, [
            'stocks' => $stocks,
        ])->extends('layouts.admin')->section('content');
    }
}
