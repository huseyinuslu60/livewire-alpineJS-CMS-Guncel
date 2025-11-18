<?php

namespace Modules\Lastminutes\Domain\Repositories;

use Modules\Lastminutes\Models\Lastminute;

interface LastminuteRepositoryInterface
{
    public function findById(int $lastminuteId): ?Lastminute;

    public function findBySlug(string $slug): ?Lastminute;

    public function create(array $data): Lastminute;

    public function update(Lastminute $lastminute, array $data): Lastminute;

    public function delete(Lastminute $lastminute): bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function getQuery(): \Illuminate\Database\Eloquent\Builder;
}
