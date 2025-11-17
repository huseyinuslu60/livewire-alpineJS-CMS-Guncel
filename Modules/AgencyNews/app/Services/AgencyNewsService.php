<?php

namespace Modules\AgencyNews\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
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

            LogHelper::info('AgencyNews oluşturuldu', [
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

                LogHelper::info('AgencyNews güncellendi', [
                    'record_id' => $agencyNews->record_id,
                    'title' => $agencyNews->title,
                ]);

                return $agencyNews;
            });
        } catch (\Exception $e) {
            LogHelper::error('AgencyNewsService update error', [
                'record_id' => $agencyNews->record_id,
                'error' => $e->getMessage(),
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

                LogHelper::info('AgencyNews silindi', [
                    'record_id' => $agencyNews->record_id,
                    'title' => $agencyNews->title,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('AgencyNewsService delete error', [
                'record_id' => $agencyNews->record_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

