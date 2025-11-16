<?php

namespace Modules\Logs\Livewire;

use App\Livewire\Concerns\HasBulkActions;
use App\Livewire\Concerns\HasSearchAndFilters;
use App\Livewire\Concerns\InteractsWithToast;
use App\Support\Pagination;
use App\Traits\HandlesExceptionsWithToast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Logs\Models\UserLog;
use Modules\Logs\Services\LogService;

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
    use InteractsWithToast, HandlesExceptionsWithToast;
    use HasSearchAndFilters, HasBulkActions;

    protected LogService $logService;

    public int $perPage = 15;

    public ?string $action = null;

    public ?string $user_id = null;

    public ?string $date_from = null;

    public ?string $date_to = null;

    /** @var array<int> */
    public array $selectedLogs = [];

    /** @var array<int> Mevcut sayfadaki görünen log ID'leri - performans için */
    public array $visibleLogIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'action' => ['except' => ''],
        'user_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function boot(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function mount()
    {
        Gate::authorize('view logs');
    }

    /**
     * Get filter properties for HasSearchAndFilters trait
     */
    protected function getFilterProperties(): array
    {
        return ['search', 'action', 'user_id', 'date_from', 'date_to', 'perPage'];
    }

    /**
     * Get selected items property name for HasBulkActions trait
     */
    protected function getSelectedItemsPropertyName(): string
    {
        return 'selectedLogs';
    }

    /**
     * Get visible item IDs for HasBulkActions trait
     */
    protected function getVisibleItemIds(): array
    {
        return $this->visibleLogIds;
    }

    /**
     * Handle updated method - combine both traits
     */
    public function updated($propertyName): void
    {
        // Handle search and filters
        if (in_array($propertyName, $this->getFilterProperties())) {
            $this->onFilterUpdated($propertyName);
        }

        // Handle bulk actions
        $selectedPropertyName = $this->getSelectedItemsPropertyName();
        if ($propertyName === $selectedPropertyName) {
            if (! is_array($this->$propertyName)) {
                $this->$propertyName = [];
            }

            $visibleIds = $this->getVisibleItemIds();
            $diff = array_diff($visibleIds, $this->$propertyName);
            $this->selectAll = empty($diff);
        }
    }

    public function applyBulkAction(): void
    {
        Gate::authorize('delete logs');

        if (empty($this->selectedLogs) || empty($this->bulkAction) || ! is_array($this->selectedLogs)) {
            return;
        }

        try {
            $message = $this->logService->applyBulkAction($this->bulkAction, $this->selectedLogs);

            $this->clearBulkActionState();

            $this->toastSuccess($message);
        } catch (\Throwable $e) {
            $this->handleException($e, 'Toplu işlem sırasında bir hata oluştu. Lütfen tekrar deneyin.', [
                'selected_ids' => $this->selectedLogs ?? null,
                'bulk_action' => $this->bulkAction ?? null,
            ]);
        }
    }

    public function deleteLog($id)
    {
        Gate::authorize('delete logs');

        try {
            $log = UserLog::findOrFail($id);
            $this->logService->delete($log);

            $this->toastSuccess('Log kaydı başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Log kaydı silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'log_id' => $id,
            ]);
        }
    }

    public function clearAllLogs()
    {
        Gate::authorize('delete logs');

        try {
            $this->logService->clearAll();
            $this->toastSuccess('Tüm log kayıtları başarıyla silindi.');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Log kayıtları silinirken bir hata oluştu. Lütfen tekrar deneyin.');
        }
    }

    public function exportLogs()
    {
        Gate::authorize('export logs');

        try {
            // Aynı filtreleri kullanarak pagination olmadan tüm logları al
            // Optimize: Export için de sadece gerekli kolonları seç
            $query = UserLog::query()
                ->select([
                    'log_id',
                    'user_id',
                    'action',
                    'description',
                    'model_type',
                    'model_id',
                    'ip_address',
                    'user_agent',
                    'created_at',
                ])
                ->with(['user:id,name,email']);

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

        } catch (\Throwable $e) {
            $this->handleException($e, 'Log kayıtları dışa aktarılırken bir hata oluştu. Lütfen tekrar deneyin.');
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

        return $this->logService->getFilteredQuery($filters)
            ->paginate(Pagination::clamp($this->perPage));
    }

    public function render()
    {
        // Modül aktiflik kontrolü
        if (! \App\Helpers\SystemHelper::isModuleActive('logs')) {
            abort(404, 'Logs modülü aktif değil.');
        }

        $logs = $this->getLogs();

        // Mevcut sayfadaki görünen log ID'lerini kaydet - performans için
        $this->visibleLogIds = $logs->pluck('log_id')->all();

        /** @var view-string $view */
        $view = 'logs::livewire.log-index';

        return view($view, [
            'logs' => $logs,
            'actions' => UserLog::ACTIONS,
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
        ])->extends('layouts.admin')->section('content');
    }
}
