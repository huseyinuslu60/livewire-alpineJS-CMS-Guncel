<?php

namespace Modules\Headline\Domain\Repositories;

use Modules\Headline\app\Models\Featured;

class EloquentFeaturedRepository implements FeaturedRepositoryInterface
{
    public function findById(int $id): ?Featured
    {
        return Featured::find($id);
    }

    public function findByZoneAndSubject(string $zone, string $subjectType, int $subjectId): ?Featured
    {
        return Featured::where('zone', $zone)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->first();
    }

    public function findByZoneAndSlot(string $zone, int $slot): \Illuminate\Database\Eloquent\Collection
    {
        return Featured::where('zone', $zone)
            ->where('slot', $slot)
            ->get();
    }

    public function findByZone(string $zone): \Illuminate\Database\Eloquent\Collection
    {
        return Featured::where('zone', $zone)->get();
    }

    public function getSubjectIdsByZone(string $zone): array
    {
        return Featured::where('zone', $zone)
            ->pluck('subject_id')
            ->toArray();
    }

    public function getMaxSlotForZone(string $zone): ?int
    {
        return Featured::where('zone', $zone)
            ->whereNotNull('slot')
            ->max('slot');
    }

    public function create(array $data): Featured
    {
        return Featured::create($data);
    }

    public function update(Featured $featured, array $data): Featured
    {
        $featured->update($data);

        return $featured->fresh();
    }

    public function updateOrCreate(array $attributes, array $values): Featured
    {
        return Featured::updateOrCreate($attributes, $values);
    }

    public function delete(Featured $featured): bool
    {
        return $featured->delete();
    }

    public function deleteByZoneAndSubject(string $zone, string $subjectType, int $subjectId): int
    {
        return Featured::where('zone', $zone)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->delete();
    }

    public function getQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Featured::query();
    }
}
