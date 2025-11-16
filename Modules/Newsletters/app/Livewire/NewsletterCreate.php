<?php

namespace Modules\Newsletters\Livewire;

use App\Support\Pagination;
use App\Traits\ValidationMessages;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Newsletters\Models\Newsletter;
use Modules\Newsletters\Models\NewsletterTemplate;
use Modules\Posts\Models\Post;

class NewsletterCreate extends Component
{
    use ValidationMessages, WithPagination;

    public string $name = '';

    public string $mail_subject = '';

    public string $mail_body = '';

    public string $status = 'draft';

    public bool $reklam = false;

    public ?string $successMessage = null;

    // Newsletter builder properties
    // Note: availablePosts is a computed property (getAvailablePostsProperty), not a real property
    // Removed public $availablePosts = [] to avoid conflict with computed property

    /** @var array<int> */
    public array $selectedPosts = [];

    public bool $showPreview = false;

    public string $searchQuery = '';

    public int $perPage = 15;

    // Template selection properties
    public int $selectedTemplate = 1;

    /** @var \Illuminate\Support\Collection<int, \Modules\Newsletters\Models\NewsletterTemplate> */
    public \Illuminate\Support\Collection $availableTemplates;

    protected $rules = [
        'name' => 'required|string|max:255',
        'mail_subject' => 'required|string|max:255',
        'mail_body' => 'required|string',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['newsletter'] ?? $this->getValidationMessages();
    }

    public function mount()
    {
        $this->availableTemplates = collect();
        $this->loadAvailableTemplates();
        $this->updateNewsletterBody(); // İlk yüklemede template'i göster
    }

    public function getAvailablePostsProperty()
    {
        $query = Post::query()
            ->where('in_newsletter', true)
            ->ofStatus('published')
            ->where('published_date', '>=', now()->subDays(7))
            ->with(['primaryFile', 'author'])
            ->searchForNewsletter($this->searchQuery ?? null);

        return $query->latest('published_date')
            ->paginate(Pagination::clamp($this->perPage ?? null, 5, 50, 15), ['*'], 'postsPage');
    }

    public function loadAvailableTemplates()
    {
        $this->availableTemplates = NewsletterTemplate::active()->ordered()->get();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage('postsPage'); // Reset to first page when searching
    }

    public function selectTemplate($templateId)
    {
        $this->selectedTemplate = $templateId;
        $this->updateNewsletterBody();
        $this->dispatch('$refresh');
        session()->flash('success', 'Template değiştirildi!');
    }

    public function addPostToNewsletter($postId)
    {
        $post = Post::find($postId);
        if ($post && ! in_array($postId, $this->selectedPosts)) {
            $this->selectedPosts[] = $postId;
            $this->updateNewsletterBody();
            session()->flash('success', 'Haber bültene eklendi!');

            // Dispatch event to trigger sortable initialization
            $this->dispatch('post-added');
        }
    }

    public function removePostFromNewsletter($postId)
    {
        $this->selectedPosts = array_values(array_filter($this->selectedPosts, function ($id) use ($postId) {
            return $id != $postId;
        }));
        $this->updateNewsletterBody();
        session()->flash('success', 'Haber bültenden çıkarıldı!');
    }

    public function updateNewsletterBody()
    {
        if (empty($this->selectedPosts)) {
            // Template'i boş haberlerle göster
            $this->mail_body = $this->generateNewsletterTemplate(collect(), $this->selectedTemplate);

            return;
        }

        $posts = Post::whereIn('post_id', $this->selectedPosts)
            ->with(['primaryFile', 'author'])
            ->get()
            ->keyBy('post_id');

        $orderedPosts = collect($this->selectedPosts)->map(function ($postId) use ($posts) {
            return $posts->get($postId);
        })->filter();

        $this->mail_body = $this->generateNewsletterTemplate($orderedPosts, $this->selectedTemplate);
    }

