<?php

namespace Modules\Newsletters\Livewire;

use App\Support\Pagination;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Models\NewsletterTemplate;

class TemplateIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive($templateId)
    {
        $template = NewsletterTemplate::find($templateId);
        if ($template) {
            $template->update(['is_active' => ! $template->is_active]);
            session()->flash('success', 'Template durumu güncellendi!');
        }
    }

    public function deleteTemplate($templateId)
    {
        $template = NewsletterTemplate::find($templateId);
        if ($template) {
            $template->delete();
            session()->flash('success', 'Template başarıyla silindi!');
        }
    }

    public function render()
    {
        $query = NewsletterTemplate::query()
            ->search($this->search ?? null);

        // Sorting: Referans modül kalıbına göre
        if ($this->sortField === 'created_at' && $this->sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $templates = $query->paginate(Pagination::clamp($this->perPage ?? null));

        /** @var view-string $view */
        $view = 'newsletters::livewire.template-index';

        return view($view, [
            'templates' => $templates,
        ])->extends('layouts.admin')->section('content');
    }
}
