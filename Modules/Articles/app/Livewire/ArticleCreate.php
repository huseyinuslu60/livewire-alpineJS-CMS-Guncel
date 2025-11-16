<?php

namespace Modules\Articles\Livewire;

use App\Models\User;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Articles\Models\Article;
use Modules\Articles\Services\ArticleService;

class ArticleCreate extends Component
{
    use ValidationMessages;

    public $title = '';

    public $summary = '';

    public $article_text = '';

    public $author_id = '';

    public $status = 'draft';

    public $successMessage = null;

    public $show_on_mainpage = false;

    public $is_commentable = true;

    public $published_at = '';

    public $site_id = 1;

    protected ArticleService $articleService;

    public function boot()
    {
        $this->articleService = app(ArticleService::class);
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'summary' => 'nullable|string|max:1000',
        'article_text' => 'required|string',
        'author_id' => 'required|exists:users,id',
        'status' => 'required|in:draft,published,pending',
        'show_on_mainpage' => 'boolean',
        'is_commentable' => 'boolean',
        'published_at' => 'nullable|date',
        'site_id' => 'nullable|integer',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['article'] ?? $this->getValidationMessages();
    }

    public function mount()
    {
        Gate::authorize('create articles');

        // Eğer yazar rolündeyse, kendi ID'sini kullan
        if (Auth::user()->hasRole('yazar')) {
            $this->author_id = Auth::id();
        } else {
            $this->author_id = Auth::id();
        }
        $this->published_at = now()->format('Y-m-d\TH:i');
    }

    public function store()
    {
        Gate::authorize('create articles');

        try {
            $this->validate();

            $data = [
                'title' => $this->title,
                'summary' => $this->summary,
                'article_text' => $this->article_text,
                'author_id' => $this->author_id,
                'status' => $this->status,
                'show_on_mainpage' => $this->show_on_mainpage,
                'is_commentable' => $this->is_commentable,
                'published_at' => $this->published_at ?: null,
                'site_id' => $this->site_id,
            ];

            $this->articleService->create($data);

            // Toast mesajı göster
            $this->dispatch('article-created');

            // Success mesajını session flash ile göster ve yönlendir
            session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'article'));

            return redirect()->route('articles.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Makale oluşturulurken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Yazar ve editör rolündeki kullanıcıları getir
        $authors = User::select('id', 'name')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['yazar', 'editor']);
            })
            ->get();

        $statuses = [
            'draft' => 'Pasif',
            'published' => 'Aktif',
            'scheduled' => 'Zamanlanmış',
        ];

        /** @var view-string $view */
        $view = 'articles::livewire.article-create';

        return view($view, compact('authors', 'statuses'))
            ->extends('layouts.admin')->section('content');
    }
}
