<?php

namespace Modules\Newsletters\Domain\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Newsletters\Models\NewsletterUser;

interface NewsletterUserRepositoryInterface
{
    public function findById(int $userId): ?NewsletterUser;

    public function create(array $data): NewsletterUser;

    public function update(NewsletterUser $user, array $data): NewsletterUser;

    public function delete(NewsletterUser $user): bool;

    public function getQuery(): Builder;
}
