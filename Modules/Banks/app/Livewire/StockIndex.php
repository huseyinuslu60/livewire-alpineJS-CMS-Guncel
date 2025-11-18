<?php

namespace Modules\Banks\Livewire;

use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Banks\Services\StockService;

class StockIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $status = null;

    public ?int $perPage = null;

    /** @var array<int> */
    public array $selectedStocks = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    /** @var array<string, bool> */
    public array $visibleColumns = [];

    protected StockService $stockService;

    public function boot()
    {
        $this->stockService = app(StockService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

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
            $stocks = $this->stockService->getQuery()->whereIn('stock_id', $this->selectedStocks);
            $selectedCount = count($this->selectedStocks);

            switch ($this->bulkAction) {
                case 'delete':
                    $this->stockService->bulkDelete($this->selectedStocks);
                    $message = $selectedCount.' hisse senedi başarıyla silindi.';
                    break;
                case 'activate':
                    $this->stockService->bulkUpdateStatus($this->selectedStocks, 'active');
                    $message = $selectedCount.' hisse senedi aktif yapıldı.';
                    break;
                case 'deactivate':
                    $this->stockService->bulkUpdateStatus($this->selectedStocks, 'inactive');
                    $message = $selectedCount.' hisse senedi pasif yapıldı.';
                    break;
                default:
                    return;
            }

            $this->selectedStocks = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('success', $message);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deleteStock($id)
    {
        if (! Auth::user()->can('delete stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $stock = $this->stockService->findById($id);
            $this->stockService->delete($stock);

            session()->flash('success', 'Hisse senedi başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Hisse senedi silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getStocks()
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Banks\Models\Stock> $query */
        $query = $this->stockService->getQuery()
            ->with(['creator', 'updater'])
            ->search($this->search ?? null)
            ->ofStatus($this->status ?? null)
            ->sortedLatest('created_at');

        return $query
            ->paginate(Pagination::clamp($this->perPage));
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