    public function generateNewsletterTemplate($posts, $templateId = 1)
    {
        $template = NewsletterTemplate::find($templateId);
        if (! $template) {
            $template = NewsletterTemplate::first();
        }

        // Eğer hala template yoksa, varsayılan template oluştur
        if (! $template) {
            $template = (object) [
                'header_html' => '<div style="text-align: center; padding: 20px; background: #f8f9fa;"><h1>Bülten Başlığı</h1></div>',
                'content_html' => '<div style="padding: 20px;">{{ $newsletterContent }}</div>',
                'footer_html' => '<div style="text-align: center; padding: 20px; background: #f8f9fa;"><p>Bülten Footer</p></div>',
            ];
        }

        // Get site logo
        $siteLogo = \Modules\Settings\Models\SiteSetting::where('key', 'site_logo')->first();
        $logoPath = $siteLogo ? $siteLogo->value : '/images/logo.png';
        $logoUrl = asset('storage/'.$logoPath);

        // Get user info
        $userName = auth()->user()->name ?? 'Değerli Abonemiz';
        $userEmail = auth()->user()->email ?? 'user@example.com';
        $siteUrl = config('app.url');

        // Generate newsletter content
        $newsletterContent = $this->generateNewsletterContent($posts);

        // Get template styles
        $styles = $template->styles ?? [];
        $primaryColor = $styles['primary_color'] ?? '#1e40af';
        $secondaryColor = $styles['secondary_color'] ?? '#3b82f6';
        $textColor = $styles['text_color'] ?? '#374151';
        $backgroundColor = $styles['background_color'] ?? '#ffffff';

        // Replace template variables and colors
        $headerHtml = str_replace(
            ['{{ $siteLogo }}', '{{ $userName }}', '{{ $siteUrl }}', '#isim#', '#tarih#', '{{ primary_color }}', '{{ secondary_color }}', '{{ text_color }}', '{{ background_color }}'],
            [$logoUrl, $userName, $siteUrl, $userName, now()->format('d.m.Y'), $primaryColor, $secondaryColor, $textColor, $backgroundColor],
            $template->header_html
        );

        $contentHtml = str_replace(
            ['{{ $newsletterContent }}', '{{ primary_color }}', '{{ secondary_color }}', '{{ text_color }}', '{{ background_color }}'],
            [$newsletterContent, $primaryColor, $secondaryColor, $textColor, $backgroundColor],
            $template->content_html
        );

        $footerHtml = str_replace(
            ['{{ $userEmail }}', '#mail#', '#isim#', '{{ primary_color }}', '{{ secondary_color }}', '{{ text_color }}', '{{ background_color }}'],
            [$userEmail, $userEmail, $userName, $primaryColor, $secondaryColor, $textColor, $backgroundColor],
            $template->footer_html
        );

        return $headerHtml.$contentHtml.$footerHtml;
    }

    public function generateNewsletterContent($posts)
    {
        $content = '';

        foreach ($posts as $post) {
            $imageHtml = '';
            if ($post->primaryFile) {
                $imagePath = $post->primaryFile->file_path;
                if (strpos($imagePath, 'http') === 0) {
                    $imageUrl = $imagePath;
                } elseif (strpos($imagePath, 'storage/') === 0) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/'.$imagePath);
                }
                $imageHtml = '<img src="'.$imageUrl.'" alt="'.$post->title.'" style="width: 100%; max-width: 300px; height: auto; margin-bottom: 10px; border-radius: 8px;" onerror="this.style.display=\'none\';">';
            }

            // Get post URL (frontend URL)
            $postUrl = config('app.url').'/haber/'.$post->slug;

            $content .= '
                <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 24px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 0 0 120px;">
                            <a href="'.$postUrl.'" style="text-decoration: none;">
                                '.$imageHtml.'
                            </a>
                        </div>
                        <div style="flex: 1;">
                            <a href="'.$postUrl.'" style="text-decoration: none;">
                                <h3 style="color: #dc2626; font-size: 20px; font-weight: bold; margin: 0 0 12px 0; line-height: 1.3;">'.$post->title.'</h3>
                            </a>
                            <p style="color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 12px 0;">'.$post->summary.'</p>
                            <div style="text-align: right;">
                                <a href="'.$postUrl.'" style="color: #3b82f6; text-decoration: none; font-size: 14px; font-weight: 500;">Devamını Oku...</a>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        }

        return $content;
    }

    public function reorderPosts($orderedIds = [], $action = null, $index = null)
    {
        if ($action === 'moveUp' && $index > 0) {
            // Move item up
            $temp = $this->selectedPosts[$index];
            $this->selectedPosts[$index] = $this->selectedPosts[$index - 1];
            $this->selectedPosts[$index - 1] = $temp;

            $this->updateNewsletterBody();
            session()->flash('success', 'Haber yukarı taşındı!');
        } elseif ($action === 'moveDown' && $index < count($this->selectedPosts) - 1) {
            // Move item down
            $temp = $this->selectedPosts[$index];
            $this->selectedPosts[$index] = $this->selectedPosts[$index + 1];
            $this->selectedPosts[$index + 1] = $temp;

            $this->updateNewsletterBody();
            session()->flash('success', 'Haber aşağı taşındı!');
        } elseif (! empty($orderedIds)) {
            // Use provided order (for sortable drag & drop)
            $this->selectedPosts = $orderedIds;
            $this->updateNewsletterBody();
            session()->flash('success', 'Haberlerin sırası güncellendi!');
        }

        // Force refresh to update the view
        $this->dispatch('$refresh');
    }

    protected $listeners = [
        'reorderPosts' => 'handleReorderPosts',
    ];

    public function handleReorderPosts($orderedIds)
    {
        \Log::info('handleReorderPosts called with:', $orderedIds);

        if (! empty($orderedIds)) {
            $this->selectedPosts = $orderedIds;
            $this->updateNewsletterBody();
            session()->flash('success', 'Haberlerin sırası güncellendi!');
            \Log::info('Posts reordered successfully:', $this->selectedPosts);
        }
    }

    public function store()
    {
        $this->validate();

        $newsletter = Newsletter::create([
            'name' => $this->name,
            'mail_subject' => $this->mail_subject,
            'mail_body' => $this->mail_body,
            'status' => $this->status,
            'reklam' => $this->reklam,
            // Audit fields (created_by, updated_by) are handled by AuditFields trait
        ]);

        session()->flash('success', 'Newsletter başarıyla oluşturuldu!');

        return redirect()->route('newsletters.index');
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'newsletters::livewire.newsletter-create';

        return view($view)
            ->extends('layouts.admin')->section('content');
    }
}
