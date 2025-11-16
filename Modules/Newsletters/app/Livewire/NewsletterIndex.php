<?php

namespace Modules\Newsletters\Livewire;

use App\Helpers\SystemHelper;
use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Models\Newsletter;
use Modules\Newsletters\Services\NewsletterService;

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
    use InteractsWithModal, InteractsWithToast, HandlesExceptionsWithToast, WithPagination;

    protected NewsletterService $newsletterService;

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

    public function boot(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

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
        $this->confirmModal(
            'Newsletter Sil',
            'Bu newsletter\'i silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deleteNewsletter',
            ['id' => $newsletterId],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
    }

    public function deleteNewsletter($newsletterId)
    {
        if (! Auth::user()->can('delete newsletters')) {
            $this->toastError('Bülten silme yetkiniz bulunmuyor.');

            return;
        }

        try {
            $newsletter = Newsletter::findOrFail($newsletterId);
            $this->newsletterService->delete($newsletter);

            $this->toastSuccess('Newsletter başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Newsletter silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'newsletter_id' => $newsletterId,
            ]);
        }
    }

    public function toggleStatus($newsletterId)
    {
        if (! Auth::user()->can('edit newsletters')) {
            $this->toastError('Bülten düzenleme yetkiniz bulunmuyor.');

            return;
        }

        try {
            $newsletter = Newsletter::findOrFail($newsletterId);
            $this->newsletterService->toggleStatus($newsletter);

            $this->toastSuccess('Newsletter durumu güncellendi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Newsletter durumu güncellenirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'newsletter_id' => $newsletterId,
            ]);
        }
    }

    public function getNewsletters()
    {
        $filters = [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        return $this->newsletterService->getFilteredQuery($filters);
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
