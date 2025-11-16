<?php

namespace Modules\Newsletters\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            Log::info('NewsletterTemplate created', [
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

                Log::info('NewsletterTemplate updated', [
                    'template_id' => $template->id,
                    'name' => $template->name,
                ]);

                return $template;
            });
        } catch (\Exception $e) {
            Log::error('NewsletterTemplateService update error:', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

                Log::info('NewsletterTemplate deleted', [
                    'template_id' => $template->id,
                    'name' => $template->name,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('NewsletterTemplateService delete error:', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

                Log::info('NewsletterTemplate active status toggled', [
                    'template_id' => $template->id,
                    'is_active' => $template->is_active,
                ]);

                return $template;
            });
        } catch (\Exception $e) {
            Log::error('NewsletterTemplateService toggleActive error:', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

