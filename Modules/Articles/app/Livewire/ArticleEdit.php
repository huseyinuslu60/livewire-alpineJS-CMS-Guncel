<?php

namespace Modules\Articles\Livewire;

use App\Models\User;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Articles\Models\Article;

class ArticleEdit extends Component
{
    use ValidationMessages;

    public $article;

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

    public function mount($article)
    {
        Gate::authorize('edit articles');

        // Eğer $article string ise (ID), model'i bul
        if (is_string($article) || is_numeric($article)) {
            $this->article = Article::findOrFail($article);
        } else {
            $this->article = $article;
        }
        $this->title = $this->article->title;
        $this->summary = $this->article->summary;
        $this->article_text = $this->article->article_text;
        $this->author_id = $this->article->author_id;
        $this->status = $this->article->status;
        $this->show_on_mainpage = $this->article->show_on_mainpage;
        $this->is_commentable = $this->article->is_commentable;
        $this->published_at = $this->article->published_at ? $this->article->published_at->format('Y-m-d\TH:i') : '';
        $this->site_id = $this->article->site_id;
    }

    public function update()
    {
        Gate::authorize('edit articles');

        $this->validate();

        // Eğer durum published ise ve published_at boşsa, şu anki zamanı ata
        if ($this->status === 'published' && empty($this->published_at)) {
            $this->published_at = now();
        }

        $this->article->update([
            'title' => $this->title,
            'summary' => $this->summary,
            'article_text' => $this->article_text,
            'author_id' => $this->author_id,
            'status' => $this->status,
            'show_on_mainpage' => $this->show_on_mainpage,
            'is_commentable' => $this->is_commentable,
            'published_at' => $this->published_at ?: null,
            'site_id' => $this->site_id,
            // Audit fields (updated_by) are handled by AuditFields trait
        ]);

        $this->dispatch('article-updated');

        // Success mesajını session flash ile göster ve yönlendir
        session()->flash('success', $this->createContextualSuccessMessage('updated', 'title', 'article'));

        return redirect()->route('articles.index');
    }

    public function updatedArticleText()
    {
        // TinyMCE'den gelen güncellemeleri işle
        // Livewire 3'te emit yerine dispatch kullanılıyor
        $this->dispatch('article_text_updated', $this->article_text);
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
        $view = 'articles::livewire.article-edit';

        return view($view, compact('authors', 'statuses'))
            ->extends('layouts.admin')->section('content');
    }
}
