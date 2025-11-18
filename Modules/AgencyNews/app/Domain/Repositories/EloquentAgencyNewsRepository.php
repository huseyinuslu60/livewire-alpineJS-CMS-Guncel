<?php

namespace Modules\AgencyNews\Domain\Repositories;

use Modules\AgencyNews\Models\AgencyNews;

class EloquentAgencyNewsRepository implements AgencyNewsRepositoryInterface
{
    public function findById(int $recordId): ?AgencyNews
    {
        return AgencyNews::find($recordId);
    }

    public function findBySlug(string $slug): ?AgencyNews
    {
        return AgencyNews::where('slug', $slug)->first();
    }

    public function create(array $data): AgencyNews
    {
        return AgencyNews::create($data);
    }

    public function update(AgencyNews $agencyNews, array $data): AgencyNews
    {
        $agencyNews->update($data);

        return $agencyNews->fresh();
    }

    public function delete(AgencyNews $agencyNews): bool
    {
        return $agencyNews->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = AgencyNews::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('record_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return AgencyNews::query();
    }
}
