<?php

namespace Modules\Lastminutes\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Lastminutes\Models\Lastminute;

class LastminuteService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Lastminute::query();

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->ofStatus($filters['status']);
        }

        // Sıralama
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDirection = $filters['sortDirection'] ?? 'desc';

        if ($sortBy === 'created_at' && $sortDirection === 'desc') {
            $query->sortedLatest('created_at');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Lastminute oluştur
     *
     * @param  array<string, mixed>  $data  Lastminute verileri
     * @return Lastminute
     */
    public function create(array $data): Lastminute
    {
        return DB::transaction(function () use ($data) {
            $lastminute = Lastminute::create($data);

            Log::info('Lastminute created via LastminuteService', [
                'lastminute_id' => $lastminute->lastminute_id,
                'title' => $lastminute->title,
                'status' => $lastminute->status,
            ]);

            return $lastminute;
        });
    }

    /**
     * Lastminute güncelle
     *
     * @param  Lastminute  $lastminute  Lastminute modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @return Lastminute
     */
    public function update(Lastminute $lastminute, array $data): Lastminute
    {
        return DB::transaction(function () use ($lastminute, $data) {
            $lastminute->update($data);

            Log::info('Lastminute updated via LastminuteService', [
                'lastminute_id' => $lastminute->lastminute_id,
                'title' => $lastminute->title,
            ]);

            return $lastminute->fresh();
        });
    }

    /**
     * Lastminute sil
     *
     * @param  Lastminute  $lastminute  Lastminute modeli
     * @return void
     */
    public function delete(Lastminute $lastminute): void
    {
        DB::transaction(function () use ($lastminute) {
            $lastminuteId = $lastminute->lastminute_id;
            $title = $lastminute->title;

            $lastminute->delete();

            Log::info('Lastminute deleted via LastminuteService', [
                'lastminute_id' => $lastminuteId,
                'title' => $title,
            ]);
        });
    }

    /**
     * Lastminute durumunu toggle et
     *
     * @param  Lastminute  $lastminute  Lastminute modeli
     * @return string Yeni durum
     */
    public function toggleStatus(Lastminute $lastminute): string
    {
        return DB::transaction(function () use ($lastminute) {
            if ($lastminute->status === 'active') {
                $lastminute->deactivate();
                $newStatus = 'inactive';
            } else {
                $lastminute->activate();
                $newStatus = 'active';
            }

            Log::info('Lastminute status toggled via LastminuteService', [
                'lastminute_id' => $lastminute->lastminute_id,
                'new_status' => $newStatus,
            ]);

            return $newStatus;
        });
    }

    /**
     * Lastminute'u süresi dolmuş olarak işaretle
     *
     * @param  Lastminute  $lastminute  Lastminute modeli
     * @return void
     */
    public function markAsExpired(Lastminute $lastminute): void
    {
        DB::transaction(function () use ($lastminute) {
            $lastminute->markAsExpired();

            Log::info('Lastminute marked as expired via LastminuteService', [
                'lastminute_id' => $lastminute->lastminute_id,
            ]);
        });
    }
}

