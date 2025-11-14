<?php

namespace Modules\Banks\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Banks\Models\InvestorQuestion;

class InvestorQuestionAnswer extends Component
{
    public ?\Modules\Banks\Models\InvestorQuestion $question = null;

    public string $answer = '';

    public string $answer_title = '';

    protected $rules = [
        'answer' => 'required|string',
        'answer_title' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'answer.required' => 'Cevap gereklidir.',
        'answer_title.max' => 'Cevap başlığı en fazla 255 karakter olabilir.',
    ];

    public function mount($id)
    {
        $this->question = InvestorQuestion::findOrFail($id);

        if ($this->question->status === 'answered') {
            $this->answer = $this->question->answer;
            $this->answer_title = $this->question->answer_title;
        }
    }

    public function save()
    {
        if (! Auth::user()->can('edit investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $this->validate();

        try {
            if ($this->question->status === 'answered') {
                // Cevabı güncelle
                $this->question->updateAnswer(
                    $this->answer,
                    $this->answer_title,
                    Auth::id()
                );
                session()->flash('success', 'Cevap başarıyla güncellendi.');
            } else {
                // Yeni cevap ekle
                $this->question->markAsAnswered(
                    $this->answer,
                    $this->answer_title,
                    Auth::id()
                );
                session()->flash('success', 'Soru başarıyla cevaplandı.');
            }

            return redirect()->route('banks.investor-questions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'İşlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function reject()
    {
        if (! Auth::user()->can('edit investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $this->question->markAsRejected(Auth::id());

            session()->flash('success', 'Soru reddedildi.');

            return redirect()->route('banks.investor-questions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Soru reddedilirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        if (! Auth::user()->can('edit investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        /** @var view-string $view */
        $view = 'banks::livewire.investor-question-answer';

        return view($view)
            ->extends('layouts.admin')
            ->section('content');
    }
}
