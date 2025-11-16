<?php

namespace Modules\Newsletters\Livewire;

use App\Helpers\SystemHelper;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Models\NewsletterLog;

class NewsletterLogIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $typeFilter = null;

    public ?string $statusFilter = null;

    public ?int $perPage = null;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /** @var array<int> */
    public array $selectedLogs = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedLogs = $this->getLogs()->pluck('record_id')->toArray();
        } else {
            $this->selectedLogs = [];
        }
    }

    public function updatedSelectedLogs()
    {
        $this->selectAll = count($this->selectedLogs) === $this->getLogs()->count();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDeleteLog($logId)
    {
        $this->dispatch('confirm-delete-log', [
            'title' => 'Log Sil',
            'message' => 'Bu log kaydını silmek istediğinizden emin misiniz?',
            'logId' => $logId,
        ]);
    }

    public function deleteLog($logId)
    {
        if (! Auth::user()->can('delete newsletter_logs')) {
            abort(403, 'Bülten log silme yetkiniz bulunmuyor.');
        }

        try {
            $log = NewsletterLog::findOrFail($logId);
            $log->delete();

            session()->flash('success', 'Log kaydı başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Log kaydı silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getLogs()
    {
        $query = NewsletterLog::with(['newsletter', 'user'])
            ->search($this->search ?? null)
            ->ofType($this->typeFilter ?? null)
            ->ofStatus($this->statusFilter ?? null);

        // Sorting: Referans modül kalıbına göre
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        if (! Auth::user()->can('view newsletter_logs')) {
            abort(403, 'Bülten log görüntüleme yetkiniz bulunmuyor.');
        }

        $logs = $this->getLogs()->paginate(Pagination::clamp($this->perPage));

        $types = [
            'click' => 'Tıklama',
            'open' => 'Açma',
            'bounce' => 'Bounce',
            'unsubscribe' => 'Abonelikten Çıkma',
        ];

        $statuses = [
            'success' => 'Başarılı',
            'failed' => 'Başarısız',
        ];

        /** @var view-string $view */
        $view = 'newsletters::livewire.newsletter-log-index';

        return view($view, compact('logs', 'types', 'statuses'))
            ->extends('layouts.admin')->section('content');
    }

    public function getTurkishDate($date)
    {
        return SystemHelper::turkishDate($date);
    }

    public function getStatusBadge($status)
    {
        return SystemHelper::statusBadge($status);
    }

    public function getTruncatedText($text, $limit = 100)
    {
        return SystemHelper::truncateText($text, $limit);
    }
}
