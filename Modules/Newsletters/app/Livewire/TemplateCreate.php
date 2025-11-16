<?php

namespace Modules\Newsletters\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\Newsletters\Models\NewsletterTemplate;

class TemplateCreate extends Component
{
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

    protected $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:newsletter_templates,slug',
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

    // Trumbowyg ile Livewire sync için
    public function updatedHeaderHtml()
    {
        $this->dispatch('trumbowyg-updated', 'header_html', $this->header_html);
    }

    public function updatedContentHtml()
    {
        $this->dispatch('trumbowyg-updated', 'content_html', $this->content_html);
    }

    public function updatedFooterHtml()
    {
        $this->dispatch('trumbowyg-updated', 'footer_html', $this->footer_html);
    }

    public function save()
    {
        $this->validate();

        $template = NewsletterTemplate::create([
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
        ]);

        session()->flash('success', 'Template başarıyla oluşturuldu!');

        return redirect()->route('newsletters.templates.index');
    }

    public function render()
    {
        /** @var view-string $view */
        $view = 'newsletters::livewire.template-create';

        return view($view)
            ->extends('layouts.admin')->section('content');
    }
}
