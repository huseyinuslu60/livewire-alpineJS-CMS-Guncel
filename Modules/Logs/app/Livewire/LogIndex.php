<?php

namespace Modules\Logs\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Logs\Models\UserLog;
use Modules\Logs\Services\LogService;
use Modules\User\Services\UserService;

/**
 * @property string|null $search
 * @property string|null $action
 * @property string|null $user_id
 * @property string|null $date_from
 * @property string|null $date_to
 * @property int $perPage
 * @property array<int> $selectedLogs
 * @property bool $selectAll
 * @property string $bulkAction
 */
class LogIndex extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $action = null;

    public ?string $user_id = null;

    public ?string $date_from = null;

    public ?string $date_to = null;

    public int $perPage = 15;

    /** @var array<int> */
    public array $selectedLogs = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    protected LogService $logService;

    protected UserService $userService;

    public function boot()
    {
        $this->logService = app(LogService::class);
        $this->userService = app(UserService::class);
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'action' => ['except' => ''],
        'user_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function mount()
    {
        Gate::authorize('view logs');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedLogs = $this->getLogs()->pluck('log_id')->toArray();
        } else {
            $this->selectedLogs = [];
        }
    }

    public function updatedSelectedLogs()
    {
        if (! is_array($this->selectedLogs)) {
            $this->selectedLogs = [];
        }
        $this->selectAll = count($this->selectedLogs) === $this->getLogs()->count();
    }

    public function applyBulkAction()
    {
        Gate::authorize('delete logs');

        if (empty($this->selectedLogs) || empty($this->bulkAction) || ! is_array($this->selectedLogs)) {
            return;
        }

        try {
            $selectedCount = count($this->selectedLogs);

            switch ($this->bulkAction) {
                case 'delete':
                    $deletedCount = $this->logService->deleteBulk($this->selectedLogs);
                    $message = $deletedCount.' log kaydı başarıyla silindi.';
                    break;
                default:
                    return;
            }

            $this->selectedLogs = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('success', $message);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deleteLog($id)
    {
        Gate::authorize('delete logs');

        try {
            $this->logService->delete($id);
            session()->flash('success', 'Log kaydı başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Log kaydı silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function clearAllLogs()
    {
        Gate::authorize('delete logs');

        try {
            $this->logService->clearAll();
            session()->flash('success', 'Tüm log kayıtları başarıyla silindi.');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Log kayıtları silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function exportLogs()
    {
        Gate::authorize('export logs');

        try {
            $filters = [
                'search' => $this->search,
                'action' => $this->action,
                'user_id' => $this->user_id,
                'date_from' => $this->date_from,
                'date_to' => $this->date_to,
            ];

            $export = $this->logService->exportToCsv($filters);

            // JavaScript indirme için veri hazırla
            $this->dispatch('download-csv', data: $export['data'], filename: $export['filename']);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('show-error', $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Log kayıtları dışa aktarılırken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getLogs()
    {
        $filters = [
            'search' => $this->search,
            'action' => $this->action,
            'user_id' => $this->user_id,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];

        return $this->logService->getFilteredLogs($filters, $this->perPage);
    }

    public function render()
    {
        // Modül aktiflik kontrolü
        if (! \App\Helpers\SystemHelper::isModuleActive('logs')) {
            abort(404, 'Logs modülü aktif değil.');
        }

        /** @var view-string $view */
        $view = 'logs::livewire.log-index';

        return view($view, [
            'logs' => $this->getLogs(),
            'actions' => UserLog::ACTIONS,
            'users' => $this->userService->getQuery()->select('id', 'name')->orderBy('name')->get(),
        ])->extends('layouts.admin')->section('content');
    }
}
