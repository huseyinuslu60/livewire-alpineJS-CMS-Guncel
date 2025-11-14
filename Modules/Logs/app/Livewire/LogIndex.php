<?php

namespace Modules\Logs\Livewire;

use App\Support\Pagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Logs\Models\UserLog;

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
            $logs = UserLog::whereIn('log_id', $this->selectedLogs);
            $selectedCount = count($this->selectedLogs);

            switch ($this->bulkAction) {
                case 'delete':
                    $logs->delete();
                    $message = $selectedCount.' log kaydı başarıyla silindi.';
                    break;
                default:
                    return;
            }

            $this->selectedLogs = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('success', $message);
        } catch (\Exception $e) {
            session()->flash('error', 'Toplu işlem sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function deleteLog($id)
    {
        Gate::authorize('delete logs');

        try {
            $log = UserLog::findOrFail($id);
            $log->delete();

            session()->flash('success', 'Log kaydı başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Log kaydı silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function clearAllLogs()
    {
        Gate::authorize('delete logs');

        try {
            UserLog::truncate();
            session()->flash('success', 'Tüm log kayıtları başarıyla silindi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Log kayıtları silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function exportLogs()
    {
        Gate::authorize('export logs');

        try {
            // Aynı filtreleri kullanarak pagination olmadan tüm logları al
            $query = UserLog::query()->with(['user']);

            if ($this->search !== null) {
                $query->search($this->search);
            }

            if ($this->action !== null) {
                $query->ofAction($this->action);
            }

            if ($this->user_id !== null) {
                $query->ofUser($this->user_id);
            }

            if ($this->date_from !== null) {
                $query->whereDate('created_at', '>=', $this->date_from);
            }

            if ($this->date_to !== null) {
                $query->whereDate('created_at', '<=', $this->date_to);
            }

            // Export için chunk() kullan (büyük veri setleri için)
            $hasLogs = false;
            $csvData = "ID,User,Action,Model Type,Model ID,Description,IP Address,User Agent,Created At\n";

            $query->sortedLatest('created_at')->chunk(1000, function ($logs) use (&$csvData, &$hasLogs) {
                $hasLogs = true;
                foreach ($logs as $log) {
                    $csvData .= sprintf(
                        "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                        $log->log_id,
                        $log->user->name ?? 'Sistem',
                        $log->action,
                        $log->model_type ?? '',
                        $log->model_id ?? '',
                        str_replace(',', ';', $log->description ?? ''),
                        $log->ip_address ?? '',
                        str_replace(',', ';', $log->user_agent ?? ''),
                        $log->created_at ?
                            (is_string($log->created_at)
                                ? Carbon::parse($log->created_at)->format('Y-m-d H:i:s')
                                : $log->created_at->format('Y-m-d H:i:s')) : ''
                    );
                }
            });

            // Dışa aktarılacak log var mı kontrol et
            if (! $hasLogs) {
                $this->dispatch('show-error', 'Dışa aktarılacak log kaydı bulunamadı.');

                return;
            }

            // UTF-8 BOM ekle (Excel için)
            $csvData = "\xEF\xBB\xBF".$csvData;

            // Dosya adı oluştur
            $filename = 'logs_export_'.date('Y-m-d_H-i-s').'.csv';

            // JavaScript indirme için veri hazırla
            $this->dispatch('download-csv', data: $csvData, filename: $filename);

        } catch (\Exception $e) {
            $this->dispatch('show-error', 'Log kayıtları dışa aktarılırken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function getLogs()
    {
        $query = UserLog::query()->with(['user']);

        if ($this->search !== null) {
            $query->search($this->search);
        }

        if ($this->action !== null) {
            $query->ofAction($this->action);
        }

        if ($this->user_id !== null) {
            $query->ofUser($this->user_id);
        }

        if ($this->date_from !== null) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to !== null) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        return $query
            ->sortedLatest('created_at')
            ->paginate(Pagination::clamp($this->perPage));
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
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
        ])->extends('layouts.admin')->section('content');
    }
}
