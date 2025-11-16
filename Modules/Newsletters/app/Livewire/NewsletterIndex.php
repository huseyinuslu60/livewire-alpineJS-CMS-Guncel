<?php

namespace Modules\Newsletters\Livewire;

use App\Helpers\SystemHelper;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Models\Newsletter;

/**
 * @property string|null $search
 * @property string|null $statusFilter
 * @property int $perPage
 * @property string $sortBy
 * @property string $sortDirection
 * @property array<int> $selectedNewsletters
 * @property bool $selectAll
 * @property string $bulkAction
 */
class NewsletterIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $statusFilter = null;

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /** @var array<int> */
    public array $selectedNewsletters = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedNewsletters = $this->getNewsletters()->pluck('newsletter_id')->toArray();
        } else {
            $this->selectedNewsletters = [];
        }
    }

    public function updatedSelectedNewsletters()
    {
        $this->selectAll = count($this->selectedNewsletters) === $this->getNewsletters()->count();
    }

    public function clearFilters()
    {
        $this->search = null;
        $this->statusFilter = null;
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

    public function confirmDeleteNewsletter($newsletterId)
    {
        $this->dispatch('confirm-delete-newsletter', [
            'title' => 'Newsletter Sil',
            'message' => 'Bu newsletter\'i silmek istediğinizden emin misiniz?',
            'newsletterId' => $newsletterId,
        ]);
    }

    public function deleteNewsletter($newsletterId)
    {
        if (! Auth::user()->can('delete newsletters')) {
            abort(403, 'Bülten silme yetkiniz bulunmuyor.');
        }

        try {
            $newsletter = Newsletter::findOrFail($newsletterId);
            $newsletter->delete();

            session()->flash('success', 'Newsletter başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Newsletter silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleStatus($newsletterId)
    {
        if (! Auth::user()->can('edit newsletters')) {
            abort(403, 'Bülten düzenleme yetkiniz bulunmuyor.');
        }

        try {
            $newsletter = Newsletter::findOrFail($newsletterId);

            $newStatus = match ($newsletter->status) {
                'draft' => 'sending',
                'sending' => 'sent',
                'sent' => 'draft',
                'failed' => 'draft',
                default => 'draft'
            };

            $newsletter->update(['status' => $newStatus]);

            session()->flash('success', 'Newsletter durumu güncellendi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Newsletter durumu güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getNewsletters()
    {
        $query = Newsletter::with(['creator', 'updater']);

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->statusFilter !== null) {
            $query->ofStatus($this->statusFilter);
        }

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
        if (! Auth::user()->can('view newsletters')) {
            abort(403, 'Bülten görüntüleme yetkiniz bulunmuyor.');
        }

        $newsletters = $this->getNewsletters()->paginate(Pagination::clamp($this->perPage));

        $statuses = [
            'draft' => 'Taslak',
            'sending' => 'Gönderiliyor',
            'sent' => 'Gönderildi',
            'failed' => 'Başarısız',
        ];

        /** @var view-string $view */
        $view = 'newsletters::livewire.newsletter-index';

        return view($view, compact('newsletters', 'statuses'))
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
