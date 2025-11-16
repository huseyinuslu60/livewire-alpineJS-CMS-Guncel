<?php

namespace Modules\Lastminutes\Livewire;

use App\Contracts\SupportsToastErrors;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Lastminutes\Services\LastminuteService;

class LastminuteCreate extends Component implements SupportsToastErrors
{
    use InteractsWithToast, ValidationMessages;

    protected LastminuteService $lastminuteService;

    public string $title = '';

    public string $redirect = '';

    public string $end_at = '';

    public string $status = 'active';

    public int $weight = 0;

    protected $listeners = ['contentUpdated'];

    public function mount()
    {
        Gate::authorize('create lastminutes');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'redirect' => 'nullable|string|max:500',
            'end_at' => 'nullable|date|after:now',
            'status' => 'required|in:active,inactive',
            'weight' => 'required|integer|min:0',
        ];
    }

    protected function messages()
    {
        return $this->getContextualValidationMessages()['lastminute'] ?? $this->getValidationMessages();
    }

    public function boot(LastminuteService $lastminuteService)
    {
        $this->lastminuteService = $lastminuteService;
    }

    public function setQuickTime($minutes)
    {
        $this->end_at = now()->addMinutes($minutes)->format('Y-m-d\TH:i');
    }

    public function saveLastminute()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'redirect' => $this->redirect,
            'end_at' => $this->end_at ?: null,
            'status' => $this->status,
            'weight' => $this->weight,
        ];

        $this->lastminuteService->create($data);

        $this->toastSuccess($this->createContextualSuccessMessage('created', 'title', 'lastminute'), 6000);

        return redirect()->route('lastminutes.index');
    }

    public function render()
    {
        $statusOptions = [
            'active' => 'Aktif',
            'inactive' => 'Pasif',
        ];

        /** @var view-string $view */
        $view = 'lastminutes::livewire.lastminute-create';

        return view($view, compact('statusOptions'))
            ->extends('layouts.admin')
            ->section('content');
    }
}
