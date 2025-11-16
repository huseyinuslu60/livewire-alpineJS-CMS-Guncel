<?php

namespace Modules\Newsletters\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Newsletters\Models\NewsletterTemplate;
use Modules\Newsletters\Services\NewsletterTemplateService;

class TemplateEdit extends Component
{
    public ?\Modules\Newsletters\Models\NewsletterTemplate $template = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $header_html = '';

    public string $content_html = '';

    public string $footer_html = '';

    public string $primary_color = '#1e40af';

    public string $secondary_color = '#3b82f6';

    public string $text_color = '#374151';

    public string $background_color = '#ffffff';

    public bool $is_active = true;

    public int $sort_order = 0;

    protected NewsletterTemplateService $templateService;

    public function boot()
    {
        $this->templateService = app(NewsletterTemplateService::class);
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'description' => 'nullable|string',
        'header_html' => 'required|string',
        'content_html' => 'required|string',
        'footer_html' => 'required|string',
        'primary_color' => 'required|string',
        'secondary_color' => 'required|string',
        'text_color' => 'required|string',
        'background_color' => 'required|string',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    public function mount($id)
    {
        $this->template = NewsletterTemplate::findOrFail($id);
        $this->name = $this->template->name;
        $this->slug = $this->template->slug;
        $this->description = $this->template->description;
        $this->header_html = $this->template->header_html;
        $this->content_html = $this->template->content_html;
        $this->footer_html = $this->template->footer_html;
        $this->primary_color = $this->template->styles['primary_color'] ?? '#1e40af';
        $this->secondary_color = $this->template->styles['secondary_color'] ?? '#3b82f6';
        $this->text_color = $this->template->styles['text_color'] ?? '#374151';
        $this->background_color = $this->template->styles['background_color'] ?? '#ffffff';
        $this->is_active = $this->template->is_active;
        $this->sort_order = $this->template->sort_order;
    }

    public function updatedName()
    {
        $this->slug = Str::slug($this->name);
    }

    // Renk değiştiğinde ön izleme otomatik güncellenecek (computed property sayesinde)

    private function replaceColorsInHtml($html)
    {
        // Placeholder'ları güncelle (tüm placeholder'ları replace et)
        $html = str_replace('{{ primary_color }}', $this->primary_color, $html);
        $html = str_replace('{{ secondary_color }}', $this->secondary_color, $html);
        $html = str_replace('{{ text_color }}', $this->text_color, $html);
        $html = str_replace('{{ background_color }}', $this->background_color, $html);

        return $html;
    }

    // Ön izleme için computed property
    public function getPreviewHeaderHtmlProperty()
    {
        return $this->replaceColorsInHtml($this->header_html);
    }

    public function getPreviewContentHtmlProperty()
    {
        return $this->replaceColorsInHtml($this->content_html);
    }

    public function getPreviewFooterHtmlProperty()
    {
        return $this->replaceColorsInHtml($this->footer_html);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:newsletter_templates,slug,'.$this->template->id,
            'description' => 'nullable|string',
            'header_html' => 'required|string',
            'content_html' => 'required|string',
            'footer_html' => 'required|string',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'header_html' => $this->header_html,
            'content_html' => $this->content_html,
            'footer_html' => $this->footer_html,
            'styles' => [
                'primary_color' => $this->primary_color,
                'secondary_color' => $this->secondary_color,
                'text_color' => $this->text_color,
                'background_color' => $this->background_color,
            ],
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        $this->templateService->update($this->template, $data);

        session()->flash('success', 'Template başarıyla güncellendi!');

        return redirect()->route('newsletters.templates.index');
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'newsletters::livewire.template-edit';

        return view($view)
            ->extends('layouts.admin')->section('content');
    }
}
