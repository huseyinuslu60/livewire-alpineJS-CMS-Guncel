<?php

namespace Modules\Authors\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Authors\Services\AuthorService;
use Modules\User\Services\UserService;

class AuthorCreate extends Component
{
    use ValidationMessages, WithFileUploads;

    public ?int $user_id = null;

    public ?string $title = null;

    public ?string $bio = null;

    public ?\Illuminate\Http\UploadedFile $image = null;

    public ?string $twitter = null;

    public ?string $linkedin = null;

    public ?string $facebook = null;

    public ?string $instagram = null;

    public ?string $website = null;

    public bool $show_on_mainpage = false;

    public int $weight = 0;

    public bool $status = true;

    public ?string $successMessage = null;

    protected AuthorService $authorService;

    protected UserService $userService;

    public function boot()
    {
        $this->authorService = app(AuthorService::class);
        $this->userService = app(UserService::class);
    }

    protected $rules = [
        'user_id' => 'required|exists:users,id|unique:authors,user_id',
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

    public function mount()
    {
        Gate::authorize('create authors');
    }

    public function save()
    {
        $this->validate();

        $data = $this->only([
            'user_id', 'title', 'bio', 'twitter', 'linkedin',
            'facebook', 'instagram', 'website', 'show_on_mainpage',
            'weight', 'status',
        ]);

        $this->authorService->create($data, $this->image);

        $this->dispatch('author-created');

        // Success mesajını session flash ile göster ve yönlendir
        session()->flash('success', $this->createContextualSuccessMessage('created', 'title', 'author'));

        return redirect()->route('authors.index');
    }

    public function render()
    {
        $users = $this->userService->getQuery()
            ->whereDoesntHave('authorProfile')
            ->whereRelation('roles', 'name', 'yazar')
            ->get();

        /** @var view-string $view */
        $view = 'authors::livewire.author-create';

        return view($view, compact('users'))
            ->extends('layouts.admin')
            ->section('content')
            ->title('Yeni Yazar Ekle - Admin Panel');
    }
}
