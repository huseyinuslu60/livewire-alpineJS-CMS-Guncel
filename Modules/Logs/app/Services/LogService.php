<?php

namespace Modules\Logs\Services;

use App\Helpers\LogHelper;
use App\Support\Pagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Logs\Domain\Events\LogDeleted;
use Modules\Logs\Domain\Events\LogsBulkDeleted;
use Modules\Logs\Domain\Events\LogsCleared;
use Modules\Logs\Domain\Repositories\LogRepositoryInterface;
use Modules\Logs\Domain\Services\LogValidator;
use Modules\Logs\Models\UserLog;

class LogService
{
    protected LogValidator $logValidator;
    protected LogRepositoryInterface $logRepository;

    public function __construct(
        ?LogValidator $logValidator = null,
        ?LogRepositoryInterface $logRepository = null
    ) {
        $this->logValidator = $logValidator ?? app(LogValidator::class);
        $this->logRepository = $logRepository ?? app(LogRepositoryInterface::class);
    }

    /**
     * Delete a single log
     *
     * @param  int  $logId
     * @return bool
     */
    public function delete(int $logId): bool
    {
        try {
            // Validate log ID
            $this->logValidator->validateLogId($logId);

            $log = $this->logRepository->findById($logId);
            
            if (!$log) {
                throw new \Exception('Log kaydı bulunamadı');
            }

            $this->logRepository->delete($log);

            // Fire domain event
            Event::dispatch(new LogDeleted($log));

            LogHelper::info('Log kaydı silindi', [
                'log_id' => $logId,
            ]);

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Log kaydı silinirken hata oluştu', [
                'log_id' => $logId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete multiple logs (bulk delete)
     *
     * @param  array  $logIds
     * @return int  Number of deleted logs
     */
    public function deleteBulk(array $logIds): int
    {
        try {
            // Validate log IDs
            $this->logValidator->validateLogIds($logIds);

            $deletedCount = $this->logRepository->deleteBulk($logIds);

            // Fire domain event
            Event::dispatch(new LogsBulkDeleted($deletedCount, $logIds));

            LogHelper::info('Log kayıtları toplu silindi', [
                'count' => $deletedCount,
                'log_ids' => $logIds,
            ]);

            return $deletedCount;
        } catch (\Exception $e) {
            LogHelper::error('Log kayıtları toplu silinirken hata oluştu', [
                'log_ids' => $logIds,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Clear all logs
     *
     * @return bool
     */
    public function clearAll(): bool
    {
        try {
            $deletedCount = $this->logRepository->clearAll();

            // Fire domain event
            Event::dispatch(new LogsCleared($deletedCount));

            LogHelper::info('Tüm log kayıtları temizlendi', [
                'deleted_count' => $deletedCount,
            ]);

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Tüm log kayıtları temizlenirken hata oluştu', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get filtered logs with pagination
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredLogs(array $filters = [], int $perPage = 15)
    {
        $query = UserLog::query()->with(['user']);

        // Search filter
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Action filter
        if (! empty($filters['action'])) {
            $query->ofAction($filters['action']);
        }

        // User filter
        if (! empty($filters['user_id'])) {
            $query->ofUser($filters['user_id']);
        }

        // Date range filters
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query
            ->sortedLatest('created_at')
            ->paginate(Pagination::clamp($perPage));
    }

    /**
     * Export logs to CSV
     *
     * @param  array  $filters
     * @return array  ['data' => string, 'filename' => string]
     */
    public function exportToCsv(array $filters = []): array
    {
        try {
            $query = UserLog::query()->with(['user']);

            // Apply same filters as getFilteredLogs
            if (! empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (! empty($filters['action'])) {
                $query->ofAction($filters['action']);
            }

            if (! empty($filters['user_id'])) {
                $query->ofUser($filters['user_id']);
            }

            if (! empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            // CSV header
            $csvData = "ID,User,Action,Model Type,Model ID,Description,IP Address,User Agent,Created At\n";

            $hasLogs = false;

            // Export için chunk() kullan (büyük veri setleri için)
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

            if (! $hasLogs) {
                throw new \Exception('Dışa aktarılacak log kaydı bulunamadı.');
            }

            // UTF-8 BOM ekle (Excel için)
            $csvData = "\xEF\xBB\xBF".$csvData;

            // Dosya adı oluştur
            $filename = 'logs_export_'.date('Y-m-d_H-i-s').'.csv';

            LogHelper::info('Log kayıtları CSV olarak dışa aktarıldı', [
                'filename' => $filename,
                'filters' => $filters,
            ]);

            return [
                'data' => $csvData,
                'filename' => $filename,
            ];
        } catch (\Exception $e) {
            LogHelper::error('Log kayıtları dışa aktarılırken hata oluştu', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get log statistics
     *
     * @param  array  $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        try {
            $query = UserLog::query();

            // Apply filters
            if (! empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            if (! empty($filters['user_id'])) {
                $query->ofUser($filters['user_id']);
            }

            return [
                'total' => $query->count(),
                'by_action' => $query->clone()
                    ->select('action', DB::raw('count(*) as count'))
                    ->groupBy('action')
                    ->pluck('count', 'action')
                    ->toArray(),
                'by_user' => $query->clone()
                    ->select('user_id', DB::raw('count(*) as count'))
                    ->groupBy('user_id')
                    ->pluck('count', 'user_id')
                    ->toArray(),
            ];
        } catch (\Exception $e) {
            LogHelper::error('Log istatistikleri alınırken hata oluştu', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

