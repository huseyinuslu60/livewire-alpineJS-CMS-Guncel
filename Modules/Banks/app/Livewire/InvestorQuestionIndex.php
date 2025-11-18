<?php

namespace Modules\Banks\Livewire;

use App\Support\Pagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Banks\Domain\ValueObjects\InvestorQuestionStatus;
use Modules\Banks\Services\InvestorQuestionService;

class InvestorQuestionIndex extends Component
{
    use WithPagination;

    protected InvestorQuestionService $investorQuestionService;

    public function boot()
    {
        $this->investorQuestionService = app(InvestorQuestionService::class);
    }

    public ?string $search = null;

    public ?string $status = null;

    public ?int $perPage = null;

    /** @var array<int> */
    public array $selectedQuestions = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    /** @var array<string, bool> */
    public array $visibleColumns = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedQuestions = $this->getQuestions()->pluck('question_id')->toArray();
        } else {
            $this->selectedQuestions = [];
        }
    }

    public function updatedSelectedQuestions()
    {
        $this->selectAll = count($this->selectedQuestions) === $this->getQuestions()->count();
    }

    public function applyBulkAction()
    {
        if (! Auth::user()->can('edit investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        if (empty($this->selectedQuestions) || empty($this->bulkAction)) {
            return;
        }

        try {
            $selectedCount = count($this->selectedQuestions);

            switch ($this->bulkAction) {
                case 'delete':
                    $this->investorQuestionService->bulkDelete($this->selectedQuestions);
                    $message = $selectedCount.' soru başarıyla silindi.';
                    break;
                case 'reject':
                    $this->investorQuestionService->bulkUpdateStatus($this->selectedQuestions, InvestorQuestionStatus::rejected()->toString());
                    $message = $selectedCount.' soru reddedildi.';
                    break;
                default:
                    return;
            }

            $this->selectedQuestions = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('success', $message);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function rejectQuestion($id)
    {
        if (! Auth::user()->can('edit investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $question = $this->investorQuestionService->findById($id);
            $this->investorQuestionService->markAsRejected($question, Auth::id());

            session()->flash('success', 'Soru başarıyla reddedildi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Soru reddedilirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deleteQuestion($id)
    {
        if (! Auth::user()->can('delete investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $question = $this->investorQuestionService->findById($id);
            $this->investorQuestionService->delete($question);

            session()->flash('success', 'Soru başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Soru silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getQuestions()
    {
        /** @var \Illuminate\Database\Eloquent\Builder<\Modules\Banks\Models\InvestorQuestion> $query */
        $query = $this->investorQuestionService->getQuery()
            ->with(['updater'])
            ->search($this->search ?? null)
            ->ofStatus($this->status ?? null)
            ->sortedLatest('created_at');

        return $query->paginate(Pagination::clamp($this->perPage));
    }

    public function mount()
    {
        $this->loadUserColumnPreferences();
    }

    public function loadUserColumnPreferences()
    {
        $user = Auth::user();
        $defaultColumns = [
            'checkbox' => true,
            'id' => true,
            'title' => true,
            'name' => true,
            'email' => true,
            'stock' => true,
            'status' => true,
            'hit' => true,
            'updater' => true,
            'date' => true,
            'actions' => true,
        ];

        if ($user && $user instanceof \App\Models\User && $user->table_columns) {
            $userColumns = is_array($user->table_columns) ? $user->table_columns : json_decode($user->table_columns, true) ?? [];
            $this->visibleColumns = array_merge($defaultColumns, $userColumns);
        } else {
            $this->visibleColumns = $defaultColumns;
        }
    }

    public function updatedVisibleColumns()
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['table_columns' => $this->visibleColumns]);
        }
    }

    public function render()
    {
        if (! Auth::user()->can('view investor_questions')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        /** @var view-string $view */
        $view = 'banks::livewire.investor-question-index';

        return view($view, [
            'questions' => $this->getQuestions(),
            'statusLabels' => InvestorQuestionStatus::labels(),
        ])->extends('layouts.admin')->section('content');
    }
}
