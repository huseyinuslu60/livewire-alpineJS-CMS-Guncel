<?php

namespace Modules\Newsletters\Domain\Repositories;

use Modules\Newsletters\Models\NewsletterTemplate;

interface NewsletterTemplateRepositoryInterface
{
    public function findById(int $id): ?NewsletterTemplate;

    public function findBySlug(string $slug): ?NewsletterTemplate;

    public function create(array $data): NewsletterTemplate;

    public function update(NewsletterTemplate $template, array $data): NewsletterTemplate;

    public function delete(NewsletterTemplate $template): bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
