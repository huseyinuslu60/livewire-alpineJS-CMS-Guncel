<?php

namespace Modules\AgencyNews\Livewire;

use App\Helpers\LogHelper;
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

    public string $search = '';

    public string $agencyFilter = '';

    public string $categoryFilter = '';

    public string $imageFilter = '';

    public int $perPage = 10;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    protected AgencyNewsService $agencyNewsService;

    public function boot()
    {
        $this->agencyNewsService = app(AgencyNewsService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'agencyFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'imageFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

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
        LogHelper::info('confirmPublishAgencyNews çağrıldı', ['agencyNewsId' => $agencyNewsId]);
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
            \App\Helpers\LogHelper::error('Agency news publish error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');

            return;
        }
    }

    public function render()
    {
        if (! Auth::user()->can('view agency_news')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $query = AgencyNews::query()
            ->search($this->search ?? null)
            ->ofAgency($this->agencyFilter ?? null)
            ->ofCategory($this->categoryFilter ?? null);

        // Resim filtresi - 0-yutmayan kontrol
        if ($this->imageFilter !== '') {
            if ($this->imageFilter === 'yes') {
                $query->where('has_image', true);
            } elseif ($this->imageFilter === 'no') {
                $query->where('has_image', false);
            }
        }

        // Sıralama
        if ($this->sortBy === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $agencyNews = $query->paginate(Pagination::clamp($this->perPage ?? null));

        $agencies = AgencyNews::select('agency_id')
            ->whereNotNull('agency_id')
            ->distinct()
            ->pluck('agency_id')
            ->map(function ($id) {
                return ['id' => $id, 'name' => 'Agency '.$id];
            });

        $categories = AgencyNews::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

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
