<?php

namespace Modules\User\Domain\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $userId): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
}

