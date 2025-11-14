<?php

namespace Modules\Newsletters\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Newsletters\Models\Newsletter;

class NewsletterEdit extends Component
{
    use ValidationMessages;

    public ?\Modules\Newsletters\Models\Newsletter $newsletter = null;

    public string $name = '';

    public string $mail_subject = '';

    public string $mail_body = '';

    public string $mail_body_raw = '';

    public string $status = 'draft';

    public bool $reklam = false;

    public ?string $successMessage = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'mail_subject' => 'required|string|max:255',
        'mail_body' => 'required|string',
        'mail_body_raw' => 'nullable|string',
        'status' => 'required|in:draft,sending,sent,failed',
        'reklam' => 'boolean',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['newsletter'] ?? $this->getValidationMessages();
    }

    public function mount($newsletter)
    {
        if (! Auth::user()->can('edit newsletters')) {
            abort(403, 'Bülten düzenleme yetkiniz bulunmuyor.');
        }

        if (is_string($newsletter) || is_numeric($newsletter)) {
            $this->newsletter = Newsletter::findOrFail($newsletter);
        } else {
            $this->newsletter = $newsletter;
        }

        $this->name = $this->newsletter->name;
        $this->mail_subject = $this->newsletter->mail_subject;
        $this->mail_body = $this->newsletter->mail_body;
        $this->mail_body_raw = $this->newsletter->mail_body_raw;
        $this->status = $this->newsletter->status;
        $this->reklam = $this->newsletter->reklam;
    }

    public function update()
    {
        if (! Auth::user()->can('edit newsletters')) {
            abort(403, 'Bülten düzenleme yetkiniz bulunmuyor.');
        }

        $this->validate();

        $this->newsletter->update([
            'name' => $this->name,
            'mail_subject' => $this->mail_subject,
            'mail_body' => $this->mail_body,
            'mail_body_raw' => $this->mail_body_raw,
            'status' => $this->status,
            'reklam' => $this->reklam,
            // Audit fields (updated_by) are handled by AuditFields trait
        ]);

        $this->dispatch('newsletter-updated');

        session()->flash('success', $this->createContextualSuccessMessage('updated', 'name', 'newsletter'));

        return redirect()->route('newsletters.index');
    }

    public function updatedMailBody()
    {
        $this->dispatch('mail_body_updated', $this->mail_body);
    }

    public function render()
    {
        $statuses = [
            'draft' => 'Taslak',
            'sending' => 'Gönderiliyor',
            'sent' => 'Gönderildi',
            'failed' => 'Başarısız',
        ];

        /** @var view-string $view */
        $view = 'newsletters::livewire.newsletter-edit';

        return view($view, compact('statuses'))
            ->extends('layouts.admin')->section('content');
    }
}
