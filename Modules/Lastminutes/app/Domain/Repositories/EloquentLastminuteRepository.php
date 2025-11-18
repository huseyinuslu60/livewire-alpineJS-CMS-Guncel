<?php

namespace Modules\Lastminutes\Domain\Repositories;

use Modules\Lastminutes\Models\Lastminute;

class EloquentLastminuteRepository implements LastminuteRepositoryInterface
{
    public function findById(int $lastminuteId): ?Lastminute
    {
        return Lastminute::find($lastminuteId);
    }

    public function findBySlug(string $slug): ?Lastminute
    {
        return Lastminute::where('slug', $slug)->first();
    }

    public function create(array $data): Lastminute
    {
        return Lastminute::create($data);
    }

    public function update(Lastminute $lastminute, array $data): Lastminute
    {
        $lastminute->update($data);

        return $lastminute->fresh();
    }

    public function delete(Lastminute $lastminute): bool
    {
        return $lastminute->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Lastminute::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('lastminute_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Lastminute::query();
    }
}
