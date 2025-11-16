<?php

namespace Modules\Lastminutes\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Lastminutes\Models\Lastminute;

class LastminuteEdit extends Component
{
    use ValidationMessages;

    public Lastminute $lastminute;

    public string $title = '';

    public string $redirect = '';

    public string $end_at = '';

    public string $status = 'active';

    public int $weight = 0;

    protected $listeners = ['contentUpdated'];

    public function mount(Lastminute $lastminute)
    {
        Gate::authorize('edit lastminutes');

        $this->lastminute = $lastminute;
        $this->title = $lastminute->title;
        $this->redirect = $lastminute->redirect;
        $this->end_at = $lastminute->end_at ? \Carbon\Carbon::parse($lastminute->end_at)->format('Y-m-d\TH:i') : '';
        $this->status = $lastminute->status;
        $this->weight = $lastminute->weight;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'redirect' => 'nullable|string|max:500',
            'end_at' => 'nullable|date|after:now',
            'status' => 'required|in:active,inactive,expired',
            'weight' => 'required|integer|min:0',
        ];
    }

    protected function messages()
    {
        return $this->getContextualValidationMessages()['lastminute'] ?? $this->getValidationMessages();
    }

    public function setQuickTime($minutes)
    {
        $this->end_at = now()->addMinutes($minutes)->format('Y-m-d\TH:i');
    }

    public function updateLastminute()
    {
        $this->validate();

        // Audit fields (updated_by) are handled by AuditFields trait
        $this->lastminute->update([
            'title' => $this->title,
            'redirect' => $this->redirect,
            'end_at' => $this->end_at ?: null,
            'status' => $this->status,
            'weight' => $this->weight,
        ]);

        $this->dispatch('lastminute-updated');

        $successMessage = $this->createContextualSuccessMessage('updated', 'title', 'lastminute');
        session()->flash('success', $successMessage);

        return redirect()->route('lastminutes.index');
    }

    public function deleteLastminute()
    {
        Gate::authorize('delete lastminutes');

        // Soft delete (deleted_by is handled by AuditFields trait)
        $this->lastminute->delete();

        $this->dispatch('lastminute-deleted');

        $successMessage = $this->createContextualSuccessMessage('deleted', 'title', 'lastminute');
        session()->flash('success', $successMessage);

        return redirect()->route('lastminutes.index');
    }

    public function render()
    {
        $statusOptions = [
            'active' => 'Aktif',
            'inactive' => 'Pasif',
            'expired' => 'Süresi Dolmuş',
        ];

        /** @var view-string $view */
        $view = 'lastminutes::livewire.lastminute-edit';

        return view($view, compact('statusOptions'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
