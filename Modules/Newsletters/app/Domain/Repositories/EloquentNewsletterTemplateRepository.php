<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterTemplate;

class EloquentNewsletterTemplateRepository implements NewsletterTemplateRepositoryInterface
{
    public function findById(int $id): ?NewsletterTemplate
    {
        return NewsletterTemplate::find($id);
    }

    public function findBySlug(string $slug): ?NewsletterTemplate
    {
        return NewsletterTemplate::where('slug', $slug)->first();
    }

    public function create(array $data): NewsletterTemplate
    {
        return NewsletterTemplate::create($data);
    }

    public function update(NewsletterTemplate $template, array $data): NewsletterTemplate
    {
        $template->update($data);
        return $template->fresh();
    }

    public function delete(NewsletterTemplate $template): bool
    {
        return $template->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = NewsletterTemplate::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}

