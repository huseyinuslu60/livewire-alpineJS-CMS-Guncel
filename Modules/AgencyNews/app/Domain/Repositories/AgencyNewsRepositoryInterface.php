<?php

namespace Modules\AgencyNews\Domain\Repositories;

use Modules\AgencyNews\Models\AgencyNews;

interface AgencyNewsRepositoryInterface
{
    public function findById(int $recordId): ?AgencyNews;

    public function findBySlug(string $slug): ?AgencyNews;

    public function create(array $data): AgencyNews;

    public function update(AgencyNews $agencyNews, array $data): AgencyNews;

    public function delete(AgencyNews $agencyNews): bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
