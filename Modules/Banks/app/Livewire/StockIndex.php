<?php

namespace Modules\Banks\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Banks\Models\Stock;
use Modules\Banks\Services\StockService;

class StockIndex extends Component
{
    use InteractsWithToast, WithPagination;

    protected StockService $stockService;

    public ?string $search = null;

    public ?string $status = null;

    public ?int $perPage = null;

    /** @var array<int> */
    public array $selectedStocks = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedStocks = $this->getStocks()->pluck('stock_id')->toArray();
        } else {
            $this->selectedStocks = [];
        }
    }

    public function updatedSelectedStocks()
    {
        $this->selectAll = count($this->selectedStocks) === $this->getStocks()->count();
    }

    public function applyBulkAction()
    {
        if (! Auth::user()->can('edit stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        if (empty($this->selectedStocks) || empty($this->bulkAction)) {
            return;
        }

        try {
            $message = $this->stockService->applyBulkAction($this->bulkAction, $this->selectedStocks);

            $this->selectedStocks = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            $this->toastSuccess($message);
        } catch (\Exception $e) {
            $this->toastError('Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
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
        } catch (\Exception $e) {
            $this->toastError('Hisse senedi silinirken bir hata oluştu: '.$e->getMessage());
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

    public function loadUserColumnPreferences()
    {
        $user = Auth::user();
        $defaultColumns = [
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

        if ($user && $user instanceof \App\Models\User && $user->table_columns) {
            $userColumns = is_array($user->table_columns) ? $user->table_columns : json_decode($user->table_columns, true) ?? [];
            $this->visibleColumns = array_merge($defaultColumns, $userColumns);
        } else {
            $this->visibleColumns = $defaultColumns;
        }
    }

    public function updatedVisibleColumns()
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['table_columns' => $this->visibleColumns]);
        }
    }

    public function render()
    {
        if (! Auth::user()->can('view stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        /** @var view-string $view */
        $view = 'banks::livewire.stock-index';

        return view($view, [
            'stocks' => $this->getStocks(),
        ])->extends('layouts.admin')->section('content');
    }
}
