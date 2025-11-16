<?php

namespace Modules\AgencyNews\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\AgencyNews\Models\AgencyNews;

class AgencyNewsService
{
    /**
     * Create a new agency news
     */
    public function create(array $data): AgencyNews
    {
        return DB::transaction(function () use ($data) {
            $agencyNews = AgencyNews::create($data);

            Log::info('AgencyNews created', [
                'record_id' => $agencyNews->record_id,
                'title' => $agencyNews->title,
            ]);

            return $agencyNews;
        });
    }

    /**
     * Update an existing agency news
     */
    public function update(AgencyNews $agencyNews, array $data): AgencyNews
    {
        try {
            return DB::transaction(function () use ($agencyNews, $data) {
                $agencyNews->update($data);

                Log::info('AgencyNews updated', [
                    'record_id' => $agencyNews->record_id,
                    'title' => $agencyNews->title,
                ]);

                return $agencyNews;
            });
        } catch (\Exception $e) {
            Log::error('AgencyNewsService update error:', [
                'record_id' => $agencyNews->record_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an agency news
     */
    public function delete(AgencyNews $agencyNews): void
    {
        try {
            DB::transaction(function () use ($agencyNews) {
                $agencyNews->delete();

                Log::info('AgencyNews deleted', [
                    'record_id' => $agencyNews->record_id,
                    'title' => $agencyNews->title,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('AgencyNewsService delete error:', [
                'record_id' => $agencyNews->record_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

