<?php

namespace Modules\AgencyNews\Livewire;

use App\Helpers\SystemHelper;
use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AgencyNews\Models\AgencyNews;
use Modules\AgencyNews\Services\AgencyNewsService;

class AgencyNewsIndex extends Component
{
    use WithPagination;

    protected AgencyNewsService $agencyNewsService;

    public string $search = '';

    public string $agencyFilter = '';

    public string $categoryFilter = '';

    public string $imageFilter = '';

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'agencyFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'imageFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function boot(AgencyNewsService $agencyNewsService)
    {
        $this->agencyNewsService = $agencyNewsService;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAgencyFilter()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedImageFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->agencyFilter = '';
        $this->categoryFilter = '';
        $this->imageFilter = '';
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

    public function confirmDeleteAgencyNews($agencyNewsId)
    {
        $this->dispatch('confirm-delete-agency-news', [
            'title' => 'Agency News Sil',
            'message' => 'Bu agency news\'i silmek istediğinizden emin misiniz?',
            'agencyNewsId' => $agencyNewsId,
        ]);
    }

    public function deleteAgencyNews($agencyNewsId)
    {
        if (! Auth::user()->can('delete agency_news')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $agencyNews = AgencyNews::findOrFail($agencyNewsId);
            $this->agencyNewsService->delete($agencyNews);

            session()->flash('success', 'Agency news başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Agency news silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function confirmPublishAgencyNews($agencyNewsId)
    {
        \Log::info('confirmPublishAgencyNews çağrıldı', ['agencyNewsId' => $agencyNewsId]);
        $this->dispatch('confirm-publish-agency-news', [
            'title' => 'Agency News Yayına Al',
            'message' => 'Bu agency news\'i post edit sayfasına yönlendirmek istediğinizden emin misiniz? Son kontrolü yapıp yayınlayabilirsiniz.',
            'agencyNewsId' => $agencyNewsId,
        ]);
    }

    public function publishAgencyNews($agencyNewsId)
    {
        try {
            if (! Auth::user()->can('publish agency_news')) {
                abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
            }

            // Agency news'i bul
            $agencyNews = AgencyNews::find($agencyNewsId);
            if (! $agencyNews) {
                session()->flash('error', 'Agency news bulunamadı.');

                return;
            }

            // Post create sayfasına yönlendir
            return redirect()->route('posts.create.news', ['agency' => $agencyNewsId]);
        } catch (\Exception $e) {
            \Log::error('Agency news publish error: '.$e->getMessage());
            session()->flash('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');

            return;
        }
    }

    public function render()
    {
        if (! Auth::user()->can('view agency_news')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $filters = [
            'search' => $this->search,
            'agencyFilter' => $this->agencyFilter,
            'categoryFilter' => $this->categoryFilter,
            'imageFilter' => $this->imageFilter,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];

        $query = $this->agencyNewsService->getFilteredQuery($filters);
        $agencyNews = $query->paginate(Pagination::clamp($this->perPage ?? null));

        $agencies = $this->agencyNewsService->getAgenciesList();
        $categories = $this->agencyNewsService->getCategoriesList();

        /** @var view-string $view */
        $view = 'agencynews::livewire.agency-news-index';

        return view($view, compact('agencyNews', 'agencies', 'categories'))
            ->extends('layouts.admin')
            ->section('content');
    }

    /**
     * Helper method'ları view'da kullanmak için
     */
    public function getTurkishDate($date)
    {
        return SystemHelper::turkishDate($date);
    }

    public function getTruncatedText($text, $limit = 100)
    {
        return SystemHelper::truncateText($text, $limit);
    }
}
