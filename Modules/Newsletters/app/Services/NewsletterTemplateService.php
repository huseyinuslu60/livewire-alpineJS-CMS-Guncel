<?php

namespace Modules\Newsletters\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Newsletters\Domain\Events\NewsletterTemplateCreated;
use Modules\Newsletters\Domain\Events\NewsletterTemplateDeleted;
use Modules\Newsletters\Domain\Events\NewsletterTemplateUpdated;
use Modules\Newsletters\Domain\Repositories\NewsletterTemplateRepositoryInterface;
use Modules\Newsletters\Domain\Services\NewsletterTemplateValidator;
use Modules\Newsletters\Models\NewsletterTemplate;

class NewsletterTemplateService
{
    protected SlugGenerator $slugGenerator;

    protected NewsletterTemplateValidator $templateValidator;

    protected NewsletterTemplateRepositoryInterface $templateRepository;

    public function __construct(
        ?SlugGenerator $slugGenerator = null,
        ?NewsletterTemplateValidator $templateValidator = null,
        ?NewsletterTemplateRepositoryInterface $templateRepository = null
    ) {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->templateValidator = $templateValidator ?? app(NewsletterTemplateValidator::class);
        $this->templateRepository = $templateRepository ?? app(NewsletterTemplateRepositoryInterface::class);
    }

    /**
     * Create a new newsletter template
     */
    public function create(array $data): NewsletterTemplate
    {
        try {
            // Validate template data
            $this->templateValidator->validate($data);

            return DB::transaction(function () use ($data) {
                // Generate slug if not provided
                if (empty($data['slug']) && ! empty($data['name'])) {
                    $slug = $this->slugGenerator->generate($data['name'], NewsletterTemplate::class, 'slug', 'id');
                    $data['slug'] = $slug->toString();
                }

                $template = $this->templateRepository->create($data);

                // Fire domain event
                Event::dispatch(new NewsletterTemplateCreated($template));

                LogHelper::info('NewsletterTemplate oluşturuldu', [
                    'template_id' => $template->id,
                    'name' => $template->name,
                ]);

                return $template;
            });
        } catch (\Exception $e) {
            LogHelper::error('NewsletterTemplateService create error', [
                'name' => $data['name'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing newsletter template
     */
    public function update(NewsletterTemplate $template, array $data): NewsletterTemplate
    {
        try {
            // Validate template data
            $this->templateValidator->validate($data);

            return DB::transaction(function () use ($template, $data) {
                // Generate slug if name changed and slug is empty
                if (isset($data['name']) && $data['name'] !== $template->name && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['name'], NewsletterTemplate::class, 'slug', 'id', $template->id);
                    $data['slug'] = $slug->toString();
                }

                $template = $this->templateRepository->update($template, $data);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new NewsletterTemplateUpdated($template, $changedAttributes));

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
                $this->templateRepository->delete($template);

                // Fire domain event
                Event::dispatch(new NewsletterTemplateDeleted($template));

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
                $template->update(['is_active' => ! $template->is_active]);

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

    /**
     * Find a template by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $templateId): NewsletterTemplate
    {
        $template = $this->templateRepository->findById($templateId);

        if (! $template) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Newsletter template not found');
        }

        return $template;
    }

    /**
     * Get query builder for templates
     */
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Newsletters\Models\NewsletterTemplate>
     */
    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->templateRepository->getQuery();
    }
}
