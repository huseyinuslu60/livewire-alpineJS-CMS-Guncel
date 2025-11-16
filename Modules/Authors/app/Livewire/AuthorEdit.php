<?php

namespace Modules\Authors\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Authors\Models\Author;
use Modules\Authors\Services\AuthorService;

class AuthorEdit extends Component
{
    use ValidationMessages, WithFileUploads;

    public Author $author;

    public ?string $title = null;

    public ?string $bio = null;

    public ?\Illuminate\Http\UploadedFile $image = null;

    public ?string $twitter = null;

    public ?string $linkedin = null;

    public ?string $facebook = null;

    public ?string $instagram = null;

    public ?string $website = null;

    public ?bool $show_on_mainpage = null;

    public ?int $weight = null;

    public ?bool $status = null;

    public ?string $successMessage = null;

    protected AuthorService $authorService;

    public function boot()
    {
        $this->authorService = app(AuthorService::class);
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'bio' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'twitter' => 'nullable|string|max:255',
        'linkedin' => 'nullable|string|max:255',
        'facebook' => 'nullable|string|max:255',
        'instagram' => 'nullable|string|max:255',
        'website' => 'nullable|url|max:255',
        'show_on_mainpage' => 'boolean',
        'weight' => 'integer|min:0',
        'status' => 'boolean',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['author'] ?? $this->getValidationMessages();
    }

    public function mount($author)
    {
        Gate::authorize('edit authors');

        // Eğer $author string ise, Author model'ini bul
        if (is_string($author) || is_numeric($author)) {
            $this->author = Author::findOrFail($author);
        } else {
            $this->author = $author;
        }
        $this->title = $this->author->title;
        $this->bio = $this->author->bio;
        $this->twitter = $this->author->twitter;
        $this->linkedin = $this->author->linkedin;
        $this->facebook = $this->author->facebook;
        $this->instagram = $this->author->instagram;
        $this->website = $this->author->website;
        $this->show_on_mainpage = $this->author->show_on_mainpage;
        $this->weight = $this->author->weight;
        $this->status = (bool) $this->author->status;
    }

    public function save()
    {
        $this->validate();

        $data = $this->only([
            'title', 'bio', 'twitter', 'linkedin',
            'facebook', 'instagram', 'website', 'show_on_mainpage',
            'weight', 'status',
        ]);

        $this->authorService->update($this->author, $data, $this->image);

        $this->dispatch('author-updated');

        // Success mesajını session flash ile göster ve yönlendir
        session()->flash('success', $this->createContextualSuccessMessage('updated', 'title', 'author'));

        return redirect()->route('authors.index');
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'authors::livewire.author-edit';

        return view($view)
            ->extends('layouts.admin')
            ->section('content')
            ->title('Yazar Düzenle - Admin Panel');
    }
}
