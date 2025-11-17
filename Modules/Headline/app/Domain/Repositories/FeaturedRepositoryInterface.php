<?php

namespace Modules\Headline\Domain\Repositories;

use Modules\Headline\app\Models\Featured;

interface FeaturedRepositoryInterface
{
    public function findById(int $id): ?Featured;
    public function findByZoneAndSubject(string $zone, string $subjectType, int $subjectId): ?Featured;
    public function create(array $data): Featured;
    public function update(Featured $featured, array $data): Featured;
    public function updateOrCreate(array $attributes, array $values): Featured;
    public function delete(Featured $featured): bool;
    public function deleteByZoneAndSubject(string $zone, string $subjectType, int $subjectId): int;
}

