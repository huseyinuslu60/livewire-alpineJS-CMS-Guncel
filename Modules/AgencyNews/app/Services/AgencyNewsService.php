<?php

namespace Modules\AgencyNews\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use Illuminate\Support\Facades\DB;
use Modules\AgencyNews\Domain\Services\AgencyNewsValidator;
use Modules\AgencyNews\Models\AgencyNews;

class AgencyNewsService
{
    protected SlugGenerator $slugGenerator;
    protected AgencyNewsValidator $agencyNewsValidator;

    public function __construct(?SlugGenerator $slugGenerator = null, ?AgencyNewsValidator $agencyNewsValidator = null)
    {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->agencyNewsValidator = $agencyNewsValidator ?? app(AgencyNewsValidator::class);
    }

    /**
     * Create a new agency news
     */
    public function create(array $data): AgencyNews
    {
        try {
            // Validate agency news data
            $this->agencyNewsValidator->validate($data);

            return DB::transaction(function () use ($data) {
                // Generate slug if not provided
                if (empty($data['slug']) && !empty($data['title'])) {
                    $slug = $this->slugGenerator->generate($data['title'], AgencyNews::class, 'slug', 'record_id');
                    $data['slug'] = $slug->toString();
                }

                $agencyNews = AgencyNews::create($data);

                LogHelper::info('AgencyNews oluşturuldu', [
                    'record_id' => $agencyNews->record_id,
                    'title' => $agencyNews->title,
                ]);

                return $agencyNews;
            });
        } catch (\Exception $e) {
            LogHelper::error('AgencyNewsService create error', [
                'title' => $data['title'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing agency news
     */
    public function update(AgencyNews $agencyNews, array $data): AgencyNews
    {
        try {
            // Validate agency news data
            $this->agencyNewsValidator->validate($data);

            return DB::transaction(function () use ($agencyNews, $data) {
                // Generate slug if title changed and slug is empty
                if (isset($data['title']) && $data['title'] !== $agencyNews->title && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['title'], AgencyNews::class, 'slug', 'record_id', $agencyNews->record_id);
                    $data['slug'] = $slug->toString();
                }

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

