<?php

namespace Modules\Newsletters\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateService
{
    /**
     * Create a new newsletter template
     */
    public function create(array $data): NewsletterTemplate
    {
        return DB::transaction(function () use ($data) {
            $template = NewsletterTemplate::create($data);

            LogHelper::info('NewsletterTemplate oluşturuldu', [
                'template_id' => $template->id,
                'name' => $template->name,
            ]);

            return $template;
        });
    }

    /**
     * Update an existing newsletter template
     */
    public function update(NewsletterTemplate $template, array $data): NewsletterTemplate
    {
        try {
            return DB::transaction(function () use ($template, $data) {
                $template->update($data);

                LogHelper::info('NewsletterTemplate güncellendi', [
                    'template_id' => $template->id,
                    'name' => $template->name,
                ]);

                return $template;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterTemplateService update error', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a newsletter template
     */
    public function delete(NewsletterTemplate $template): void
    {
        try {
            DB::transaction(function () use ($template) {
                $template->delete();

                LogHelper::info('NewsletterTemplate silindi', [
                    'template_id' => $template->id,
                    'name' => $template->name,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterTemplateService delete error', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle template active status
     */
    public function toggleActive(NewsletterTemplate $template): NewsletterTemplate
    {
        try {
            return DB::transaction(function () use ($template) {
                $template->update(['is_active' => !$template->is_active]);

                LogHelper::info('NewsletterTemplate aktif durumu değiştirildi', [
                    'template_id' => $template->id,
                    'is_active' => $template->is_active,
                ]);

                return $template;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterTemplateService toggleActive error', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

