<?php

namespace Modules\Newsletters\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Newsletters\Models\Newsletter;

class NewsletterService
{
    /**
     * Filtreli sorgu oluştur
     *
     * @param  array<string, mixed>  $filters  Filtre parametreleri
     * @return Builder
     */
    public function getFilteredQuery(array $filters = []): Builder
    {
        $query = Newsletter::with(['creator', 'updater']);

        // Arama filtresi
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Durum filtresi
        if (! empty($filters['statusFilter'])) {
            $query->ofStatus($filters['statusFilter']);
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
     * Newsletter oluştur
     *
     * @param  array<string, mixed>  $data  Newsletter verileri
     * @return Newsletter
     */
    public function create(array $data): Newsletter
    {
        return DB::transaction(function () use ($data) {
            $newsletter = Newsletter::create($data);

            Log::info('Newsletter created via NewsletterService', [
                'newsletter_id' => $newsletter->newsletter_id,
                'name' => $newsletter->name,
                'status' => $newsletter->status,
            ]);

            return $newsletter;
        });
    }

    /**
     * Newsletter güncelle
     *
     * @param  Newsletter  $newsletter  Newsletter modeli
     * @param  array<string, mixed>  $data  Güncellenecek veriler
     * @return Newsletter
     */
    public function update(Newsletter $newsletter, array $data): Newsletter
    {
        return DB::transaction(function () use ($newsletter, $data) {
            $newsletter->update($data);

            Log::info('Newsletter updated via NewsletterService', [
                'newsletter_id' => $newsletter->newsletter_id,
                'name' => $newsletter->name,
                'status' => $newsletter->status,
            ]);

            return $newsletter->fresh();
        });
    }

    /**
     * Newsletter sil
     *
     * @param  Newsletter  $newsletter  Newsletter modeli
     * @return void
     */
    public function delete(Newsletter $newsletter): void
    {
        DB::transaction(function () use ($newsletter) {
            $newsletterId = $newsletter->newsletter_id;
            $name = $newsletter->name;

            $newsletter->delete();

            Log::info('Newsletter deleted via NewsletterService', [
                'newsletter_id' => $newsletterId,
                'name' => $name,
            ]);
        });
    }

    /**
     * Newsletter durumunu toggle et
     *
     * @param  Newsletter  $newsletter  Newsletter modeli
     * @return string Yeni durum
     */
    public function toggleStatus(Newsletter $newsletter): string
    {
        return DB::transaction(function () use ($newsletter) {
            $newStatus = match ($newsletter->status) {
                'draft' => 'sending',
                'sending' => 'sent',
                'sent' => 'draft',
                'failed' => 'draft',
                default => 'draft'
            };

            $newsletter->update(['status' => $newStatus]);

            Log::info('Newsletter status toggled via NewsletterService', [
                'newsletter_id' => $newsletter->newsletter_id,
                'old_status' => $newsletter->getOriginal('status'),
                'new_status' => $newStatus,
            ]);

            return $newStatus;
        });
    }
}

