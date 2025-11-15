<?php

namespace Modules\Logs\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Logs\Models\UserLog;

class LogService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = UserLog::query()->with(['user']);

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

        return $query->sortedLatest('created_at');
    }

    /**
     * Log kaydını sil
     *
     * @param  UserLog  $log  Log modeli
     */
    public function delete(UserLog $log): void
    {
        DB::transaction(function () use ($log) {
            $logId = $log->log_id;
            $log->delete();

            Log::info('UserLog deleted via LogService', ['log_id' => $logId]);
        });
    }

    /**
     * Toplu işlem uygula
     *
     * @param  string  $action  İşlem tipi (delete)
     * @param  array<int>  $ids  Log ID'leri
     * @return string Mesaj
     */
    public function applyBulkAction(string $action, array $ids): string
    {
        return DB::transaction(function () use ($action, $ids) {
            $logs = UserLog::whereIn('log_id', $ids);
            $selectedCount = count($ids);

            switch ($action) {
                case 'delete':
                    $logs->delete();
                    $message = $selectedCount.' log kaydı başarıyla silindi.';
                    break;
                default:
                    throw new \Exception('Geçersiz toplu işlem: '.$action);
            }

            Log::info('Bulk action applied via LogService', [
                'action' => $action,
                'count' => $selectedCount,
            ]);

            return $message;
        });
    }

    /**
     * Tüm log kayıtlarını sil
     */
    public function clearAll(): void
    {
        DB::transaction(function () {
            UserLog::truncate();
            Log::info('All logs cleared via LogService');
        });
    }
}
